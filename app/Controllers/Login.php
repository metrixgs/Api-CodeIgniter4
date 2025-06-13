<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RondaModel;
use App\Models\UsuariosModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\TicketsModel;
use App\Models\AccionesTicketsModel;
use App\Models\EncuestaIncidenciaModel;
use App\Models\RolesModel;
use App\Models\EstadosTareaModel;
use App\Models\ArticulosModel;
use App\Models\EstadosArticuloModel;
use App\Models\ActividadesExtraModel;


class Login extends BaseController
{
    use ResponseTrait;

    protected $usuarios;
    protected $tickets;
     protected $roles;
      protected $estadosTarea;
      protected $articulos;
        protected $estadosArticulo;
          protected $rondas;
  protected $acciones; 
    public function __construct()
    {
        // Instanciar el modelo de usuarios
        $this->usuarios = new UsuariosModel();
        $this->tickets = new TicketsModel();
      $this->acciones = new AccionesTicketsModel();
      $this->encuesta = new EncuestaIncidenciaModel();
        $this->roles = new RolesModel(); 
         $this->estadosTarea = new EstadosTareaModel();
         $this->articulos = new ArticulosModel();
    $this->estadosArticulo = new EstadosArticuloModel();
     $this->rondas = new RondaModel();
     $this->actividadesExtra = new \App\Models\ActividadesExtraModel();

        // Cargar los Helpers
        helper(['Alerts', 'Email']);

        // Configurar encabezados CORS
        $this->configurarCORS();
    }

