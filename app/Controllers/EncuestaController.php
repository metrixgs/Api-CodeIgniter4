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

    // ✅ Normalizar respuestas y procesar fotos embebidas
    foreach ($respuestas as &$respuesta) {
        // 📌 Subrespuestas (preguntas anidadas)
        if (isset($respuesta['respuesta']['preguntasAnidadas'])) {
            $subrespuestas = $respuesta['respuesta']['preguntasAnidadas'];
            foreach ($subrespuestas as &$sub) {
                if (is_array($sub['respuesta'])) {
                    if (isset($sub['respuesta']['nombre'])) {
                        $sub['respuesta'] = $sub['respuesta']['nombre'];
                    } elseif (isset($sub['respuesta']['texto'])) {
                        $sub['respuesta'] = $sub['respuesta']['texto'];
                    } else {
                        $sub['respuesta'] = null;
                    }
                }
            }
            $respuesta['subrespuestas'] = $subrespuestas;
        }

        // 📸 Procesar imagen base64 si es tipo foto
        if ($respuesta['tipo'] === 'foto' && isset($respuesta['respuesta']['base64'])) {
            $url = $this->guardarImagenBase64($respuesta['respuesta']['base64'], $actividadId);
            $respuesta['respuesta'] = $url;
        }

        // ✅ Normalizar respuesta principal
        if (is_array($respuesta['respuesta'])) {
            if (isset($respuesta['respuesta']['nombre'])) {
                $respuesta['respuesta'] = $respuesta['respuesta']['nombre'];
            } elseif (isset($respuesta['respuesta']['texto'])) {
                $respuesta['respuesta'] = $respuesta['respuesta']['texto'];
            } else {
                $respuesta['respuesta'] = null;
            }
        }
    }
    unset($respuesta);

    // 🎨 Color por completitud
    $todasContestadas = true;
    foreach ($respuestas as $respuesta) {
        if (!isset($respuesta['respuesta']) || $respuesta['respuesta'] === '' || $respuesta['respuesta'] === null) {
            $todasContestadas = false;
            break;
        }
    }
    $color = $todasContestadas ? '#4CAF50' : '#F44336';

    // ✅ Actualizar actividad
    $tickets = new TicketsModel();
    $tickets->update($actividadId, [
        'estado' => 'Completada',
        'encuesta_contestada' => 1,
        'fecha_modificacion' => date('Y-m-d H:i:s'),
        'estado_id' => 1
    ]);

    // 📌 Procesar imagen externa si viene aparte (opcional)
    $imagenGuardada = null;
    if (!empty($json['foto_base64'])) {
        $imagenGuardada = $this->guardarImagenBase64($json['foto_base64'], $actividadId);
        $respuestas[] = [
            'pregunta' => 'Fotografía de la fachada',
            'respuesta' => $imagenGuardada,
            'tipo' => 'foto'
        ];
    }

    // ✅ Guardar en tabla de respuestas con manejo de errores
    $surveyModel = new SurveyResponseModel();
    $dataInsert = [
        'survey_id' => $json['survey_id'] ?? 5,
        'name'      => $json['nombre'] ?? 'Desconocido',
        'email'     => $json['correo'] ?? 'no@correo.com',
        'answers'   => json_encode($respuestas)
    ];

    if (!$surveyModel->insert($dataInsert)) {
        return $this->failValidationErrors($surveyModel->errors() ?? 'Error al guardar en la base de datos');
    }

    return $this->respond([
        'success' => true,
        'mensaje' => 'Encuesta registrada correctamente',
        'status' => [
            'id' => "3",
            'nombre' => 'Completada',
            'dibujarRuta' => false,
            'color' => $color
        ],
        'fotoGuardada' => $imagenGuardada
    ]);
}




 private function guardarImagenBase64($base64, $ticketId)
{
    // Detectar si viene con encabezado MIME
    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
        $data = substr($base64, strpos($base64, ',') + 1);
        $ext = strtolower($type[1]);
    } else {
        // Asumimos que es JPEG si no viene encabezado
        $data = $base64;
        $ext = 'jpg';
    }

    $data = base64_decode($data);
    if ($data === false) {
        return null;
    }

    $nombreArchivo = uniqid() . "_$ticketId.$ext";
    $ruta = WRITEPATH . "uploads/tickets/";

    if (!is_dir($ruta)) {
        mkdir($ruta, 0777, true);
    }

    file_put_contents($ruta . $nombreArchivo, $data);

    return base_url("writable/uploads/tickets/" . $nombreArchivo);
}

}
