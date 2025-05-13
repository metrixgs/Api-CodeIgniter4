<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de los archivos de las tareas
class DocumentosTareasModel extends Model {

    protected $table = 'tbl_documentos_tareas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'tarea_id', 'usuario_id', 'comentario', 'archivo', 'fecha_creacion'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'fecha_registro';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerDocumentos() {
        return $this->select('tbl_documentos_tareas.*, tbl_usuarios.nombre as nombre_usuario')
                        ->join('tbl_usuarios', 'tbl_usuarios.id = tbl_documentos_tareas.usuario_id')
                        ->findAll();
    }

    public function obtenerDocumentosPorTareas($tarea_id) {
        return $this->select('tbl_documentos_tareas.*, tbl_usuarios.nombre as nombre_usuario')
                        ->join('tbl_usuarios', 'tbl_usuarios.id = tbl_documentos_tareas.usuario_id')
                        ->where('tarea_id', $tarea_id)
                        ->findAll();
    }

    public function obtenerDocumento($id) {
        return $this->select('tbl_documentos_tareas.*, tbl_usuarios.nombre as nombre_usuario')
                        ->join('tbl_usuarios', 'tbl_usuarios.id = tbl_documentos_tareas.usuario_id')
                        ->where('tbl_documentos_tareas.id', $id)
                        ->first();
    }

    public function crearDocumento($data) {
        return $this->insert($data);
    }

    public function actualizarDocumento($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarDocumento($id) {
        return $this->delete($id);
    }
}
