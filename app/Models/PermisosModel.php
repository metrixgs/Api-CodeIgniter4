<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Permisos
class PermisosModel extends Model {

    protected $table = 'tbl_permisos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'modulo_id', 'usuario_id', 'lectura', 'escritura',
        'actualizacion', 'eliminacion', 'fecha_creacion'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'fecha_registro';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerPermisos() {
        return $this->findAll();
    }

    public function obtenerPermisosPorUsuario($usuario_id) {
        return $this->join('tbl_modulos', 'tbl_modulos.id = tbl_permisos.modulo_id', 'left') // Hacemos el join con tbl_modulos
                        ->select('tbl_permisos.*, tbl_modulos.nombre AS nombre_modulo') // Seleccionamos los permisos y el nombre del módulo
                        ->where('tbl_permisos.usuario_id', $usuario_id) // Filtramos por usuario_id
                        ->orderBy('tbl_permisos.id', 'ASC') // Ordenamos por id de permisos
                        ->findAll(); // Ejecutamos la consulta y obtenemos todos los resultados
    }
    public function obtenerPermisosPorUsuarioModulo($usuario_id, $modulo_id) {
        return $this->join('tbl_modulos', 'tbl_modulos.id = tbl_permisos.modulo_id', 'left') // Hacemos el join con tbl_modulos
                        ->select('tbl_permisos.*, tbl_modulos.nombre AS nombre_modulo') // Seleccionamos los permisos y el nombre del módulo
                        ->where('tbl_permisos.usuario_id', $usuario_id) // Filtramos por usuario_id
                        ->where('tbl_permisos.modulo_id', $modulo_id) // Filtramos por modulo_id
                        ->orderBy('tbl_permisos.id', 'ASC') // Ordenamos por id de permisos
                        ->findAll(); // Ejecutamos la consulta y obtenemos todos los resultados
    }

    public function obtenerPermiso($id) {
        return $this->join('tbl_modulos', 'tbl_modulos.id = tbl_permisos.modulo_id', 'left') // Hacemos el join con tbl_modulos
                        ->select('tbl_permisos.*, tbl_modulos.nombre AS nombre_modulo') // Seleccionamos los permisos y el nombre del módulo
                        ->where('tbl_permisos.id', $id) // Filtramos por el ID del permiso
                        ->first(); // Obtenemos el primer resultado (solo uno debido al ID único)
    }

    public function obtenerPermisoPorModuloTipoUsuario($modulo_id, $tipo, $usuario_id) {
        // Validar que el campo proporcionado existe en la tabla
        if (!in_array($tipo, ['lectura', 'escritura', 'actualizacion', 'eliminacion'])) {
            throw new \InvalidArgumentException("El tipo especificado no es válido.");
        }

        // Construimos la consulta con las condiciones
        $resultado = $this->join('tbl_modulos', 'tbl_modulos.id = tbl_permisos.modulo_id', 'left') // JOIN con la tabla de módulos
                ->select('tbl_permisos.*, tbl_modulos.nombre AS nombre_modulo') // Seleccionamos los permisos y el nombre del módulo
                ->where('tbl_permisos.modulo_id', $modulo_id)
                ->where("tbl_permisos.$tipo", 1) // Campo dinámico para el tipo de permiso
                ->where('tbl_permisos.usuario_id', $usuario_id)
                ->first(); // Obtenemos un único resultado
        // Si encontramos un resultado, retornamos true, de lo contrario false
        return $resultado !== null;
    }

    public function obtenerPermisoPorModulo($modulo_id) {
        return $this->join('tbl_modulos', 'tbl_modulos.id = tbl_permisos.modulo_id', 'left') // JOIN con tbl_modulos
                        ->select('tbl_permisos.*, tbl_modulos.nombre AS nombre_modulo') // Seleccionamos los permisos y el nombre del módulo
                        ->where('tbl_permisos.modulo_id', $modulo_id)
                        ->first(); // Obtenemos el primer resultado
    }

    public function crearPermiso($data) {
        return $this->insert($data);
    }

    public function actualizarPermiso($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarPermiso($id) {
        return $this->delete($id);
    }
}
