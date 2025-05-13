<?php

if (!function_exists('mostrar_alerta')) {

    function mostrar_alerta() {
        // Verifica si hay información en la sesión
        if (session()->has('titulo') && session()->has('mensaje') && session()->has('tipo')) {
            // Iconos de Bootstrap para cada tipo de alerta
            $iconos = [
                'success' => 'ri-check-line',
                'info' => 'ri-information-line',
                'danger' => 'ri-error-warning-line',
                'warning' => 'ri-alert-line',
                'primary' => 'ri-bell-line',
                'secondary' => 'ri-circle-line'
            ];

            // Obtiene el tipo de alerta y el icono correspondiente
            $tipo = session('tipo');
            $icono = isset($iconos[$tipo]) ? $iconos[$tipo] : 'ri-question-line'; // Icono por defecto
            // Genera el HTML de la alerta con la estructura deseada
            $alerta = '
            <div class="alert alert-' . $tipo . ' alert-border-left alert-dismissible fade show material-shadow" role="alert">
                <i class="' . $icono . ' me-3 align-middle fs-16"></i><strong>' . session('titulo') . '</strong> - ' . session('mensaje') . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';

            return $alerta;
        }

        return ''; // Si no hay alertas, devuelve una cadena vacía
    }

}