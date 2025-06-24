<?php


namespace App\Models;

use CodeIgniter\Model;

class CategoriasModel extends Model {

    protected $returnType = 'array';

    protected $table = 'tbl_categorias';
    protected $primaryKey = 'id_categoria';
    protected $allowedFields = ['nombre'];
}