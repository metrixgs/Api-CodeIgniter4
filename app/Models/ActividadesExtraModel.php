<?php
namespace App\Models;

use CodeIgniter\Model;

class ActividadesExtraModel extends Model
{
    protected $table = 'actividades_extra';
    protected $primaryKey = 'id';     protected $allowedFields = [
        'ronda_nombre',
        'latitud',
        'longitud',
        'direccion',
        'nombreCiudadano',
        'correoCiudadano',
        'telefonoCiudadano',
        'articulosPorEntregar',
        'status_id',
         'encuesta_contestada'
    ];
    protected $returnType = 'array';
}
