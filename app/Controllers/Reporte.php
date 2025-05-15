<?php

namespace App\Controllers;

use App\Models\PrioridadesModel;
use App\Models\CategoriasModel;
use App\Models\SubcategoriasModel;
use App\Models\CategoriaSubcategoriaPrioridadModel;
use App\Models\TicketsModel;
use App\Models\ArchivosModel;
use App\Models\UsuariosModel;
use App\Models\AreasModel;
use App\Models\CampanasModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Config\Wasabi;
use Aws\S3\Exception\S3Exception;


class Reporte extends BaseController
{
    protected $prioridades;
    protected $categorias;
    protected $subcategorias;
    protected $categoriaSubcategoriaPrioridad;
    protected $tickets;
    protected $archivos;
    protected $usuarios;
    protected $areas;
    protected $campanas;

    public function __construct()
    {
        $this->prioridades = new PrioridadesModel();
        $this->categorias = new CategoriasModel();
        $this->subcategorias = new SubcategoriasModel();
        $this->categoriaSubcategoriaPrioridad = new CategoriaSubcategoriaPrioridadModel();
        $this->tickets = new TicketsModel();
        $this->archivos = new ArchivosModel();
        $this->usuarios = new UsuariosModel();
        $this->areas = new AreasModel();
        $this->campanas = new CampanasModel();

        helper(['Alerts', 'Email']);
    }

    public function inicio()
    {
        return $this->response->setJSON(['error' => 'Solicitud Incorrecta']);
    }

    public function listarPrioridades()
    {
        return $this->response->setJSON($this->prioridades->findAll());
    }

    public function listarCategorias()
    {
        return $this->response->setJSON($this->categorias->findAll());
    }

    public function listarSubcategorias()
    {
        return $this->response->setJSON($this->subcategorias->findAll());
    }

    public function listarReporteCompleto()
    {
        // Obtener los datos de las tablas relacionadas
        $data = $this->categoriaSubcategoriaPrioridad
            ->select('tbl_categoria_subcategoria_prioridad.*, tbl_categorias.id_categoria, tbl_categorias.nombre as categoria, tbl_subcategorias.id_subcategoria, tbl_subcategorias.nombre as subcategoria, tbl_prioridades.id_prioridad, tbl_prioridades.nombre as prioridad')
            ->join('tbl_categorias', 'tbl_categorias.id_categoria = tbl_categoria_subcategoria_prioridad.id_categoria')
            ->join('tbl_subcategorias', 'tbl_subcategorias.id_subcategoria = tbl_categoria_subcategoria_prioridad.id_subcategoria')
            ->join('tbl_prioridades', 'tbl_prioridades.id_prioridad = tbl_categoria_subcategoria_prioridad.id_prioridad')
            ->findAll();
    
        $categorias = [];
    
        // Organizar los datos en un array estructurado
        foreach ($data as $row) {
            $categoriaId = $row['id_categoria'];
            $subcategoriaId = $row['id_subcategoria'];  // Usar el id real de subcategoría
            $prioridadId = $row['id_prioridad'];
    
            // Crear una nueva categoría si no existe
            if (!isset($categorias[$categoriaId])) {
                $categorias[$categoriaId] = [
                    'id' => (string)$categoriaId,
                    'nombre' => $row['categoria'],
                    'subcategorias' => []
                ];
            }
    
            // Crear una nueva subcategoría si no existe
            $subcategorias = &$categorias[$categoriaId]['subcategorias'];
    
            // Buscar si la subcategoría ya existe en el array
            $subcategoriaIndex = array_search((string)$subcategoriaId, array_column($subcategorias, 'id'));
    
            if ($subcategoriaIndex === false) {
                // Si no existe, crearla
                $subcategorias[] = [
                    'id' => (string)$subcategoriaId,  // Usar el id real de subcategoría
                    'nombre' => $row['subcategoria'],
                    'prioridades' => []
                ];
                $subcategoriaIndex = count($subcategorias) - 1;  // Última subcategoría agregada
            }
    
            // Agregar la prioridad a la subcategoría
            $subcategorias[$subcategoriaIndex]['prioridades'][] = [
                'id' => (string)$prioridadId,
                'nombre' => $row['prioridad']
            ];
        }
    
        // Devolver el JSON de las categorías, subcategorías y prioridades
        return $this->response->setJSON(array_values($categorias));
    }
    
    
        
public function crearTicket(): ResponseInterface
{
    try {
        // Obtener datos de la solicitud
        $json = $this->request->getPost();  // Para manejar el 'form-data'

        // Validación de los datos de la solicitud
        $validationRules = [
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'categoria_id' => 'required|is_natural_no_zero',
            'subcategoria_id' => 'required|is_natural_no_zero',
            'prioridad_id' => 'required|is_natural_no_zero',
            'descripcion' => 'required',
            'usuario_id' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Errores de validación',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Asignar valores predeterminados si no se proporcionan
        $cliente_id = $json['cliente_id'] ?? null;
        $area_id = $json['area_id'] ?? null;
        $campana_id = $json['campana_id'] ?? null;

        // Generar identificador único del ticket
        $identificador = 'TKD-' . strtoupper(bin2hex(random_bytes(5)));

        // Preparar los datos del ticket
        $ticketData = [
            'usuario_id' => $json['usuario_id'],
            'categoria_id' => $json['categoria_id'],
            'subcategoria_id' => $json['subcategoria_id'],
            'prioridad_id' => $json['prioridad_id'],
            'descripcion' => $json['descripcion'],
            'latitud' => $json['latitud'],
            'longitud' => $json['longitud'],
            'estado' => 'Abierto',
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'cliente_id' => $cliente_id,
            'area_id' => $area_id,
            'campana_id' => $campana_id,
            'tipo_id' => $json['tipo_id'] ?? null,
            'titulo' => 'Reporte: ' . uniqid(),
            'prioridad' => $json['prioridad_id'],
            'identificador' => $identificador // Identificador generado automáticamente
        ];

        // Insertar ticket
        $ticketId = $this->tickets->insert($ticketData);
        if (!$ticketId) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'No se pudo crear el ticket'
            ]);
        }

