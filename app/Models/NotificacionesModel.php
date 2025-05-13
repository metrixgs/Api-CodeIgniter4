<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Notificaciones
class NotificacionesModel extends Model {

    protected $table = 'tbl_notificaciones';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'usuario_id', 'titulo', 'descripcion', 'fecha_creacion'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'fecha_registro';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerNotificaciones() {
        return $this->findAll();
    }

    public function obtenerNotificacionesPorUsuario($usuario_id) {
        return $this->where('usuario_id', $usuario_id)
                        ->orderBy('id', 'DESC')
                        ->findAll();
    }

    public function obtenerNotificacion($id) {
        return $this->find($id);
    }

    public function crearNotificacion($data) {
        return $this->insert($data);
    }

    public function actualizarNotificacion($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarNotificacion($id) {
        return $this->delete($id);
    }
}
