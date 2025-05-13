<?php

if (!function_exists('enviarCorreo')) {
    function enviarCorreo($destinatarios, $asunto, $mensaje, $adjuntos = [], $esHtml = true, $bcc = []) {
        $email = \Config\Services::email();

        // Configura los destinatarios
        $email->setTo($destinatarios);

        // Configura los destinatarios ocultos (BCC)
        if (!empty($bcc)) {
            $email->setBCC($bcc);
        }

        // Configura el asunto
        $email->setSubject($asunto);

        // Configura el contenido del mensaje
        if ($esHtml) {
            $email->setMessage($mensaje);
        } else {
            $email->setMessage(strip_tags($mensaje));
        }

        // Adjunta los archivos
        if (!empty($adjuntos)) {
            foreach ($adjuntos as $adjunto) {
                $email->attach($adjunto);
            }
        }

        // Intenta enviar el correo
        if ($email->send()) {
            return true;
        } else {
            log_message('error', $email->printDebugger(['headers']));
            return false;
        }
    }
}
