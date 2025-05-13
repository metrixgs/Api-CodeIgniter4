<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use CodeIgniter\API\ResponseTrait;

class Login extends BaseController
{
    use ResponseTrait;

    protected $usuarios;

    public function __construct()
    {
        // Instanciar el modelo de usuarios
        $this->usuarios = new UsuariosModel();

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
        // Obtener datos del cuerpo de la petición
        $json = $this->request->getJSON();

        if (empty($json)) {
            $json = $this->request->getPost();
        }

        // Validar datos
        $rules = [
            'correo' => 'required|valid_email',
            'contrasena' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Extraer correo y contraseña
        $correo = $json->correo ?? $json['correo'];
        $contrasena = $json->contrasena ?? $json['contrasena'];

        // Buscar usuario por correo
        $user = $this->usuarios->where('correo', $correo)->first();

        if ($user === null) {
            return $this->failUnauthorized('Correo electrónico no registrado');
        }

        // Verificar contraseña (comparación directa para texto plano)
        if ($contrasena !== $user['contrasena']) {
            return $this->failUnauthorized('Contraseña incorrecta');
        }

        // Preparar datos de respuesta (excluir contraseña)
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

        // Devolver respuesta exitosa
        return $this->respond([
            'status' => 200,
            'error' => false,
            'message' => 'Inicio de sesión exitoso',
            'data' => $userData
        ]);
    }
}