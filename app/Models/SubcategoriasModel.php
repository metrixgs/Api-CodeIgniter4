<?php


namespace App\Models;

use CodeIgniter\Model;

class SubcategoriasModel extends Model {

    protected $returnType = 'array';

    protected $table = 'tbl_subcategorias';
    protected $primaryKey = 'id_subcategoria';
    protected $allowedFields = ['nombre'];
}