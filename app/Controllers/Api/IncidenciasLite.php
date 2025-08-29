<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\IncidenciasLiteModel;
use CodeIgniter\API\ResponseTrait;

class IncidenciasLite extends BaseController
{
    use ResponseTrait;

    protected $incidenciasLiteModel;

    public function __construct()
    {
        $this->incidenciasLiteModel = new IncidenciasLiteModel();
        $this->configurarCORS();
    }

    /**
     * Configurar encabezados CORS de manera mejorada
     */
    private function configurarCORS()
    {
        // Verificar si ya se han enviado encabezados
        if (!headers_sent()) {
            // Permitir acceso desde cualquier origen
            header('Access-Control-Allow-Origin: *');

            // Permitir métodos HTTP
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

            // Permitir encabezados personalizados
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');

            // Establecer la duración de la caché de comprobación previa (en segundos)
            header('Access-Control-Max-Age: 3600');
        }

        // Manejar solicitudes de comprobación previa (OPTIONS)
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit(0);
        }
    }

    /**
     * Método específico para manejar solicitudes OPTIONS
     * Útil para las solicitudes preflight CORS
     */
    public function options()
    {
        $response = $this->response;
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Max-Age', '3600');
        $response->setStatusCode(200);
        return $response->setBody('');
    }

    public function index()
    {
        $incidencias = $this->incidenciasLiteModel->obtenerIncidenciasLite();

        // Eliminar el campo 'Foto_Firmada' de cada incidencia
        foreach ($incidencias as &$incidencia) {
            if (isset($incidencia['Foto_Firmada'])) {
                unset($incidencia['Foto_Firmada']);
            }
        }

        return $this->respond([
            'status' => 200,
            'error' => false,
            'data' => $incidencias
        ]);
    }

    /**
     * Obtener una incidencia específica por ID (GET)
     */
    public function show($id = null)
    {
        // Validar ID
        if ($id === null) {
            return $this->failNotFound('ID de incidencia no proporcionado');
        }

        // Obtener la incidencia
        $incidencia = $this->incidenciasLiteModel->obtenerIncidenciaLitePorId($id);

        if ($incidencia === null) {
            return $this->failNotFound('Incidencia no encontrada');
        }

        return $this->respond([
            'status' => 200,
            'error' => false,
            'data' => $incidencia
        ]);
    }
}