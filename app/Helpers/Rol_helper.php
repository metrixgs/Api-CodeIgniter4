<?php

if (!function_exists('obtener_nombre_rol')) {

    /**
     * Obtiene el nombre del rol basado en el ID del rol.
     *
     * @return string El nombre del rol.
     */
    function obtener_rol() {
        // Obtén el ID del rol desde la sesión
        $rolId = session('session_data.rol_id');

        // Definir los roles con su ID correspondiente
        $roles = [
            1 => 'admin/',
            2 => 'usuario/',
            3 => 'cliente/',
                // Agrega más roles según sea necesario
        ];

        // Retorna el nombre del rol si existe, de lo contrario, un valor por defecto
        return isset($roles[$rolId]) ? $roles[$rolId] : 'Rol desconocido';
    }

}
