<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Areas
class AreasModel extends Model {

    protected $table = 'tbl_areas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'nombre', 'descripcion'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'fecha_creacion';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerAreas() {
        return $this->findAll();
    }

    public function obtenerArea($id) {
        return $this->find($id);
    }

    public function crearArea($data) {
        return $this->insert($data);
    }

    public function actualizarArea($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarArea($id) {
        return $this->delete($id);
    }
}
