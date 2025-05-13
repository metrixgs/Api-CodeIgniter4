<?php


namespace App\Models;

use CodeIgniter\Model;

class CategoriaSubcategoriaPrioridadModel extends Model {
    protected $table = 'tbl_categoria_subcategoria_prioridad';
    protected $primaryKey = ['id_categoria', 'id_subcategoria', 'id_prioridad'];
    protected $allowedFields = ['id_categoria', 'id_subcategoria', 'id_prioridad'];
}