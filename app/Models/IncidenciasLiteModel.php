<?php

namespace App\Models;

use CodeIgniter\Model;

class IncidenciasLiteModel extends Model
{
    protected $table = 'tbl_tickets'; // La tabla principal sigue siendo tbl_tickets
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function obtenerIncidenciasLite()
    {
        return $this->select('
        tbl_tickets.id AS id,
            u2.nombre AS Cliente,
            tbl_tickets.campana_id AS ID_Campana,
            tbl_tickets.ronda_id AS ID_Ronda,
            tbl_tickets.identificador AS ID_Ticket,
            tbl_tickets.fecha_creacion AS Fecha,
            tbl_categorias.nombre AS Categoria,
            tbl_subcategorias.nombre AS Clasificacion,
            tbl_prioridades.nombre AS Prioridad,
            tbl_tickets.estado AS Estatus,
            tbl_areas.nombre AS Area_Responsable,
            u1.nombre AS Operador,
            tbl_tickets.estado_p AS Estado,
            tbl_tickets.municipio AS Municipio,
            tbl_tickets.codigo_postal AS Codigo_Postal,
            tbl_tickets.df AS Distrito_Federal,
            tbl_tickets.dl AS Distrito_Local,
            tbl_tickets.seccion_electoral AS Seccion_Electoral,
            tbl_tickets.latitud AS Latitud,
            tbl_tickets.longitud AS Longitud,
            (SELECT ruta FROM tbl_documentos_tickets d WHERE d.ticket_id = tbl_tickets.id ORDER BY fecha_subida DESC LIMIT 1) AS Foto_Firmada
        ')
        ->join('tbl_usuarios AS u1', 'tbl_tickets.usuario_id = u1.id', 'left')
        ->join('tbl_usuarios AS u2', 'tbl_tickets.cliente_id = u2.id', 'left')
        ->join('tbl_categorias', 'tbl_tickets.categoria_id = tbl_categorias.id_categoria', 'left')
        ->join('tbl_subcategorias', 'tbl_tickets.subcategoria_id = tbl_subcategorias.id_subcategoria', 'left')
        ->join('tbl_prioridades', 'tbl_tickets.prioridad_id = tbl_prioridades.id_prioridad', 'left')
        ->join('tbl_areas', 'tbl_tickets.area_id = tbl_areas.id', 'left')
        ->orderBy('tbl_tickets.id', 'DESC')
        ->findAll();
    }

    public function obtenerIncidenciaLitePorId($id)
    {
        return $this->select('
            u2.nombre AS Cliente,
            tbl_tickets.campana_id AS ID_Campana,
            tbl_tickets.ronda_id AS ID_Ronda,
            tbl_tickets.identificador AS ID_Ticket,
            tbl_tickets.fecha_creacion AS Fecha,
            tbl_categorias.nombre AS Categoria,
            tbl_subcategorias.nombre AS Clasificacion,
            tbl_prioridades.nombre AS Prioridad,
            tbl_tickets.estado AS Estatus,
            tbl_areas.nombre AS Area_Responsable,
            u1.nombre AS Operador,
            tbl_tickets.estado_p AS Estado,
            tbl_tickets.municipio AS Municipio,
            tbl_tickets.codigo_postal AS Codigo_Postal,
            tbl_tickets.df AS Distrito_Federal,
            tbl_tickets.dl AS Distrito_Local,
            tbl_tickets.seccion_electoral AS Seccion_Electoral,
            tbl_tickets.latitud AS Latitud,
            tbl_tickets.longitud AS Longitud,
            (SELECT ruta FROM tbl_documentos_tickets d WHERE d.ticket_id = tbl_tickets.id ORDER BY fecha_subida DESC LIMIT 1) AS Foto_Firmada
        ')
        ->join('tbl_usuarios AS u1', 'tbl_tickets.usuario_id = u1.id', 'left')
        ->join('tbl_usuarios AS u2', 'tbl_tickets.cliente_id = u2.id', 'left')
        ->join('tbl_categorias', 'tbl_tickets.categoria_id = tbl_categorias.id_categoria', 'left')
        ->join('tbl_subcategorias', 'tbl_tickets.subcategoria_id = tbl_subcategorias.id_subcategoria', 'left')
        ->join('tbl_prioridades', 'tbl_tickets.prioridad_id = tbl_prioridades.id_prioridad', 'left')
        ->join('tbl_areas', 'tbl_tickets.area_id = tbl_areas.id', 'left')
        ->where('tbl_tickets.id', $id)
        ->first();
    }
}