<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    /**
     * Método que se ejecuta antes de la solicitud
     *
     * @param RequestInterface $request
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Agregar encabezados CORS para permitir el acceso desde cualquier origen
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        
        // Si es una petición OPTIONS, devolver 200 OK sin más procesamiento
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'OPTIONS') {
            header('HTTP/1.1 200 OK');
            exit();
        }
    }

    /**
     * Método que se ejecuta después de la solicitud
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Agregar encabezados CORS a la respuesta
        $response->setHeader('Access-Control-Allow-Origin', '*')
                 ->setHeader('Access-Control-Allow-Headers', 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization')
                 ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        
        return $response;
    }
}