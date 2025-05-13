<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Tareas
class TareasModel extends Model {

    protected $table = 'tbl_tareas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'ticket_id', 'emisor_id', 'receptor_id', 'area_id', 'prioridad',
        'descripcion', 'solucion', 'fecha_vencimiento', 'fecha_creacion',
        'fecha_solucion', 'estado'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'fecha_registro';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerTareas() {
        return $this->select('tbl_tareas.*, 
                          tbl_usuarios.nombre AS nombre_emisor, 
                          u_receptor.nombre AS nombre_receptor, 
                          tbl_areas.nombre AS nombre_area, 
                          tbl_tickets.identificador AS identificador_ticket')
                        ->join('tbl_usuarios', 'tbl_usuarios.id = tbl_tareas.emisor_id', 'left')
                        ->join('tbl_usuarios AS u_receptor', 'u_receptor.id = tbl_tareas.receptor_id', 'left')
                        ->join('tbl_areas', 'tbl_areas.id = tbl_tareas.area_id', 'left')
                        ->join('tbl_tickets', 'tbl_tickets.id = tbl_tareas.ticket_id', 'left')
                        ->findAll();
    }

    public function obtenerTareasPorArea($area_id) {
        return $this->select('tbl_tareas.*, 
                          tbl_usuarios.nombre AS nombre_emisor, 
                          u_receptor.nombre AS nombre_receptor, 
                          tbl_areas.nombre AS nombre_area, 
                          tbl_tickets.identificador AS identificador_ticket')
                        ->join('tbl_usuarios', 'tbl_usuarios.id = tbl_tareas.emisor_id', 'left')
                        ->join('tbl_usuarios AS u_receptor', 'u_receptor.id = tbl_tareas.receptor_id', 'left')
                        ->join('tbl_areas', 'tbl_areas.id = tbl_tareas.area_id', 'left')
                        ->join('tbl_tickets', 'tbl_tickets.id = tbl_tareas.ticket_id', 'left')
                        ->where('tbl_areas.id', $area_id)
                        ->orderBy('fecha_creacion', 'DESC')
                        ->findAll();
    }

    public function obtenerTareasPorAreaEnrango($area_id, $fecha_inicio, $fecha_fin) {
        return $this->select('tbl_tareas.*, 
                          tbl_usuarios.nombre AS nombre_emisor, 
                          u_receptor.nombre AS nombre_receptor, 
                          tbl_areas.nombre AS nombre_area, 
                          tbl_tickets.identificador AS identificador_ticket')
                        ->join('tbl_usuarios', 'tbl_usuarios.id = tbl_tareas.emisor_id', 'left')
                        ->join('tbl_usuarios AS u_receptor', 'u_receptor.id = tbl_tareas.receptor_id', 'left')
                        ->join('tbl_areas', 'tbl_areas.id = tbl_tareas.area_id', 'left')
                        ->join('tbl_tickets', 'tbl_tickets.id = tbl_tareas.ticket_id', 'left')
                        ->where('tbl_areas.id', $area_id)
                        ->where('tbl_tickets.fecha_creacion >=', $fecha_inicio)
                        ->where('tbl_tickets.fecha_creacion <=', $fecha_fin)
                        ->orderBy('fecha_creacion', 'DESC')
                        ->findAll();
    }

    public function obtenerTareasPorTicket($ticket_id) {
        return $this->select('tbl_tareas.*, 
                          tbl_usuarios.nombre AS nombre_emisor, 
                          u_receptor.nombre AS nombre_receptor, 
                          tbl_areas.nombre AS nombre_area, 
                          tbl_tickets.identificador AS identificador_ticket')
                        ->join('tbl_usuarios', 'tbl_usuarios.id = tbl_tareas.emisor_id', 'left')
                        ->join('tbl_usuarios AS u_receptor', 'u_receptor.id = tbl_tareas.receptor_id', 'left')
                        ->join('tbl_areas', 'tbl_areas.id = tbl_tareas.area_id', 'left')
                        ->join('tbl_tickets', 'tbl_tickets.id = tbl_tareas.ticket_id', 'left')
                        ->where('tbl_tareas.ticket_id', $ticket_id)
                        ->orderBy('fecha_creacion', 'DESC')
                        ->findAll();
    }

    public function obtenerTarea($id) {
        return $this->select('tbl_tareas.*, 
                          tbl_usuarios.nombre AS nombre_emisor, 
                          u_receptor.nombre AS nombre_receptor, 
                          tbl_areas.nombre AS nombre_area, 
                          tbl_tickets.identificador AS identificador_ticket')
                        ->join('tbl_usuarios', 'tbl_usuarios.id = tbl_tareas.emisor_id', 'left')
                        ->join('tbl_usuarios AS u_receptor', 'u_receptor.id = tbl_tareas.receptor_id', 'left')
                        ->join('tbl_areas', 'tbl_areas.id = tbl_tareas.area_id', 'left')
                        ->join('tbl_tickets', 'tbl_tickets.id = tbl_tareas.ticket_id', 'left')
                        ->where('tbl_tareas.id', $id)
                        ->first();
    }

    public function crearTarea($data) {
        return $this->insert($data);
    }

    public function actualizarTarea($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarTarea($id) {
        return $this->delete($id);
    }
}