    /**
     * Configurar encabezados CORS
     */
    private function configurarCORS()
    {
        if (!headers_sent()) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
            header('Access-Control-Max-Age: 3600');
        }

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit(0);
        }
    }

    /**
     * Manejar solicitudes OPTIONS para CORS
     */
    public function options()
    {
        $response = $this->response;

        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->setHeader('Access-Control-Max-Age', '3600');
        $response->setStatusCode(200);

        return $response->setBody('');
    } 
   public function index()
{
    $json = (array) ($this->request->getJSON() ?? $this->request->getPost());

    $rules = [
        'correo' => 'required|valid_email',
        'contrasena' => 'required|min_length[6]'
    ];

    if (!$this->validate($rules)) {
        return $this->failValidationErrors($this->validator->getErrors());
    }

    $correo = $json['correo'];
    $contrasena = $json['contrasena'];

    $user = $this->usuarios->where('correo', $correo)->first();

    if ($user === null) {
        return $this->failUnauthorized('Correo electrónico no registrado');
    }

    if ($contrasena !== $user['contrasena']) {
        return $this->failUnauthorized('Contraseña incorrecta');
    }

    // Encuesta
    $encuestaBD = $this->encuesta->first();
    $questionsRaw = trim($encuestaBD['questions'] ?? '');
    if (!str_starts_with($questionsRaw, '[')) {
        $questionsRaw = '[' . $questionsRaw . ']';
    }
    $questionsArray = json_decode($questionsRaw, true);
    foreach ($questionsArray as &$question) {
        if (isset($question['options']) && is_array($question['options'])) {
            $question['options'] = array_map(function ($opt) {
                if (is_array($opt)) {
                    if (!isset($opt['status'])) {
                        $opt['status'] = (strtolower($opt['text']) === 'baldío') ? "1" : null;
                    }
                    return $opt;
                } else {
                    return [
                        'text' => $opt,
                        'status' => (strtolower($opt) === 'baldío') ? "1" : null
                    ];
                }
            }, $question['options']);
        }
    }

    // Estados para tareas (tickets)
    $mapaEstados = [
        'baldio' => 1, 'abandonada' => 2, 'completada' => 3, 'cancelada' => 4,
        'no quiere interactuar' => 5, 'volver' => 6, 'contacto / invitacion' => 7, 'pendiente' => 8
    ];

    // Estados para artículos
    $estadosArticulo = $this->estadosArticulo->findAll();
    $estadosMapArticulos = [];
    foreach ($estadosArticulo as $estado) {
        $estadosMapArticulos[$estado['id']] = ['nombre' => $estado['nombre'], 'color' => $estado['color']];
    }

    // Estados para actividades_extra
    $estadosTarea = model('App\\Models\\EstadosTareaModel')->findAll();
    $estadosMapActividades = [];
    foreach ($estadosTarea as $estado) {
        $estadosMapActividades[$estado['id']] = $estado['nombre'];
    }

    $articulosPorTicket = function ($ticketId) use ($estadosMapArticulos) {
        $db = \Config\Database::connect();
        $builder = $db->table('articulos a');
        $builder->select('a.id, a.nombre, a.imagen, ta.estado_id AS estado_id_articulo');
        $builder->join('ticket_articulo ta', 'a.id = ta.articulo_id AND ta.ticket_id = ' . $ticketId, 'left');
        $result = $builder->get()->getResultArray();

        return array_map(function ($articulo) use ($estadosMapArticulos) {
            $estadoId = (int)($articulo['estado_id_articulo'] ?? 0);
            $estado = $estadosMapArticulos[$estadoId] ?? ['nombre' => '', 'color' => null];

            return [
                'id' => $articulo['id'],
                'nombre' => $articulo['nombre'],
                'imagen' => $articulo['imagen'] ? base_url('uploads/articulos/' . $articulo['imagen']) : null,
                'status' => [
                    'id' => $estadoId,
                    'nombre' => $estado['nombre'],
                    'color' => $estado['color']
                ]
            ];
        }, $result);
    };

    $tickets = $this->tickets
        ->select('tbl_tickets.*, u1.nombre AS nombre_usuario, u2.nombre AS nombre_cliente')
        ->join('tbl_usuarios AS u1', 'tbl_tickets.usuario_id = u1.id', 'left')
        ->join('tbl_usuarios AS u2', 'tbl_tickets.cliente_id = u2.id', 'left')
        ->where('tbl_tickets.cuenta_id', $user['cuenta_id'])
        ->orderBy('tbl_tickets.id', 'DESC')
        ->findAll(10);

    $tareas = array_map(function ($ticket) use ($mapaEstados, $articulosPorTicket) {
        $estadoNombre = strtolower(trim($ticket['estado'] ?? 'sin estado'));
        $estadoNombre = str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $estadoNombre);
        $idEstado = $mapaEstados[$estadoNombre] ?? 0;
        $colorEstado = match($idEstado) {
            1 => '#000000', 2 => '#808080', 3 => '#008000', 4 => '#800000',
            5 => '#FFA500', 6 => '#FFFF00', 7 => '#0000FF', 8 => '#9C27B0', default => '#CCCCCC'
        };
        $status = [
            'id' => $idEstado,
            'nombre' => $ticket['estado'] ?? 'Sin estado',
            'color' => $colorEstado,
            'dibujarRuta' => ($idEstado === 8)
        ];
        $ultimaAccion = $this->acciones->where('ticket_id', $ticket['id'])->orderBy('id', 'DESC')->first();
        $comentario = $ultimaAccion['descripcion'] ?? '';

        return [
            'id' => $ticket['id'],
            'latitud' => (float)$ticket['latitud'],
            'longitud' => (float)$ticket['longitud'],
            'descripcion' => $ticket['descripcion'],
            'url_encuesta' => 'https://www.metrixencuesta.wuaze.com/index.php/survey/4',
            'titulo' => $ticket['titulo'],
            'status' => $status,
            'comentario' => $comentario,
            'direccion' => $ticket['direccion'],
            'nombreCiudadano' => $ticket['nombreCiudadano'],
            'correoCiudadano' => $ticket['correoCiudadano'],
            'telefonoCiudadano' => $ticket['telefonoCiudadano'],
            'articulosPorEntregar' => $articulosPorTicket($ticket['id'])
        ];
    }, $tickets);

    $rondasBD = $this->rondas->findAll();
    $rondas = array_map(function ($ronda) use ($estadosMapActividades) {
        $actividadesExtra = model('App\\Models\\ActividadesExtraModel')
            ->where('ronda_nombre', $ronda['nombre'])
            ->findAll();

        $actividades = array_map(function ($actividad) use ($estadosMapActividades) {
            $articulos = json_decode($actividad['articulosPorEntregar'], true) ?? [];
            $estadoActividadId = (int)($actividad['status_id'] ?? 1);
            $nombreEstado = $estadosMapActividades[$estadoActividadId] ?? 'Desconocido';

            $color = match($estadoActividadId) {
                1 => '#000000', 2 => '#808080', 3 => '#F44336', 4 => '#FF5722',
                5 => '#FFC107', 6 => '#9E9E9E', 7 => '#03A9F4', 8 => '#4CAF50', default => '#CCCCCC'
            };
            $dibujarRuta = ($estadoActividadId === 8);

            return [
                'id' => 'act' . $actividad['id'],
                'status' => [
                    'id' => $estadoActividadId,
                    'nombre' => $nombreEstado,
                    'color' => $color,
                    'dibujarRuta' => $dibujarRuta
                ],
                'latitud' => (float)$actividad['latitud'],
                'longitud' => (float)$actividad['longitud'],
                'direccion' => $actividad['direccion'],
                'nombreCiudadano' => $actividad['nombreCiudadano'] ?? '',
                'correoCiudadano' => $actividad['correoCiudadano'] ?? '',
                'telefonoCiudadano' => $actividad['telefonoCiudadano'] ?? '',
                'articulosPorEntregar' => $articulos,
                'url_encuesta' => 'https://www.metrixencuesta.wuaze.com/index.php/survey/4'
            ];
        }, $actividadesExtra);

        return [
            'id' => 'ronda' . $ronda['id'],
            'nombre' => $ronda['nombre'],
            'actividades' => $actividades
        ];
    }, $rondasBD);

    return $this->respond([
        'status' => 200,
        'error' => false,
        'message' => 'Inicio de sesión exitoso',
        'data' => [
            'id' => $user['id'],
            'correo' => $user['correo'],
            'nombre' => $user['nombre'],
            'area_id' => $user['area_id'],
            'cargo' => $user['cargo'],
            'telefono' => $user['telefono'],
            'rol_id' => $user['rol_id'],
            'rol_nombre' => null,
            'fecha_registro' => $user['fecha_registro'],
            'rondas' => $rondas,
            'tareas' => $tareas
        ]
    ]);
}



 public function registro()
{
    $data = $this->request->getJSON(true);

    $validationRules = [
        'nombre' => 'required|min_length[2]',
        'apellidoPaterno' => 'required|min_length[2]',
        'apellidoMaterno' => 'required|min_length[2]',
        'email' => 'required|valid_email',
        'fechaNacimiento' => 'required|valid_date[Y-m-d]',
        'numeroTelefonico' => 'required|regex_match[/^\+?[0-9]{10,15}$/]',
        'contrasena' => 'required|min_length[8]'
    ];

    if (!$this->validate($validationRules)) {
        return $this->respond([
            'success' => false,
            'message' => $this->validator->getErrors()
        ], 400);
    }

    // Verificar si email ya existe
    $existe = $this->usuarios->where('correo', $data['email'])->first();
    if ($existe) {
        return $this->respond([
            'success' => false,
            'message' => 'El correo electrónico ya está registrado.'
        ], 409);
    }

    
  // Generar código de activación (5 dígitos solo números)
$codigoActivacion = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);



    // Preparar datos para insertar con código y estado cuenta no activada
    $nuevoUsuario = [
        'nombre' => $data['nombre'],
        'apellido_paterno' => $data['apellidoPaterno'],
        'apellido_materno' => $data['apellidoMaterno'],
        'correo' => $data['email'],
        'fecha_nacimiento' => $data['fechaNacimiento'],
        'telefono' => $data['numeroTelefonico'],
        'contrasena' => password_hash($data['contrasena'], PASSWORD_DEFAULT),
        'fecha_registro' => date('Y-m-d H:i:s'),
        'codigo_activacion' => $codigoActivacion,
        'cuenta_activada' => 0
    ];

    // Insertar el usuario en BD
    $insertId = $this->usuarios->insert($nuevoUsuario);

    if (!$insertId) {
        return $this->respond([
            'success' => false,
            'message' => 'Error al registrar usuario.'
        ], 500);
    }

    // Enviar correo con el código de activación
    $email = \Config\Services::email();

    $email->setTo($data['email']);
    $email->setSubject('Activación de cuenta');
    $email->setMessage("
        <p>Hola {$data['nombre']},</p>
        <p>Gracias por registrarte en nuestro sistema. Para activar tu cuenta, por favor ingresa el siguiente código de activación en la app o sitio web:</p>
        <h2 style='color:#2e6c80;'>$codigoActivacion</h2>
        <p>Si no solicitaste este registro, puedes ignorar este correo.</p>
        <p>Saludos,<br>Equipo de Soporte Metrix</p>
    ");

    if (!$email->send()) {
        return $this->respond([
            'success' => false,
            'message' => 'Usuario registrado pero no se pudo enviar el correo de activación.',
            'debug' => $email->printDebugger(['headers'])
        ], 500);
    }

    return $this->respond([
        'success' => true,
        'message' => 'Usuario registrado exitosamente. Por favor, revisa tu correo electrónico para activar tu cuenta.'
    ], 201);
}



