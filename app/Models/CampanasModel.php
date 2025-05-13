<?php


namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de las Campañas
class CampanasModel extends Model {

    protected $table = 'tbl_campanas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'titulo', 'descripcion', 'fecha_inicio', 'fecha_fin', 'responsable'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function obtenerCampanas() {
        return $this->findAll();
    }

    public function obtenerCampana($id) {
        return $this->find($id);
    }

    public function crearCampana($data) {
        return $this->insert($data);
    }

    public function actualizarCampana($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarCampana($id) {
        return $this->delete($id);
    }
}

