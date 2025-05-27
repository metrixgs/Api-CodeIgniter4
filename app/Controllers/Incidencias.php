<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use App\Models\TicketsModel;
use App\Models\AreasModel;
use App\Models\AccionesTicketsModel;
use App\Models\NotificacionesModel;
use CodeIgniter\API\ResponseTrait;

class Incidencias extends BaseController {

    use ResponseTrait;

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
        helper(['Alerts', 'Email']);

        // Configurar encabezados CORS para permitir acceso desde cualquier origen
        $this->configurarCORS();
    }

    /**
     * Configurar encabezados CORS de manera mejorada
     */
    private function configurarCORS() {
        // Verificar si ya se han enviado encabezados
        if (!headers_sent()) {
            // Permitir acceso desde cualquier origen
            header('Access-Control-Allow-Origin: *');

            // Permitir métodos HTTP
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

            // Permitir encabezados personalizados
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');

            // Establecer la duración de la caché de comprobación previa (en segundos)
            header('Access-Control-Max-Age: 3600');

            // Permitir credenciales (si es necesario para cookies o autenticación)
            // header('Access-Control-Allow-Credentials: true');
        }

        // Manejar solicitudes de comprobación previa (OPTIONS)
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            // Asegurarse de que el código de estado es 200
            http_response_code(200);
            exit(0);
        }
    }

    /**
     * Método específico para manejar solicitudes OPTIONS
     * Útil para las solicitudes preflight CORS
     */
    public function options() {
        $response = $this->response;

        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Max-Age', '3600');
        $response->setStatusCode(200);

        return $response->setBody('');
    }

    /**
     * Obtener todas las incidencias (GET)
     */
    public function index() {
        // Obtener las incidencias
        $incidencias = $this->tickets->obtenerTickets();

        // Devolver respuesta
        return $this->respond([
                    'status' => 200,
                    'error' => false,
                    'data' => $incidencias
        ]);
    }

    /**
     * Obtener una incidencia específica (GET)
     */
    public function show($id = null) {
        // Validar ID
        if ($id === null) {
            return $this->failNotFound('ID de incidencia no proporcionado');
        }

        // Obtener la incidencia
        $incidencia = $this->tickets->find($id);

        if ($incidencia === null) {
            return $this->failNotFound('Incidencia no encontrada');
        }

        return $this->respond([
                    'status' => 200,
                    'error' => false,
                    'data' => $incidencia
        ]);
    }

    /**
     * Crear una nueva incidencia (POST)
     */
   public function create() {
    // Obtener datos del cuerpo de la petición
    $json = $this->request->getJSON();

    if (empty($json)) {
        $json = $this->request->getPost();
    }

    // Validar datos
    $rules = [
        'titulo' => 'required|min_length[5]',
        'descripcion' => 'required',
        'id_area' => 'required|numeric',
        'id_usuario' => 'required|numeric'
            // Agregar más reglas según sea necesario
    ];

    if (!$this->validate($rules)) {
        return $this->failValidationErrors($this->validator->getErrors());
    }

    // Preparar datos para insertar
    $data = [
        'titulo' => $json->titulo ?? $json['titulo'],
        'descripcion' => $json->descripcion ?? $json['descripcion'],
        'id_area' => $json->id_area ?? $json['id_area'],
        'id_usuario' => $json->id_usuario ?? $json['id_usuario'],
        'estado' => 'abierto',
        'fecha_creacion' => date('Y-m-d H:i:s'),
            // Agregar más campos según sea necesario
    ];

    // Insertar en la base de datos
    $inserted = $this->tickets->insert($data);

    if (!$inserted) {
        return $this->fail('Error al crear la incidencia');
    }

    // Generar código: ID + número aleatorio de 4 dígitos
    $randomNumber = rand(1000, 9999);
    $codigo = $inserted . $randomNumber;

    // Actualizar el registro con el nuevo código
    $this->tickets->update($inserted, ['codigo' => $codigo]);

    // Registrar acción
    $this->acciones->insert([
        'id_ticket' => $inserted,
        'id_usuario' => $data['id_usuario'],
        'accion' => 'crear',
        'fecha' => date('Y-m-d H:i:s')
    ]);

    // Crear notificación para el área asignada
    $this->notificaciones->crearNotificacion($inserted, $data['id_area']);

    // Devolver respuesta exitosa con el registro actualizado
    $incidencia = $this->tickets->find($inserted);
    return $this->respondCreated([
        'status' => 201,
        'error' => false,
        'message' => 'Incidencia creada con éxito',
        'data' => $incidencia
    ]);
}


    /**
     * Actualizar una incidencia (PUT)
     */
    public function update($id = null) {
        // Validar ID
        if ($id === null) {
            return $this->failNotFound('ID de incidencia no proporcionado');
        }

        // Verificar si la incidencia existe
        $incidencia = $this->tickets->find($id);
        if ($incidencia === null) {
            return $this->failNotFound('Incidencia no encontrada');
        }

        // Obtener datos del cuerpo de la petición
        $json = $this->request->getJSON();

        if (empty($json)) {
            $json = $this->request->getRawInput();
        }

        // Validar datos
        $rules = [
            'titulo' => 'min_length[5]',
            'id_area' => 'numeric',
            'estado' => 'in_list[abierto,en_proceso,cerrado]',
                // Agregar más reglas según sea necesario
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Preparar datos para actualizar
        $data = [];

        // Solo actualizar los campos proporcionados
        if (isset($json->titulo))
            $data['titulo'] = $json->titulo;
        elseif (isset($json['titulo']))
            $data['titulo'] = $json['titulo'];

        if (isset($json->descripcion))
            $data['descripcion'] = $json->descripcion;
        elseif (isset($json['descripcion']))
            $data['descripcion'] = $json['descripcion'];

        if (isset($json->id_area))
            $data['id_area'] = $json->id_area;
        elseif (isset($json['id_area']))
            $data['id_area'] = $json['id_area'];

        if (isset($json->estado))
            $data['estado'] = $json->estado;
        elseif (isset($json['estado']))
            $data['estado'] = $json['estado'];

        // Actualizar fecha de modificación
        $data['fecha_modificacion'] = date('Y-m-d H:i:s');

        // Actualizar en la base de datos
        $updated = $this->tickets->update($id, $data);

        if (!$updated) {
            return $this->fail('Error al actualizar la incidencia');
        }

        // Registrar acción
        $id_usuario = $json->id_usuario ?? $json['id_usuario'] ?? $incidencia['id_usuario'];
        $this->acciones->insert([
            'id_ticket' => $id,
            'id_usuario' => $id_usuario,
            'accion' => 'actualizar',
            'fecha' => date('Y-m-d H:i:s')
        ]);

        // Devolver respuesta exitosa
        $incidencia = $this->tickets->find($id);
        return $this->respond([
                    'status' => 200,
                    'error' => false,
                    'message' => 'Incidencia actualizada con éxito',
                    'data' => $incidencia
        ]);
    }

    /**
     * Eliminar una incidencia (DELETE)
     */
    public function delete($id = null) {
        // Validar ID
        if ($id === null) {
            return $this->failNotFound('ID de incidencia no proporcionado');
        }

        // Verificar si la incidencia existe
        $incidencia = $this->tickets->find($id);
        if ($incidencia === null) {
            return $this->failNotFound('Incidencia no encontrada');
        }

        // Eliminar la incidencia
        $deleted = $this->tickets->delete($id);

        if (!$deleted) {
            return $this->fail('Error al eliminar la incidencia');
        }

        // Registrar acción (usando el ID de usuario de la incidencia)
        $this->acciones->insert([
            'id_ticket' => $id,
            'id_usuario' => $incidencia['id_usuario'],
            'accion' => 'eliminar',
            'fecha' => date('Y-m-d H:i:s')
        ]);

        // Devolver respuesta exitosa
        return $this->respondDeleted([
                    'status' => 200,
                    'error' => false,
                    'message' => 'Incidencia eliminada con éxito'
        ]);
    }

public function actualizarEstado() {
    $json = $this->request->getJSON(true);

    if (!isset($json['idTarea'], $json['idStatus'], $json['idUsuario'])) {
        return $this->respond([
            'success' => false,
            'message' => 'Ocurrió el siguiente error: Datos incompletos'
        ], 400);
    }

    $tarea = $this->tickets->find($json['idTarea']);
    if (!$tarea) {
        return $this->respond([
            'success' => false,
            'message' => 'Ocurrió el siguiente error: Tarea no encontrada'
        ], 404);
    }

    $usuario = $this->usuarios->find($json['idUsuario']);
    if (!$usuario) {
        return $this->respond([
            'success' => false,
            'message' => 'Ocurrió el siguiente error: Usuario no encontrado'
        ], 404);
    }

    $mapaEstados = [
        '1' => 'abierto',
        '2' => 'En Proceso',
        '3' => 'cerrado',
        '4' => 'cancelado'
    ];

    $idStatusStr = (string) $json['idStatus'];

    if (!array_key_exists($idStatusStr, $mapaEstados)) {
        return $this->respond([
            'success' => false,
            'message' => 'Ocurrió el siguiente error: Estado inválido'
        ], 400);
    }

    $estadoTexto = $mapaEstados[$idStatusStr];

    $dataUpdate = [
        'estado' => $estadoTexto,
        'fecha_modificacion' => date('Y-m-d H:i:s')
    ];

    // Agregar otros campos si vienen en el JSON
    if (isset($json['prioridad'])) {
        $dataUpdate['prioridad'] = $json['prioridad'];
    }

    if (isset($json['fechaRealizacion'])) {
        $dataUpdate['fecha_realizacion'] = $json['fechaRealizacion'];
    }

    $updated = $this->tickets->update($json['idTarea'], $dataUpdate);

    if (!$updated) {
        return $this->respond([
            'success' => false,
            'message' => 'Ocurrió el siguiente error: Error al actualizar estado'
        ], 500);
    }

    $this->acciones->insert([
        'ticket_id' => $json['idTarea'],
        'usuario_id' => $json['idUsuario'],
        'accion' => 'actualizar estado',
        'descripcion' => $json['comentario'] ?? '',
        'fecha' => date('Y-m-d H:i:s')
    ]);

    return $this->respond([
        'success' => true,
        'message' => 'El estado de la tarea se actualizó correctamente.'
    ]);
}

}
