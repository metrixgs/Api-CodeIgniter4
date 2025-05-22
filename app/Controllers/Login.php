<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\TicketsModel;
use App\Models\AccionesTicketsModel;


class Login extends BaseController
{
    use ResponseTrait;

    protected $usuarios;
    protected $tickets;

  protected $acciones; 
    public function __construct()
    {
        // Instanciar el modelo de usuarios
        $this->usuarios = new UsuariosModel();
        $this->tickets = new TicketsModel();
      $this->acciones = new AccionesTicketsModel();
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

    /**
     * Autenticar usuario (POST)
     */
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
        'rol' => $user['rol'],
        'fecha_registro' => $user['fecha_registro']
    ];

    // Mapeo de estados con id y color
    $estadosMapa = [
        'Pendiente' => ['id' => 1, 'nombre' => 'Pendiente', 'color' => '#FFC107'],
        'Abierto' => ['id' => 1, 'nombre' => 'Pendiente', 'color' => '#FFC107'],
        'En Proceso' => ['id' => 2, 'nombre' => 'En Proceso', 'color' => '#2196F3'],
        'Cerrado' => ['id' => 3, 'nombre' => 'Completada', 'color' => '#4CAF50'],
    ];

    // Obtener tickets del usuario (máximo 10)
    $tickets = $this->tickets
                    ->where('usuario_id', $user['id'])
                    ->orderBy('id', 'DESC')
                    ->findAll(10);

    // Mapear tickets para incluir status con color y último comentario
    $tareas = array_map(function ($ticket) use ($estadosMapa) {
        $estadoKey = $ticket['estado'] ?? 'Pendiente';
        $status = $estadosMapa[$estadoKey] ?? ['id' => 0, 'nombre' => $estadoKey, 'color' => '#9E9E9E'];

        // Obtener el último comentario (descripcion) de acciones_tickets
        $ultimaAccion = $this->acciones
            ->where('ticket_id', $ticket['id'])
            ->orderBy('id', 'DESC')
            ->first();

        $comentario = $ultimaAccion['descripcion'] ?? '';

        return [
            'id' => $ticket['id'],
            'latitud' => (float)$ticket['latitud'],
            'longitud' => (float)$ticket['longitud'],
            'descripcion' => $ticket['descripcion'],
            'url_encuesta' => 'https://example.com/encuesta' . $ticket['id'],
            'titulo' => $ticket['titulo'],
            'status' => $status,
            'comentario' => $comentario
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

}


