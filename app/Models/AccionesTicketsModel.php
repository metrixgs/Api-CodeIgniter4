<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Acciones de los Tickets
class AccionesTicketsModel extends Model {

    protected $table = 'tbl_acciones_ticket';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'ticket_id', 'usuario_id', 'area_id', 'titulo', 'descripcion', 'fecha_creacion'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'fecha_creacion';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerAcciones() {
        return $this->select('tbl_acciones_ticket.*, 
                          u1.nombre AS nombre_usuario, 
                          tbl_areas.nombre AS nombre_area')
                        ->join('tbl_usuarios AS u1', 'tbl_acciones_ticket.usuario_id = u1.id', 'left')
                        ->join('tbl_areas', 'tbl_acciones_ticket.area_id = tbl_areas.id', 'left')
                        ->orderBy('fecha_creacion', 'DESC')
                        ->findAll(); // Obtenemos todos los resultados
    }

    public function obtenerAccionesPorTicket($ticket_id) {
        return $this->select('tbl_acciones_ticket.*, 
                          u1.nombre AS nombre_usuario, 
                          tbl_areas.nombre AS nombre_area')
                        ->join('tbl_usuarios AS u1', 'tbl_acciones_ticket.usuario_id = u1.id', 'left')
                        ->join('tbl_areas', 'tbl_acciones_ticket.area_id = tbl_areas.id', 'left')
                        ->where('ticket_id', $ticket_id) // Filtramos por ticket_id
                        ->orderBy('fecha_creacion', 'DESC')
                        ->findAll(); // Obtenemos todos los resultados
    }

    public function obtenerAccion($id) {
        return $this->select('tbl_acciones_ticket.*, 
                          u1.nombre AS nombre_usuario, 
                          tbl_areas.nombre AS nombre_area')
                        ->join('tbl_usuarios AS u1', 'tbl_acciones_ticket.usuario_id = u1.id', 'left')
                        ->join('tbl_areas', 'tbl_acciones_ticket.area_id = tbl_areas.id', 'left')
                        ->where('tbl_acciones_ticket.id', $id) // Filtramos por el id de la acción
                        ->first(); // Devuelve una sola fila
    }

    public function crearAccion($data) {
        return $this->insert($data);
    }

    public function actualizarAccion($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarAccion($id) {
        return $this->delete($id);
    }
}
