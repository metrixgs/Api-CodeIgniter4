<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ActividadesExtraModel;

class EstadoEncuestaActividadController extends ResourceController
{
    protected $modelName = 'App\Models\ActividadesExtraModel';
    protected $format    = 'json';

    public function actualizar()
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['idActividad']) || !isset($data['idRonda'])) {
            return $this->respond([
                'success' => false,
                'error' => 'Faltan parámetros idActividad o idRonda',
                'encuestaContestada' => false
            ], 400);
        }

        // Extraer número de "ACT1" y "RONDA2"
        $idActividad = (int) str_ireplace('act', '', $data['idActividad']);
        $rondaIndex = (int) str_ireplace('ronda', '', $data['idRonda']);

        // Obtener los nombres únicos de ronda en orden ascendente
        $rondas = $this->model
            ->distinct()
            ->select('ronda_nombre')
            ->orderBy('ronda_nombre', 'asc')
            ->findAll();

        if (!isset($rondas[$rondaIndex - 1])) {
            return $this->respond([
                'success' => false,
                'error' => 'Ronda no encontrada en base de datos',
                'encuestaContestada' => false
            ], 404);
        }

        $nombreRonda = $rondas[$rondaIndex - 1]['ronda_nombre'];

        // Buscar actividad exacta
        $actividad = $this->model
            ->where('id', $idActividad)
            ->where('ronda_nombre', $nombreRonda)
            ->first();

        if (!$actividad) {
            return $this->respond([
                'success' => false,
                'error' => 'Actividad no encontrada con ese ID y ronda',
                'encuestaContestada' => false
            ], 404);
        }

        if ((int)$actividad['encuesta_contestada'] === 1) {
            return $this->respond([
                'success' => true,
                'message' => 'La encuesta ya había sido contestada',
                'encuestaContestada' => true
            ]);
        }

        $this->model->update($actividad['id'], ['encuesta_contestada' => true]);

        return $this->respond([
            'success' => true,
            'error' => null,
            'encuestaContestada' => true
        ]);
    }
}
