<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Notificaciones
class SLAModel extends Model {

    protected $table = 'tbl_sla';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'titulo', 'tiempo', 'color', 'descripcion'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function obtenerSLAs() {
        return $this->findAll();
    }

    public function obtenerSLA($id) {
        return $this->find($id);
    }

    public function crearSLA($data) {
        return $this->insert($data);
    }

    public function actualizarSLA($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarSLA($id) {
        return $this->delete($id);
    }
}

