<?php

namespace App\Models;

use CodeIgniter\Model;

class EncuestaIncidenciaModel extends Model
{
    protected $table = 'encuestaincidencia';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'title',
        'description',
        'questions',
        'image',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';
}
