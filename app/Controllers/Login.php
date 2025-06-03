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
    $data = $this->request->getJSON(true);

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

    // Generar código de activación (5 caracteres hex)
 $codigoActivacion = substr(bin2hex(random_bytes(3)), 0, 5);


    // Preparar datos para insertar con código y estado cuenta no activada
    $nuevoUsuario = [
        'nombre' => $data['nombre'],
        'apellido_paterno' => $data['apellidoPaterno'],
        'apellido_materno' => $data['apellidoMaterno'],
        'correo' => $data['email'],
        'fecha_nacimiento' => $data['fechaNacimiento'],
        'telefono' => $data['numeroTelefonico'],
        'contrasena' => password_hash($data['contrasena'], PASSWORD_DEFAULT),
        'fecha_registro' => date('Y-m-d H:i:s'),
        'codigo_activacion' => $codigoActivacion,
        'cuenta_activada' => 0
    ];

    // Insertar el usuario en BD
    $insertId = $this->usuarios->insert($nuevoUsuario);

    if (!$insertId) {
        return $this->respond([
            'success' => false,
            'message' => 'Error al registrar usuario.'
        ], 500);
    }

    // Enviar correo con el código de activación
    $email = \Config\Services::email();

    $email->setTo($data['email']);
    $email->setSubject('Activación de cuenta');
    $email->setMessage("
        <p>Hola {$data['nombre']},</p>
        <p>Gracias por registrarte en nuestro sistema. Para activar tu cuenta, por favor ingresa el siguiente código de activación en la app o sitio web:</p>
        <h2 style='color:#2e6c80;'>$codigoActivacion</h2>
        <p>Si no solicitaste este registro, puedes ignorar este correo.</p>
        <p>Saludos,<br>Equipo de Soporte</p>
    ");

    if (!$email->send()) {
        return $this->respond([
            'success' => false,
            'message' => 'Usuario registrado pero no se pudo enviar el correo de activación.',
            'debug' => $email->printDebugger(['headers'])
        ], 500);
    }

    return $this->respond([
        'success' => true,
        'message' => 'Usuario registrado exitosamente. Por favor, revisa tu correo electrónico para activar tu cuenta.'
    ], 201);
}



public function activarCuenta()
{
    $data = $this->request->getJSON(true);

    if (empty($data['correo']) || empty($data['codigo'])) {
        return $this->respond([
            'success' => false,
            'message' => 'Correo y código de activación son requeridos.'
        ], 400);
    }

    $usuario = $this->usuarios->where('correo', $data['correo'])->first();

    if (!$usuario) {
        return $this->respond([
            'success' => false,
            'message' => 'Usuario no encontrado.'
        ], 404);
    }

    if ($usuario['cuenta_activada']) {
        return $this->respond([
            'success' => false,
            'message' => 'La cuenta ya está activada.'
        ], 400);
    }

    if ($usuario['codigo_activacion'] != $data['codigo']) {
        return $this->respond([
            'success' => false,
            'message' => 'Código de activación incorrecto.'
        ], 401);
    }

    $this->usuarios->update($usuario['id'], [
        'cuenta_activada' => 1,
        'codigo_activacion' => null
    ]);

    return $this->respond([
        'success' => true,
        'message' => 'Cuenta activada correctamente.'
    ]);
}


}


