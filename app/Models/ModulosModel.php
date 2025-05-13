<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Modulos
class ModulosModel extends Model {

    protected $table = 'tbl_modulos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'nombre', 'fecha_creacion'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'fecha_registro';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerModulos() {
        return $this->findAll();
    }

    public function obtenerModulo($id) {
        return $this->find($id);
    }

    public function crearModulo($data) {
        return $this->insert($data);
    }

    public function actualizarModulo($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarModulo($id) {
        return $this->delete($id);
    }
}

