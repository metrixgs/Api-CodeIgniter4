<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('show_message')) {
    function show_message($message, $type = 'info') {
        // Comprueba el tipo de mensaje y establece la clase de Bootstrap correspondiente
        switch ($type) {
            case 'danger':
                $class = 'alert-danger';
                break;
            case 'success':
                $class = 'alert-success';
                break;
            case 'warning':
                $class = 'alert-warning';
                break;
            default:
                $class = 'alert-info';
                break;
        }

        // Imprime el mensaje utilizando clases de Bootstrap para estilizarlo
        echo '<div class="alert ' . $class . '">' . $message . '</div>';
    }
}
