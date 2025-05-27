<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\TicketsModel;
use App\Models\AccionesTicketsModel;
use App\Models\EncuestaIncidenciaModel;
use App\Models\RolesModel;


class Login extends BaseController
{
    use ResponseTrait;

    protected $usuarios;
    protected $tickets;
     protected $roles;

  protected $acciones; 
    public function __construct()
    {
        // Instanciar el modelo de usuarios
        $this->usuarios = new UsuariosModel();
        $this->tickets = new TicketsModel();
      $this->acciones = new AccionesTicketsModel();
      $this->encuesta = new EncuestaIncidenciaModel();
        $this->roles = new RolesModel(); 
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
    }

   public function index()
{
    $json = $this->request->getJSON() ?? $this->request->getPost();

    $rules = [
        'correo' => 'required|valid_email',
        'contrasena' => 'required|min_length[6]'
    ];

    if (!$this->validate($rules)) {
        return $this->failValidationErrors($this->validator->getErrors());
    }

    $correo = $json->correo ?? $json['correo'];
    $contrasena = $json->contrasena ?? $json['contrasena'];

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

    $estadosMapa = [
        'Pendiente' => ['id' => 1, 'nombre' => 'Pendiente', 'color' => '#FFC107'],
        'Abierto' => ['id' => 1, 'nombre' => 'Pendiente', 'color' => '#FFC107'],
        'En Proceso' => ['id' => 2, 'nombre' => 'En Proceso', 'color' => '#2196F3'],
        'Cerrado' => ['id' => 3, 'nombre' => 'Completada', 'color' => '#4CAF50'],
    ];

    $tickets = $this->tickets
        ->where('cuenta_id', $user['cuenta_id'])
        ->orderBy('id', 'DESC')
        ->findAll(10);

    $encuestaBD = $this->encuesta->first();

    // Obtener y procesar el string de questions
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
                    if (strtolower($opt['text']) === 'baldío') {
                        $opt['status'] = "1";
                    } else {
                        $opt['status'] = null;
                    }
                }
                return $opt;
            } else {
                // Si es string
                if (strtolower($opt) === 'baldío') {
                    return ['text' => $opt, 'status' => "1"];
                } else {
                    return ['text' => $opt, 'status' => null];
                }
            }
        }, $question['options']);
    }
}



    $questionsRaw = json_encode($questionsArray, JSON_UNESCAPED_UNICODE);

    $tareas = array_map(function ($ticket) use ($estadosMapa, $encuestaBD, $questionsRaw) {
        $estadoKey = $ticket['estado'] ?? 'Pendiente';
        $status = $estadosMapa[$estadoKey] ?? ['id' => 0, 'nombre' => $estadoKey, 'color' => '#9E9E9E'];
        $status['dibujarRuta'] = true;

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


