<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Puedes personalizar esta lógica para permitir solo ciertos IPs
        $allowedIPs = ['127.0.0.1']; // IPs permitidos (por ejemplo, tu IP)

        if (!in_array($request->getIPAddress(), $allowedIPs)) {
            // Si no es una IP permitida, muestra la página de mantenimiento
            echo view('maintenance'); // Vista que mostrarás cuando esté en mantenimiento
            exit(); // Detén la ejecución
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita nada aquí
    }
}
