<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

class AutenticacionFilter implements FilterInterface {

    public function before(RequestInterface $request, $arguments = null) {
        $session = Services::session(); // Obtén el servicio de sesión

        $session_data = $session->get('session_data');
        if ($session_data) {
            # Validamos el rol del usuario...
            switch (session('session_data.rol_id')) {
                case 1:
                    # El usuario es Administrador...
                    return redirect()->to('admin/panel');
                    break;
                case 2:
                    # El usuario es Empresa...
                    return redirect()->to('usuario/panel');
                    break;
                case 3:
                    # El usuario es Condominio...
                    return redirect()->to('cliente/panel');
                    break;
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {
        // Puedes agregar lógica de filtrado después de que se ejecute la acción de la ruta si es necesario.
        // No es necesario agregar código en este método si no tienes una lógica específica para después de la acción.
    }
}
