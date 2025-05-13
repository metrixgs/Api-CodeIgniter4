<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Configuracion
class ConfiguracionModel extends Model {

    protected $table = 'tbl_configuracion';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'from_email', 'from_name', 'smtp_host', 'smtp_user', 
        'smtp_pass', 'smtp_port', 'smtp_crypto', 'bcc_addresses', 
        'mail_type', 'charset', 'priority'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function obtenerConfiguraciones() {
        return $this->findAll();
    }

    public function obtenerConfiguracion($id) {
        return $this->find($id);
    }

    public function crearConfiguracion($data) {
        return $this->insert($data);
    }

    public function actualizarConfiguracion($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarConfiguracion($id) {
        return $this->delete($id);
    }
}

