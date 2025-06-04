<?php

namespace App\Models;

use CodeIgniter\Model;

class EstadosArticuloModel extends Model
{
    protected $table = 'estados_articulo';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'color'];
}
