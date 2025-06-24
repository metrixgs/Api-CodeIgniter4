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
use App\Models\ArchivosModel;
use App\Models\CategoriasModel;
use App\Models\SubcategoriasModel;
use App\Models\PrioridadesModel;
use App\Models\TipoTicketsModel;



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
          protected $archivos;
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
       $this->archivos = new ArchivosModel(); 
  $this->categorias = new CategoriasModel();
$this->subcategorias = new SubcategoriasModel();
$this->prioridades = new PrioridadesModel();
$this->tipos = new TipoTicketsModel();
$this->articulosPorEntregar = new ArticulosModel();




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
    if ($user === null) return $this->failUnauthorized('Correo electrónico no registrado');

    $estadosTarea = $this->estadosTarea->findAll();
    $estadosMap = [];

    foreach ($estadosTarea as $estado) {
        $estadosMap[$estado['id']] = [
            'nombre' => $estado['nombre'],
            'color' => $estado['color'] ?? '#CCCCCC'
        ];
    }

    $rondasBD = $this->rondas->findAll();
    $articulosGenerales = $this->articulos->where('ticket_id', null)->findAll();

    $rondas = array_map(function ($ronda) use ($estadosMap, $articulosGenerales, $user) {
        $actividades = [];
        $rondaIdStr = 'ronda' . $ronda['id'];

        $tickets = $this->tickets->where('ronda_id', $rondaIdStr)->findAll();

        $fechasVencimiento = [];

        foreach ($tickets as $ticket) {
            if (!empty($ticket['fecha_vencimiento'])) {
                $fechasVencimiento[] = $ticket['fecha_vencimiento'];
            }

            $estadoId = $ticket['estado_id'] ?? 1;
            $estado = $estadosMap[$estadoId] ?? ['nombre' => 'Pendiente', 'color' => '#2196F3'];

            $tipoTicketId = $ticket['tipo_ticket_id'] ?? null;
            $tipo = $this->tipos->find($tipoTicketId);
            $nombreTipo = strtolower($tipo['nombre'] ?? 'reporte');

            $actividad = [
                'id' => 'act' . $ticket['id'],
                'ticket_id' => $ticket['id'],
                'ronda_id' => $rondaIdStr,
                'tipo' => ucfirst($nombreTipo),
                'status' => [
                    'id' => $estadoId,
                    'nombre' => $estado['nombre'],
                    'color' => $estado['color'],
                    'dibujarRuta' => false
                ],
                'latitud' => (float)($ticket['latitud'] ?? 0),
                'longitud' => (float)($ticket['longitud'] ?? 0),
                'direccion' => $ticket['direccion'] ?? '',
                'nombreCiudadano' => $ticket['nombreCiudadano'] ?? '',
                'correoCiudadano' => $ticket['correoCiudadano'] ?? '',
                'telefonoCiudadano' => $ticket['telefonoCiudadano'] ?? '',
                'url_encuesta' => 'https://www.metrixencuesta.wuaze.com/index.php/survey/4',
                'encuestaContestada' => (bool)($ticket['encuesta_contestada'] ?? false),
            ];

            if ($nombreTipo === 'visita') {
                $actividad['articulosPorEntregar'] = array_map(function ($articulo) {
                    return [
                        'id' => $articulo['id'],
                        'nombre' => $articulo['nombre'],
                        'imagen' => $articulo['imagen']
                    ];
                }, $articulosGenerales);
            }

            if ($nombreTipo === 'reporte') {
                $archivos = $this->archivos->where('ticket_id', $ticket['id'])->findAll();
                $fotos = [];
                $videos = [];

                foreach ($archivos as $archivo) {
                    $mime = strtolower($archivo['tipo_mime'] ?? '');
                    if (str_contains($mime, 'image')) $fotos[] = $archivo['ruta'];
                    if (str_contains($mime, 'video')) $videos[] = $archivo['ruta'];
                }

                $actividad['fotos'] = $fotos;
                $actividad['videos'] = $videos;
                $actividad['categoria'] = $this->obtenerCategoriaDetallada($ticket);
            }

            $actividades[] = $actividad;
        }

        // Ordenar fechas y tomar la más próxima
        $fechaFin = null;
        if (!empty($fechasVencimiento)) {
            sort($fechasVencimiento);
            $fechaFin = $fechasVencimiento[0];
        }

        return [
            'activa' => (bool)($ronda['activa'] ?? false),
            'fechaFin' => $fechaFin,
            'usuario' => [
                'id' => $user['id'],
                'nombre' => $user['nombre']
            ],
            'id' => $rondaIdStr,
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
            'rondas' => $rondas
        ]
    ]);
}


  private function obtenerCategoriaDetallada($ticket)
{
    $categoriaId = $ticket['categoria_id'] ?? null;
    $subcategoriaId = $ticket['subcategoria_id'] ?? null;
    $prioridadId = $ticket['prioridad_id'] ?? null;

    if (!$categoriaId) return null;

    $categoria = $this->categorias->find($categoriaId);
    if (!is_array($categoria)) return null;

    $subcategoria = $subcategoriaId ? $this->subcategorias->find($subcategoriaId) : null;
    $prioridad = $prioridadId ? $this->prioridades->find($prioridadId) : null;

    return [
        'id' => $categoria['id_categoria'] ?? null,
        'nombre' => $categoria['nombre'] ?? null,
        'subcategorias' => is_array($subcategoria) ? [
            'id' => $subcategoria['id_subcategoria'] ?? null,
            'nombre' => $subcategoria['nombre'] ?? null,
            'prioridades' => is_array($prioridad) ? [
                'id' => $prioridad['id_prioridad'] ?? null,
                'nombre' => $prioridad['nombre'] ?? null
            ] : null
        ] : null
    ];
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