        // Archivos: fotos y video
        $fotos = $this->request->getFiles('fotos');
        $video = $this->request->getFile('videos');

        $uploadedFiles = [];

        if ($fotos) {
            foreach ($fotos as $foto) {
                if (is_array($foto)) {
                    foreach ($foto as $file) {
                        $uploadedFiles[] = $this->saveFile($file, 'foto', $ticketId, $json['usuario_id']);
                    }
                } else {
                    $uploadedFiles[] = $this->saveFile($foto, 'foto', $ticketId, $json['usuario_id']);
                }
            }
        }

        if ($video) {
            $uploadedFiles[] = $this->saveFile($video, 'video', $ticketId, $json['usuario_id']);
        }

        return $this->response->setJSON([
            'status' => "success",
            'message' => 'Ticket creado con éxito',
            'ticket_id' => $ticketId,
            'identificador' => $identificador,
            'archivos_subidos' => $uploadedFiles
        ])->setStatusCode(200);

    } catch (Exception $e) {
        return $this->response->setStatusCode(500)->setJSON([
            'status' => 'error',
            'message' => 'Error al crear el ticket: ' . $e->getMessage()
        ]);
    }
}


    private function processFiles($files, $ticketId, $usuario_id)
    {
        $uploadedFiles = [];
        if (isset($files['fotos'])) {
            foreach ($files['fotos'] as $foto) {
                $uploadedFiles[] = $this->saveFile($foto, 'foto', $ticketId, $usuario_id);
            }
        }

        if (isset($files['video'])) {
            $uploadedFiles[] = $this->saveFile($files['video'], 'video', $ticketId, $usuario_id);
        }

        return $uploadedFiles;
    }
    
private function saveFile($file, $type, $ticketId, $usuario_id)
{
    if (is_object($file)) {
        $client = Wasabi::createClient();

        $bucket = 'metrixapi';
        $newName = $file->getRandomName();
        $tempPath = $file->getTempName();  // Ruta temporal del archivo
        $extension = $file->getExtension();
        $tamano = $file->getSize();
        $mimeType = $file->getMimeType();

        try {
            // Subir a Wasabi
            $client->putObject([
                'Bucket' => $bucket,
                'Key'    => 'tickets/' . $newName,
                'SourceFile' => $tempPath,
                'ACL'    => 'private',
                'ContentType' => $mimeType
            ]);

            // Generar URL temporal (válida por 10 minutos)
            $cmd = $client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key'    => 'tickets/' . $newName
            ]);
            $request = $client->createPresignedRequest($cmd, '+10 minutes');
            $presignedUrl = (string) $request->getUri();

            // Guardar en base de datos
            $fileData = [
                'ticket_id' => $ticketId,
                'usuario_id' => $usuario_id,
                'descripcion' => ucfirst($type) . ' del reporte',
                'ruta' => $presignedUrl,
                'extension' => $extension,
                'tamano' => $tamano,
                'tipo_mime' => $mimeType,
                'fecha_subida' => date('Y-m-d H:i:s'),
                'tipo' => $type
            ];

            $this->archivos->insert($fileData);
            return $presignedUrl;  // puedes devolver la URL si quieres verla desde el cliente

        } catch (S3Exception $e) {
            log_message('error', 'Error al subir archivo a Wasabi: ' . $e->getMessage());
            return null;
        }
    }

    return null;
}

}
