<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoTicketsModel extends Model
{
    protected $table = 'tipo_tickets';  
    protected $primaryKey = 'id';  
    protected $allowedFields = ['nombre']; // Campos que pueden ser insertados/actualizados
    protected $returnType = 'array'; // Retornar resultados como array
}