public function activarCuenta()
{
    $data = $this->request->getJSON(true);

    if (empty($data['correo']) || empty($data['codigo'])) {
        return $this->respond([
            'success' => false,
            'message' => 'Correo y código de activación son requeridos.'
        ], 400);
    }

    $usuario = $this->usuarios->where('correo', $data['correo'])->first();

    if (!$usuario) {
        return $this->respond([
            'success' => false,
            'message' => 'Usuario no encontrado.'
        ], 404);
    }

    if ($usuario['cuenta_activada']) {
        return $this->respond([
            'success' => false,
            'message' => 'La cuenta ya está activada.'
        ], 400);
    }

    if ($usuario['codigo_activacion'] != $data['codigo']) {
        return $this->respond([
            'success' => false,
            'message' => 'Código de activación incorrecto.'
        ], 401);
    }

    $this->usuarios->update($usuario['id'], [
        'cuenta_activada' => 1,
        'codigo_activacion' => null
    ]);

    return $this->respond([
        'success' => true,
        'message' => 'Cuenta activada correctamente.'
    ]);
}

  public function reenviarCodigoActivacion()
{
    $data = $this->request->getJSON(true);

    if (empty($data['correo'])) {
        return $this->respond([
            'success' => false,
            'message' => 'El correo es requerido.'
        ], 400);
    }

    $usuario = $this->usuarios->where('correo', $data['correo'])->first();

    if (!$usuario) {
        return $this->respond([
            'success' => false,
            'message' => 'Usuario no encontrado.'
        ], 404);
    }

    if ($usuario['cuenta_activada']) {
        return $this->respond([
            'success' => false,
            'message' => 'La cuenta ya está activada.'
        ], 400);
    }

    // Generar nuevo código de activación (5 dígitos, solo números)
    $nuevoCodigo = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);

    // Actualizar el nuevo código en la base de datos
    $this->usuarios->update($usuario['id'], ['codigo_activacion' => $nuevoCodigo]);

    // Enviar el correo con el nuevo código
    $email = \Config\Services::email();

    $email->setTo($usuario['correo']);
    $email->setSubject('Reenvío de código de activación');
    $email->setMessage("
        <p>Hola {$usuario['nombre']},</p>
        <p>Hemos generado un nuevo código de activación para tu cuenta. Utiliza el siguiente código en la app o sitio web:</p>
        <h2 style='color:#2e6c80;'>$nuevoCodigo</h2>
        <p>Si no solicitaste esto, puedes ignorar este correo.</p>
        <p>Saludos,<br>Equipo de Soporte Metrix</p>
    ");

    if (!$email->send()) {
        return $this->respond([
            'success' => false,
            'message' => 'No se pudo enviar el correo con el código de activación.',
            'debug' => $email->printDebugger(['headers'])
        ], 500);
    }

    return $this->respond([
        'success' => true,
        'message' => 'Código de activación reenviado exitosamente.'
    ]);
}


}


