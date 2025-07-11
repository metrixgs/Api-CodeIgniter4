<?php

namespace App\Models;

use CodeIgniter\Model;

// Modelo para la tabla de Usuarios
class UsuariosModel extends Model {

    protected $table = 'tbl_usuarios';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'rol_id', 'area_id', 'cargo', 'nombre', 'correo', 'telefono', 'contrasena', 'cuenta_activada', 'codigo_activacion','reset_token', 'token_expiry','fotoUsuario'

    ];
    protected $useTimestamps = true;
    protected $createdField = 'fecha_registro';
    protected $updatedField = 'fecha_actualizacion';
    protected $deletedField = 'fecha_eliminacion';

    public function obtenerUsuarios() {
        return $this->join('tbl_areas', 'tbl_areas.id = tbl_usuarios.area_id', 'left')
                        ->join('tbl_roles', 'tbl_roles.id = tbl_usuarios.rol_id', 'left')
                        ->select('tbl_usuarios.*, tbl_areas.nombre AS nombre_area, tbl_roles.nombre AS nombre_rol')
                        ->groupStart()  // Inicia un grupo de condiciones
                        ->where('rol_id', 1)
                        ->orWhere('rol_id', 2)  // Permite que rol_id sea 1 o 3
                        ->groupEnd()  // Finaliza el grupo de condiciones
                        ->orderBy('tbl_usuarios.id', 'ASC')
                        ->findAll();
    }

    public function obtenerUsuariosPorRol($rol_id) {
        return $this->where('rol_id', $rol_id)->findAll();
    }

    public function obtenerUsuariosAdministradores() {
        return $this->where('rol_id', 1)->findAll();
    }

    public function obtenerUsuariosPorArea($area_id) {
        return $this->where('area_id', $area_id)->findAll();
    }

    public function obtenerUsuario($id) {
        return $this->join('tbl_areas', 'tbl_areas.id = tbl_usuarios.area_id', 'left')
                        ->join('tbl_roles', 'tbl_roles.id = tbl_usuarios.rol_id', 'left')
                        ->select('tbl_usuarios.*, tbl_areas.nombre AS nombre_area, tbl_roles.nombre AS nombre_rol')
                        ->where('tbl_usuarios.id', $id)
                        ->first();
    }

    public function obtenerUsuarioPorCorreo($correo) {
        return $this->join('tbl_areas', 'tbl_areas.id = tbl_usuarios.area_id', 'left')
                        ->join('tbl_roles', 'tbl_roles.id = tbl_usuarios.rol_id', 'left')
                        ->select('tbl_usuarios.*, tbl_areas.nombre AS nombre_area, tbl_roles.nombre AS nombre_rol')
                        ->where('tbl_usuarios.correo', $correo)
                        ->first();
    }

    public function crearUsuario($data) {
        return $this->insert($data);
    }

    public function actualizarUsuario($id, $data) {
        return $this->update($id, $data);
    }

    public function eliminarUsuario($id) {
        return $this->delete($id);
    }
}
