<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RondaModel;
use CodeIgniter\API\ResponseTrait;

class RondasController extends BaseController
{
    use ResponseTrait;

    protected $rondas;

    public function __construct()
    {
        $this->rondas = new RondaModel();
        helper(['form']);
    }

     public function finalizarRonda()
    {
        $request = \Config\Services::request();

        $json = $request->getJSON();

        if (!$json || !isset($json->id_ronda)) {
            return $this->failValidationErrors('Se requiere el id_ronda');
        }

        $id = $json->id_ronda;

        $rondaModel = new \App\Models\RondaModel();

        $actualizado = $rondaModel->update($id, [
            'estado' => 'Finalizada'
        ]);

        if ($actualizado) {
            return $this->respond([
                'status' => 200,
                'message' => 'Ronda finalizada correctamente.',
                'id_finalizado' => $id
            ]);
        } else {
            return $this->fail([
                'status' => 400,
                'message' => 'No se pudo finalizar la ronda.'
            ]);
        }
    }
}