<?php


namespace App\Models;

use CodeIgniter\Model;

class CategoriaSubcategoriaPrioridadModel extends Model {
    protected $table = 'tbl_categoria_subcategoria_prioridad';
    protected $primaryKey = ['id_categoria', 'id_subcategoria', 'id_prioridad'];
    protected $allowedFields = ['id_categoria', 'id_subcategoria', 'id_prioridad'];


 public function formatearJerarquia(...$valores)
{
    return array_map(function ($valor, $indice) {
        return "ronda" . ($indice + 1);
    }, $valores, array_keys($valores));
}

}

