<?php

namespace App\Models;

use CodeIgniter\Model;

class EstadosTareaModel extends Model
{
    protected $table = 'tbl_estados_tarea'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Clave primaria

    protected $allowedFields = ['nombre']; // Campos que se pueden insertar/actualizar

    protected $returnType = 'array'; // Puedes usar 'object' si prefieres
    protected $useTimestamps = false; // No hay campos created_at ni updated_at por ahora
}
