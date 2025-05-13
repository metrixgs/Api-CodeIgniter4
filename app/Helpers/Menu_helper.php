<?php

if (!function_exists('generar_menu')) {

    /**
     * Genera el menú dinámico basado en las estructuras de menús y submenús.
     *
     * @param array $menus     Lista de menús principales.
     * @param array $submenus  Lista de submenús relacionados.
     * @return string          HTML generado del menú.
     */
    function generar_menu(array $menus, array $submenus): string {
        $html = '<div id="scrollbar">';
        $html .= '<div class="container-fluid">';
        $html .= '<div id="two-column-menu"></div>';
        $html .= '<ul class="navbar-nav" id="navbar-nav">';
        $html .= '<li class="menu-title"><span data-key="t-menu">Menu</span></li>';

        foreach ($menus as $menu) {
            $menuId = $menu['id'];
            $menuNombre = htmlspecialchars($menu['nombre']);
            $menuUrl = $menu['url'];
            $menuIcono = $menu['icono'];

            // Filtrar submenús asociados a este menú
            $submenusFiltrados = array_filter($submenus, function ($submenu) use ($menuId) {
                return $submenu['menu_id'] == $menuId && $submenu['estado'] == 1;
            });

            if (!empty($submenusFiltrados)) {
                // Menú con submenús
                $html .= '<li class="nav-item">';
                $html .= '    <a class="nav-link menu-link" href="#menu-' . $menuId . '" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="menu-' . $menuId . '">';
                $html .= '        <i class="' . htmlspecialchars($menuIcono) . '"></i> <span data-key="t-' . strtolower($menuNombre) . '">' . $menuNombre . '</span>';
                $html .= '    </a>';
                $html .= '    <div class="collapse menu-dropdown" id="menu-' . $menuId . '">';
                $html .= '        <ul class="nav nav-sm flex-column">';
                foreach ($submenusFiltrados as $submenu) {
                    $submenuNombre = htmlspecialchars($submenu['nombre']);
                    $submenuRuta = htmlspecialchars($submenu['ruta']);
                    $html .= '<li class="nav-item">';
                    $html .= '    <a href="' . base_url() . obtener_rol() . $submenuRuta . '" class="nav-link" data-key="t-' . strtolower($submenuNombre) . '">' . $submenuNombre . '</a>';
                    $html .= '</li>';
                }
                $html .= '        </ul>';
                $html .= '    </div>';
                $html .= '</li>';
            } else {
                // Menú sin submenús
                $html .= '<li class="nav-item">';
                $html .= '    <a class="nav-link menu-link" href="' . base_url() . obtener_rol() . htmlspecialchars($menuUrl) . '">';
                $html .= '        <i class="' . htmlspecialchars($menuIcono) . '"></i> <span data-key="t-' . strtolower($menuNombre) . '">' . $menuNombre . '</span>';
                $html .= '    </a>';
                $html .= '</li>';
            }
        }

        $html .= '<br><li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Soporte</span></li>';
        $html .= '<li class="nav-item">
                            <a class="nav-link menu-link" href="' . base_url() . obtener_rol() . 'configuracion/" aria-expanded="false">
                                <i class="ri-settings-2-line"></i> <span data-key="t-widgets">Cuenta</span>
                            </a>
                        </li>';
        $html .= '<li class="nav-item">
                            <a class="nav-link menu-link" href="' . base_url() . obtener_rol() . 'configuracion/novedades/" aria-expanded="false">
                                <i class="ri-rhythm-line "></i> <span data-key="t-widgets">Novedades</span>
                            </a>
                        </li>';
        $html .= '<li class="nav-item">
                            <a class="nav-link menu-link" href="' . base_url() . obtener_rol() . 'configuracion/actividad/" aria-expanded="false">
                                <i class="ri-equalizer-line"></i> <span data-key="t-widgets">Actividad</span>
                            </a>
                        </li>';

        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

}