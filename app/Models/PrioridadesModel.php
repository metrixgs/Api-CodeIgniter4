<?php


namespace App\Models;

use CodeIgniter\Model;


class PrioridadesModel extends Model {
    protected $table = 'tbl_prioridades';
    protected $primaryKey = 'id_prioridad';
    protected $allowedFields = ['nombre'];
}