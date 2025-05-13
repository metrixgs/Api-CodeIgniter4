<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Roles
class RolesModel extends Model {

    protected $table = 'tbl_roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'nombre'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'fecha_creacion';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerRoles() {
        return $this->findAll();
    }

    public function obtenerRol($id) {
        return $this->find($id);
    }

    public function crearRol($data) {
        return $this->insert($data);
    }

    public function actualizarRol($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarRol($id) {
        return $this->delete($id);
    }
}

