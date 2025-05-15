<?php

use CodeIgniter\Router\RouteCollection;

// Rutas de API

$routes->get('/', function () {
    return "API funcionando";
});

$routes->group('api', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('incidencias', 'Incidencias::index');
    $routes->get('incidencias/(:num)', 'Incidencias::show/$1');
    $routes->post('incidencias', 'Incidencias::create');
    $routes->put('incidencias/(:num)', 'Incidencias::update/$1');
    $routes->delete('incidencias/(:num)', 'Incidencias::delete/$1'); 
    
    $routes->get('reporte', 'Reporte::inicio');
    $routes->get('reporte/prioridades', 'Reporte::listarPrioridades');
    $routes->get('reporte/categorias', 'Reporte::listarCategorias');
    $routes->get('reporte/subcategorias', 'Reporte::listarSubcategorias');
    $routes->get('reporte/completo', 'Reporte::listarReporteCompleto');
    $routes->post('reporte/crear', 'Reporte::crearTicket');
    // Ruta para Login
    $routes->post('login', 'Login::index');


    $routes->get('surveys', 'SurveyController::index');
    $routes->get('surveys/(:num)', 'SurveyController::show/$1');
    $routes->post('surveys', 'SurveyController::store');
    $routes->post('surveys/(:num)/responses', 'SurveyController::storeResponse/$1');
    $routes->get('surveys/(:num)/responses', 'SurveyController::showResponses/$1');

});
  

    
