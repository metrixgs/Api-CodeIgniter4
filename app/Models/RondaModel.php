<?php
namespace App\Models;
use CodeIgniter\Model;

class RondaModel extends Model
{
    protected $table = 'tbl_rondas';
    protected $primaryKey = 'id';
    protected $allowedFields = ['campana_id', 'segmentacion_id', 'nombre', 'coordinador', 'encargado', 'fecha_actividad', 'hora_actividad', 'estado'];
}
