<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use App\Models\TicketsModel;
use App\Models\AreasModel;
use App\Models\AccionesTicketsModel;
use App\Models\NotificacionesModel;

class Landing extends BaseController {

    protected $usuarios;
    protected $tickets;
    protected $areas;
    protected $acciones;
    protected $notificaciones;

    public function __construct() {
        // Instanciar los modelos
        $this->usuarios = new UsuariosModel();
        $this->tickets = new TicketsModel();
        $this->areas = new AreasModel();
        $this->acciones = new AccionesTicketsModel();
        $this->notificaciones = new NotificacionesModel();

        # Cargar los Helpers
        helper('Alerts');
        helper('Email');
    }

    public function index() {
        print_r("Bad Request");
    }
}
