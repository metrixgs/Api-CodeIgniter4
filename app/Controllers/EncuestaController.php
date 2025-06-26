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

    // 1️⃣ Mapear statusID → estado (enum)
    $estadosEnum = [
        1 => 'Baldio',
        2 => 'Abandonada',
        3 => 'Completada',
        4 => 'Cancelada',
        5 => 'No quiere interactuar',
        6 => 'Volver',
        7 => 'Contacto / Invitación',
        8 => 'Pendiente'
    ];

    $estadoFinal = null;
    $statusIdUsado = null;

    foreach ($respuestas as $respuesta) {
        $statusID = $respuesta['respuesta']['statusID'] ?? null;
        if ($statusID && isset($estadosEnum[(int)$statusID])) {
            $estadoFinal = $estadosEnum[(int)$statusID];
            $statusIdUsado = (int)$statusID;
            break;
        }
    }

    if (!$estadoFinal) {
        $estadoFinal = 'Pendiente';
        $statusIdUsado = 8;
    }

    // 2️⃣ Lógica extra: si el estadoFinal NO es 'Pendiente', entonces poner estado_id = 1
    $extraFields = [];
    if ($estadoFinal !== 'Pendiente') {
        $extraFields['estado_id'] = 1;
    }

    // 3️⃣ Actualizar el ticket
    $tickets = new TicketsModel();
    $tickets->update($actividadId, array_merge([
        'estado' => $estadoFinal,
        'encuesta_contestada' => 1,
        'fecha_modificacion' => date('Y-m-d H:i:s')
    ], $extraFields));

    // 4️⃣ Guardar imagen si se envió
    $imagenGuardada = null;
    if (!empty($json['foto_base64'])) {
        $imagenGuardada = $this->guardarImagenBase64($json['foto_base64'], $actividadId);
    }

    // 5️⃣ Guardar las respuestas
    $surveyModel = new \App\Models\SurveyResponseModel();
    $surveyModel->insert([
        'survey_id' => $json['survey_id'] ?? 4,
        'name' => $json['nombre_usuario'] ?? 'Desconocido',
        'email' => $json['correo_usuario'] ?? 'no@correo.com',
        'answers' => json_encode($respuestas),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // 6️⃣ Respuesta
    return $this->respond([
        'success' => true,
        'mensaje' => 'Encuesta registrada correctamente',
        'status' => [
            'id' => $statusIdUsado,
            'nombre' => $estadoFinal,
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
