<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Archivos
class ArchivosModel extends Model {

    protected $table = 'tbl_documentos_tickets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'ticket_id', 'usuario_id', 'descripcion', 'ruta', 'tipo_mime', 'tamano', 'fecha_subida'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'fecha_creacion';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerArchivos() {
        return $this->select('tbl_documentos_tickets.*, tbl_usuarios.nombre AS nombre_usuario')
                        ->join('tbl_usuarios', 'tbl_documentos_tickets.usuario_id = tbl_usuarios.id', 'left')
                        ->findAll();
    }

    public function obtenerArchivosPorTicket($ticket_id) {
        return $this->select('tbl_documentos_tickets.*, tbl_usuarios.nombre AS nombre_usuario')
                        ->join('tbl_usuarios', 'tbl_documentos_tickets.usuario_id = tbl_usuarios.id', 'left')
                        ->where('tbl_documentos_tickets.ticket_id', $ticket_id)
                        ->findAll();
    }

    public function obtenerArchivo($id) {
        return $this->select('tbl_documentos_tickets.*, tbl_usuarios.nombre AS nombre_usuario')
                        ->join('tbl_usuarios', 'tbl_documentos_tickets.usuario_id = tbl_usuarios.id', 'left')
                        ->where('tbl_documentos_tickets.id', $id)
                        ->first();
    }

    public function crearArchivo($data) {
        return $this->insert($data);
    }

    public function actualizarArchivo($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarArchivo($id) {
        return $this->delete($id);
    }
}
