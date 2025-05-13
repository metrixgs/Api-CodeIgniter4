<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Conversaciones
class ConversacionesModel extends Model {

    protected $table = 'tbl_conversaciones';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'usuario_id', 'fecha_creacion'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'fecha_registro';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function ObtenerConversaciones() {
        return $this->join('tbl_usuarios', 'tbl_usuarios.id = tbl_conversaciones.usuario_id')
                        ->select('tbl_conversaciones.*, tbl_usuarios.nombre AS nombre_usuario')
                        ->orderBy('tbl_conversaciones.fecha_creacion', 'DESC') // Opcional: Ordenar por fecha de creación
                        ->findAll();
    }

    public function obtenerConversacionesConUsuarios() {
        return $this->join('tbl_usuarios', 'tbl_usuarios.id = tbl_conversaciones.usuario_id')
                        ->select('tbl_conversaciones.*, tbl_usuarios.nombre AS nombre_usuario')
                        ->orderBy('tbl_conversaciones.fecha_creacion', 'DESC') // Ordena por la fecha de creación si es necesario
                        ->findAll();
    }

    public function ObtenerConversacionPorUsuario($usuario_id) {
        return $this->join('tbl_usuarios', 'tbl_usuarios.id = tbl_conversaciones.usuario_id')
                        ->select('tbl_conversaciones.*, tbl_usuarios.nombre AS nombre_usuario')
                        ->where('tbl_conversaciones.usuario_id', $usuario_id)
                        ->first();
    }

    public function ObtenerConversacion($id) {
        return $this->join('tbl_usuarios', 'tbl_usuarios.id = tbl_conversaciones.usuario_id')
                        ->select('tbl_conversaciones.*, tbl_usuarios.nombre AS nombre_usuario')
                        ->where('tbl_conversaciones.id', $id)
                        ->first();
    }

    public function crearConversacion($data) {
        return $this->insert($data);
    }

    public function actualizarConversacion($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarConversacion($id) {
        return $this->delete($id);
    }
}
