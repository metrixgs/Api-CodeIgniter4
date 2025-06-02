<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\TicketsModel;
use App\Models\AccionesTicketsModel;
use App\Models\EncuestaIncidenciaModel;
use App\Models\RolesModel;
use App\Models\EstadosTareaModel;


class Login extends BaseController
{
    use ResponseTrait;

    protected $usuarios;
    protected $tickets;
     protected $roles;
      protected $estadosTarea;

  protected $acciones; 
    public function __construct()
    {
        // Instanciar el modelo de usuarios
        $this->usuarios = new UsuariosModel();
        $this->tickets = new TicketsModel();
      $this->acciones = new AccionesTicketsModel();
      $this->encuesta = new EncuestaIncidenciaModel();
        $this->roles = new RolesModel(); 
         $this->estadosTarea = new EstadosTareaModel();
        // Cargar los Helpers
        helper(['Alerts', 'Email']);

        // Configurar encabezados CORS
        $this->configurarCORS();
    }

    /**
     * Configurar encabezados CORS
     */
    private function configurarCORS()
    {
        if (!headers_sent()) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
            header('Access-Control-Max-Age: 3600');
        }

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit(0);
        }
    }

    /**
     * Manejar solicitudes OPTIONS para CORS
     */
    public function options()
    {
        $response = $this->response;

        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->setHeader('Access-Control-Max-Age', '3600');
        $response->setStatusCode(200);

        return $response->setBody('');
    }   public function index()
{
    $json = (array) ($this->request->getJSON() ?? $this->request->getPost());

    $rules = [
        'correo' => 'required|valid_email',
        'contrasena' => 'required|min_length[6]'
    ];

    if (!$this->validate($rules)) {
        return $this->failValidationErrors($this->validator->getErrors());
    }

    $correo = $json['correo'];
    $contrasena = $json['contrasena'];

    $user = $this->usuarios->where('correo', $correo)->first();

    if ($user === null) {
        return $this->failUnauthorized('Correo electrónico no registrado');
    }

    if ($contrasena !== $user['contrasena']) {
        return $this->failUnauthorized('Contraseña incorrecta');
    }

    $userData = [
        'id' => $user['id'],
        'correo' => $user['correo'],
        'nombre' => $user['nombre'],
        'area_id' => $user['area_id'],
        'cargo' => $user['cargo'],
        'telefono' => $user['telefono'],
        'rol_id' => $user['rol_id'],
        'rol_nombre' => null,
        'fecha_registro' => $user['fecha_registro']
    ];

    $tickets = $this->tickets
        ->select('tbl_tickets.*, u1.nombre AS nombre_usuario, u2.nombre AS nombre_cliente')
        ->join('tbl_usuarios AS u1', 'tbl_tickets.usuario_id = u1.id', 'left')
        ->join('tbl_usuarios AS u2', 'tbl_tickets.cliente_id = u2.id', 'left')
        ->where('tbl_tickets.cuenta_id', $user['cuenta_id'])
        ->orderBy('tbl_tickets.id', 'DESC')
        ->findAll(10);

    $encuestaBD = $this->encuesta->first();
    $questionsRaw = trim($encuestaBD['questions'] ?? '');

    if (!str_starts_with($questionsRaw, '[')) {
        $questionsRaw = '[' . $questionsRaw . ']';
    }

    $questionsArray = json_decode($questionsRaw, true);

    foreach ($questionsArray as &$question) {
        if (isset($question['options']) && is_array($question['options'])) {
            $question['options'] = array_map(function ($opt) {
                if (is_array($opt)) {
                    if (!isset($opt['status']) || $opt['status'] === null) {
                        $opt['status'] = (strtolower($opt['text']) === 'baldío') ? "1" : null;
                    }
                    return $opt;
                } else {
                    return [
                        'text' => $opt,
                        'status' => (strtolower($opt) === 'baldío') ? "1" : null
                    ];
                }
            }, $question['options']);
        }
    }

    $questionsRaw = json_encode($questionsArray, JSON_UNESCAPED_UNICODE);

    $mapaEstados = [
        'baldio' => 1,
        'abandonada' => 2,
        'completada' => 3,
        'cancelada' => 4,
        'no quiere interactuar' => 5,
        'volver' => 6,
        'contacto / invitacion' => 7,
        'pendiente' => 8
    ];

    $tareas = array_map(function ($ticket) use ($encuestaBD, $questionsRaw, $mapaEstados) {
        $estadoNombre = strtolower(trim($ticket['estado'] ?? 'sin estado'));

        // Normalizar acentos
        $buscar = ['á', 'é', 'í', 'ó', 'ú'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u'];
        $estadoNombre = str_replace($buscar, $reemplazar, $estadoNombre);

        $idEstado = $mapaEstados[$estadoNombre] ?? 0;

        switch ($idEstado) {
            case 1: $colorEstado = '#000000'; break;
            case 2: $colorEstado = '#808080'; break;
            case 3: $colorEstado = '#008000'; break;
            case 4: $colorEstado = '#800000'; break;
            case 5: $colorEstado = '#FFA500'; break;
            case 6: $colorEstado = '#FFFF00'; break;
            case 7: $colorEstado = '#0000FF'; break;
            case 8: $colorEstado = '#9C27B0'; break;
            default: $colorEstado = null;
        }

        // Aquí está el cambio importante:
        $dibujarRuta = ($idEstado === 8) ? true : false;

        $status = [
            'id' => $idEstado,
            'nombre' => $ticket['estado'] ?? 'Sin estado',
            'color' => $colorEstado,
            'dibujarRuta' => $dibujarRuta
        ];

        $ultimaAccion = $this->acciones
            ->where('ticket_id', $ticket['id'])
            ->orderBy('id', 'DESC')
            ->first();

        $comentario = $ultimaAccion['descripcion'] ?? '';

        $encuesta = [
            'id' => $encuestaBD['id'],
            'title' => $encuestaBD['title'],
            'description' => $encuestaBD['description'],
            'questions' => $questionsRaw,
            'image' => $encuestaBD['image'] ?? null,
            'created_at' => $encuestaBD['created_at'] ?? null,
            'updated_at' => $encuestaBD['updated_at'] ?? null
        ];

        return [
            'id' => $ticket['id'],
            'latitud' => (float)$ticket['latitud'],
            'longitud' => (float)$ticket['longitud'],
            'descripcion' => $ticket['descripcion'],
            'url_encuesta' => 'https://www.metrixencuesta.wuaze.com/index.php/survey/4',
            'titulo' => $ticket['titulo'],
            'status' => $status,
            'comentario' => $comentario,
            'encuesta' => $encuesta
        ];
    }, $tickets);

    $userData['tareas'] = $tareas;

    return $this->respond([
        'status' => 200,
        'error' => false,
        'message' => 'Inicio de sesión exitoso',
        'data' => $userData
    ]);
}


 public function registro()
{
    // Obtener JSON como arreglo asociativo
    $data = $this->request->getJSON(true);

    // Reglas de validación
    $validationRules = [
        'nombre' => 'required|min_length[2]',
        'apellidoPaterno' => 'required|min_length[2]',
        'apellidoMaterno' => 'required|min_length[2]',
        'email' => 'required|valid_email',
        'fechaNacimiento' => 'required|valid_date[Y-m-d]',
        'numeroTelefonico' => 'required|regex_match[/^\+?[0-9]{10,15}$/]',
        'contrasena' => 'required|min_length[8]'
    ];

    if (!$this->validate($validationRules)) {
        return $this->respond([
            'success' => false,
            'message' => $this->validator->getErrors()
        ], 400);
    }

    // Verificar si email ya existe
    $existe = $this->usuarios->where('correo', $data['email'])->first();
    if ($existe) {
        return $this->respond([
            'success' => false,
            'message' => 'El correo electrónico ya está registrado.'
        ], 409);
    }

    // Preparar datos para insertar (ajusta nombres de campos de BD si necesario)
    $nuevoUsuario = [
        'nombre' => $data['nombre'],
        'apellido_paterno' => $data['apellidoPaterno'],
        'apellido_materno' => $data['apellidoMaterno'],
        'correo' => $data['email'],
        'fecha_nacimiento' => $data['fechaNacimiento'],
        'telefono' => $data['numeroTelefonico'],
        'contrasena' => password_hash($data['contrasena'], PASSWORD_DEFAULT),
        'fecha_registro' => date('Y-m-d H:i:s')
    ];

    $this->usuarios->insert($nuevoUsuario);

    // Generar un usuarioId de ejemplo, aquí puedes usar el ID real si es numérico o UUID
    $usuarioId = uniqid('usr_', true);

    return $this->respond([
        'success' => true,
        'message' => 'Usuario registrado exitosamente.',
        'usuarioId' => $usuarioId
    ], 201);
}




}


