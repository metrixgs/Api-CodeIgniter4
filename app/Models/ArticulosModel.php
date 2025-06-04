<?php

namespace App\Models;

use CodeIgniter\Model;

class ArticulosModel extends Model
{
    protected $table = 'articulos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ticket_id', 'nombre', 'imagen', 'estado_id'];
}
