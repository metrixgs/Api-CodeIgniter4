<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TicketsModel;
use App\Models\SurveyResponseModel;
use CodeIgniter\API\ResponseTrait;

class EncuestaController extends BaseController
{
    use ResponseTrait;

    public function completarActividad()
    {
        $json = $this->request->getJSON(true);

        if (empty($json['actividad_id']) || empty($json['ronda_id']) || empty($json['respuestas'])) {
            return $this->failValidationErrors('Faltan parámetros requeridos');
        }

        $actividadId = str_replace('act', '', $json['actividad_id']);
        $respuestas = $json['respuestas'];
        $completar = false;

        // Verificar si alguna respuesta tiene statusID = "3"
        foreach ($respuestas as $respuesta) {
            $statusID = $respuesta['respuesta']['statusID'] ?? null;
            if ($statusID === "3") {
                $completar = true;
                break;
            }
        }

        $tickets = new TicketsModel();

        // Actualizar el ticket
        $tickets->update($actividadId, [
            'estado' => $completar ? 'Completada' : 'Pendiente',
            'encuesta_contestada' => 1
        ]);

        // Guardar imagen si se envió
        $imagenGuardada = null;
        if (!empty($json['foto_base64'])) {
            $imagenGuardada = $this->guardarImagenBase64($json['foto_base64'], $actividadId);
        }

        // Guardar las respuestas en la tabla survey_responses
        $surveyModel = new SurveyResponseModel();
        $surveyModel->insert([
         'survey_id' => 4,
            'name' => $json['nombre_usuario'] ?? 'Desconocido',
            'email' => $json['correo_usuario'] ?? 'no@correo.com',
            'answers' => json_encode($respuestas)
        ]);

        // Obtener el estado final para respuesta
        $ticket = $tickets->find($actividadId);
        $estadoNombre = $ticket['estado'] ?? 'Pendiente';
        $color = '#000000';

        return $this->respond([
            'success' => true,
            'mensaje' => $completar
                ? 'Actividad completada y encuesta registrada'
                : 'Encuesta registrada sin completar la actividad',
            'status' => [
                'id' => 1,
                'nombre' => $estadoNombre,
                'color' => $color,
                'dibujarRuta' => false
            ],
            'fotoGuardada' => $imagenGuardada
        ]);
    }

    private function guardarImagenBase64($base64, $ticketId)
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            return null;
        }

        $data = substr($base64, strpos($base64, ',') + 1);
        $data = base64_decode($data);

        if ($data === false) {
            return null;
        }

        $ext = strtolower($type[1]);
        $nombreArchivo = uniqid() . "_$ticketId.$ext";
        $ruta = WRITEPATH . "uploads/tickets/";

        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
        }

        file_put_contents($ruta . $nombreArchivo, $data);

        return base_url("writable/uploads/tickets/" . $nombreArchivo);
    }
}
