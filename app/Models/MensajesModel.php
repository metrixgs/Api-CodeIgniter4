<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de los Mensajes
class MensajesModel extends Model {

    protected $table = 'tbl_mensajes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'conversacion_id', 'remitente_id', 'mensaje', 'fecha_creacion', 'leido'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'fecha_registro';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerMensajes() {
        return $this->findAll();
    }

    public function obtenerMensajesPorConversacion($conversacion_id) {
        return $this->join('tbl_usuarios', 'tbl_usuarios.id = tbl_mensajes.remitente_id')
                        ->select('tbl_mensajes.*, tbl_usuarios.nombre AS nombre_remitente')
                        ->where('tbl_mensajes.conversacion_id', $conversacion_id)
                        ->orderBy('fecha_creacion', 'ASC')
                        ->findAll();
    }

    public function obtenerMensaje($id) {
        return $this->find($id);
    }

    public function crearMensaje($data) {
        return $this->insert($data);
    }

    public function actualizarMensaje($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarMensaje($id) {
        return $this->delete($id);
    }
}
