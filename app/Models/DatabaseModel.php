<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

class DatabaseModel extends Model {

    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function ejecutarConsulta($sql) {
        try {
            return $this->db->query($sql);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function obtenerTablas() {
        try {
            // Ejecutar consulta SHOW TABLES
            $query = $this->db->query('SHOW TABLES');
            $resultado = $query->getResultArray();

            // Crear un array para almacenar los nombres de las tablas
            $tablas = [];
            foreach ($resultado as $row) {
                $tablas[] = reset($row); // Extraer el primer valor de cada fila
            }

            // Configurar el encabezado como JSON
            header('Content-Type: application/json; charset=utf-8');

            // Crear respuesta organizada
            $response = [
                'status' => 200,
                'error' => false,
                'mensaje' => 'Tablas obtenidas exitosamente.',
                'data' => $tablas
            ];

            // Imprimir JSON en un formato organizado
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit; // Asegurarse de que no se agregue contenido adicional
        } catch (\Exception $e) {
            // Manejo de errores
            header('Content-Type: application/json; charset=utf-8');

            $response = [
                'status' => 500,
                'error' => true,
                'mensaje' => 'Error al obtener las tablas.',
                'detalles' => $e->getMessage()
            ];

            // Imprimir JSON con error
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function eliminarTabla($nombreTabla) {
        try {
            // Generar la consulta SQL para eliminar la tabla
            $sql = "DROP TABLE IF EXISTS " . $this->db->escapeIdentifiers($nombreTabla);
            $this->db->query($sql); // Ejecutar la consulta
            // Configurar el encabezado como JSON
            header('Content-Type: application/json; charset=utf-8');

            // Crear respuesta de éxito
            $response = [
                'status' => 200,
                'error' => false,
                'mensaje' => "La tabla '{$nombreTabla}' se eliminó exitosamente."
            ];

            // Imprimir JSON
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit; // Detener cualquier salida adicional
        } catch (\Exception $e) {
            // Configurar encabezado para la respuesta de error
            header('Content-Type: application/json; charset=utf-8');

            // Crear respuesta de error
            $response = [
                'status' => 500,
                'error' => true,
                'mensaje' => "Error al eliminar la tabla '{$nombreTabla}'.",
                'detalles' => $e->getMessage()
            ];

            // Imprimir JSON con error
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function obtenerEstructuraTabla($nombreTabla) {
        try {
            // Obtener la estructura básica de la tabla
            $sql = "DESCRIBE " . $this->db->escapeIdentifiers($nombreTabla);
            $query = $this->db->query($sql);

            // Construir el resultado
            $resultado = [
                'estructura_basica' => $query->getResultArray(),
            ];

            // Configurar el encabezado de la respuesta como JSON
            header('Content-Type: application/json');
            echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit; // Finalizar la ejecución después de enviar la respuesta
        } catch (\Exception $e) {
            // Enviar error en formato JSON
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'mensaje' => $e->getMessage()
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function obtenerDatosTabla($nombreTabla) {
        try {
            // Construir la consulta para obtener todos los datos de la tabla
            $sql = "SELECT * FROM " . $this->db->escapeIdentifiers($nombreTabla);
            $query = $this->db->query($sql);

            // Verificar si la tabla tiene datos
            if ($query->getNumRows() > 0) {
                // Retornar los datos en formato JSON
                $resultado = [
                    'datos' => $query->getResultArray(),
                ];

                // Configurar el encabezado de la respuesta como JSON
                header('Content-Type: application/json');
                echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                // Si no hay datos, retornar mensaje vacío
                header('Content-Type: application/json');
                echo json_encode([
                    'mensaje' => 'La tabla no contiene datos.',
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

            exit; // Finalizar la ejecución después de enviar la respuesta
        } catch (\Exception $e) {
            // Manejar errores
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'mensaje' => $e->getMessage()
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function eliminarElemento($nombreTabla, $id) {
        try {
            // Construir la consulta de eliminación
            $sql = "DELETE FROM " . $this->db->escapeIdentifiers($nombreTabla) . " WHERE id = ?";
            $query = $this->db->query($sql, [$id]);

            // Retornar true si se afectaron filas (eliminación exitosa)
            return $this->db->affectedRows() > 0;
        } catch (\Exception $e) {
            // Manejar errores y retornar false
            return false;
        }
    }

    public function eliminarTodosElementos($nombreTabla) {
        try {
            // Construir la consulta para eliminar todos los registros
            $sql = "DELETE FROM " . $this->db->escapeIdentifiers($nombreTabla);
            $query = $this->db->query($sql);

            // Retornar true si se afectaron filas (eliminación exitosa)
            return $this->db->affectedRows() > 0;
        } catch (\Exception $e) {
            // Manejar errores y retornar false
            return false;
        }
    }

    public function modificarNombreColumna($nombreTabla, $columnaActual, $nuevoNombre) {
        try {
            // Validar si la tabla y la columna existen
            $sqlCheckColumn = "DESCRIBE " . $this->db->escapeIdentifiers($nombreTabla);
            $query = $this->db->query($sqlCheckColumn);
            $estructura = $query->getResultArray();

            $columnaEncontrada = false;
            $tipoColumna = '';

            foreach ($estructura as $columna) {
                if ($columna['Field'] === $columnaActual) {
                    $columnaEncontrada = true;
                    $tipoColumna = $columna['Type']; // Guardar el tipo para renombrar correctamente
                    break;
                }
            }

            if (!$columnaEncontrada) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => true,
                    'mensaje' => "La columna '{$columnaActual}' no existe en la tabla '{$nombreTabla}'."
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Renombrar la columna usando la instrucción ALTER TABLE
            $sqlRename = "ALTER TABLE " . $this->db->escapeIdentifiers($nombreTabla) .
                    " CHANGE " . $this->db->escapeIdentifiers($columnaActual) .
                    " " . $this->db->escapeIdentifiers($nuevoNombre) . " " . $tipoColumna;
            $this->db->query($sqlRename);

            // Obtener la estructura actualizada de la tabla
            $sqlEstructura = "DESCRIBE " . $this->db->escapeIdentifiers($nombreTabla);
            $queryEstructura = $this->db->query($sqlEstructura);

            header('Content-Type: application/json');
            echo json_encode([
                'error' => false,
                'mensaje' => "La columna '{$columnaActual}' se renombró exitosamente a '{$nuevoNombre}'.",
                'estructura_actualizada' => $queryEstructura->getResultArray()
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'mensaje' => $e->getMessage()
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function eliminarColumna($nombreTabla, $nombreColumna) {
        try {
            // Validar si la tabla y la columna existen
            $sqlCheckColumn = "DESCRIBE " . $this->db->escapeIdentifiers($nombreTabla);
            $query = $this->db->query($sqlCheckColumn);
            $estructura = $query->getResultArray();

            $columnaEncontrada = false;

            foreach ($estructura as $columna) {
                if ($columna['Field'] === $nombreColumna) {
                    $columnaEncontrada = true;
                    break;
                }
            }

            if (!$columnaEncontrada) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => true,
                    'mensaje' => "La columna '{$nombreColumna}' no existe en la tabla '{$nombreTabla}'."
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Eliminar la columna usando ALTER TABLE
            $sqlDropColumn = "ALTER TABLE " . $this->db->escapeIdentifiers($nombreTabla) .
                    " DROP COLUMN " . $this->db->escapeIdentifiers($nombreColumna);
            $this->db->query($sqlDropColumn);

            // Obtener la estructura actualizada de la tabla
            $sqlEstructura = "DESCRIBE " . $this->db->escapeIdentifiers($nombreTabla);
            $queryEstructura = $this->db->query($sqlEstructura);

            header('Content-Type: application/json');
            echo json_encode([
                'error' => false,
                'mensaje' => "La columna '{$nombreColumna}' se eliminó exitosamente de la tabla '{$nombreTabla}'.",
                'estructura_actualizada' => $queryEstructura->getResultArray()
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'mensaje' => $e->getMessage()
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function agregarColumna($nombreTabla, $nombreColumna, $tipo, $longitud = null, $esNulo = true, $porDefecto = null, $extra = '') {
        try {
            // Construir el SQL para agregar la nueva columna
            $tipoColumna = $longitud ? "{$tipo}({$longitud})" : $tipo;
            $esNuloSql = $esNulo ? 'NULL' : 'NOT NULL';
            $porDefectoSql = $porDefecto !== null ? "DEFAULT " . $this->db->escape($porDefecto) : '';

            $sqlAddColumn = "ALTER TABLE " . $this->db->escapeIdentifiers($nombreTabla) . " ADD " .
                    $this->db->escapeIdentifiers($nombreColumna) . " {$tipoColumna} {$esNuloSql} {$porDefectoSql} {$extra}";

            // Ejecutar el comando SQL
            $this->db->query($sqlAddColumn);

            // Obtener la estructura actualizada de la tabla
            $sqlEstructura = "DESCRIBE " . $this->db->escapeIdentifiers($nombreTabla);
            $queryEstructura = $this->db->query($sqlEstructura);

            header('Content-Type: application/json');
            echo json_encode([
                'error' => false,
                'mensaje' => "La columna '{$nombreColumna}' se agregó exitosamente a la tabla '{$nombreTabla}'.",
                'estructura_actualizada' => $queryEstructura->getResultArray()
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'mensaje' => $e->getMessage()
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function cambiarOrdenColumna($nombreTabla, $nombreColumna, $posicion) {
        try {
            // Obtener la estructura de la tabla
            $sqlDescribe = "DESCRIBE " . $this->db->escapeIdentifiers($nombreTabla);
            $estructuraTabla = $this->db->query($sqlDescribe)->getResultArray();

            // Obtener las claves de la tabla
            $sqlKeys = "SHOW KEYS FROM " . $this->db->escapeIdentifiers($nombreTabla);
            $keys = $this->db->query($sqlKeys)->getResultArray();

            // Verificar que la columna exista
            $columnas = array_column($estructuraTabla, 'Field');
            if (!in_array($nombreColumna, $columnas)) {
                throw new \Exception("La columna '{$nombreColumna}' no existe en la tabla '{$nombreTabla}'.");
            }

            // Verificar que la posición sea válida
            if ($posicion < 1 || $posicion > count($columnas)) {
                throw new \Exception("La posición '{$posicion}' no es válida. Debe estar entre 1 y " . count($columnas) . ".");
            }

            // Reconstruir el orden de las columnas
            $ordenNuevo = $columnas;
            unset($ordenNuevo[array_search($nombreColumna, $ordenNuevo)]);
            array_splice($ordenNuevo, $posicion - 1, 0, $nombreColumna);

            // Crear la nueva definición de la columna
            $columnDefinitions = [];
            $primaryKeys = [];
            foreach ($ordenNuevo as $columna) {
                foreach ($estructuraTabla as $columnaDef) {
                    if ($columnaDef['Field'] === $columna) {
                        $definicion = "`{$columnaDef['Field']}` {$columnaDef['Type']}";

                        // Si la columna tiene un valor por defecto
                        if ($columnaDef['Default'] !== null) {
                            // Si es un valor específico como 'current_timestamp()'
                            if (strtolower($columnaDef['Default']) === 'current_timestamp()') {
                                $definicion .= " DEFAULT CURRENT_TIMESTAMP";
                            } else {
                                $definicion .= " DEFAULT '{$columnaDef['Default']}'";
                            }
                        }

                        // Verificar si la columna es NOT NULL
                        if ($columnaDef['Null'] === 'NO') {
                            $definicion .= " NOT NULL";
                        }

                        // Verificar si tiene 'Extra' (como auto_increment)
                        if ($columnaDef['Extra']) {
                            $definicion .= " {$columnaDef['Extra']}";
                        }

                        $columnDefinitions[] = $definicion;

                        // Identificar las claves primarias
                        foreach ($keys as $key) {
                            if ($key['Key_name'] === 'PRIMARY' && $key['Column_name'] === $columna) {
                                $primaryKeys[] = $columna;
                            }
                        }
                        break;
                    }
                }
            }

            // Agregar la clave primaria si existe
            if (!empty($primaryKeys)) {
                $columnDefinitions[] = "PRIMARY KEY (" . implode(", ", array_map(function ($key) {
                                    return "`{$key}`";
                                }, $primaryKeys)) . ")";
            }

            // Crear la tabla temporal con el nuevo orden de las columnas
            $nombreTablaTemporal = $nombreTabla . "_temp";
            $sqlCrearTablaTemporal = "CREATE TABLE {$this->db->escapeIdentifiers($nombreTablaTemporal)} (" . implode(", ", $columnDefinitions) . ")";
            $this->db->query($sqlCrearTablaTemporal);

            // Copiar los datos de la tabla original a la tabla temporal
            $sqlCopiarDatos = "INSERT INTO {$this->db->escapeIdentifiers($nombreTablaTemporal)} SELECT * FROM {$this->db->escapeIdentifiers($nombreTabla)}";
            $this->db->query($sqlCopiarDatos);

            // Eliminar la tabla original
            $sqlEliminarTablaOriginal = "DROP TABLE {$this->db->escapeIdentifiers($nombreTabla)}";
            $this->db->query($sqlEliminarTablaOriginal);

            // Renombrar la tabla temporal para reemplazar la original
            $sqlRenombrarTabla = "RENAME TABLE {$this->db->escapeIdentifiers($nombreTablaTemporal)} TO {$this->db->escapeIdentifiers($nombreTabla)}";
            $this->db->query($sqlRenombrarTabla);

            // Obtener la estructura actualizada
            $estructuraActualizada = $this->db->query($sqlDescribe)->getResultArray();

            // Retornar la respuesta en formato JSON
            header('Content-Type: application/json');
            echo json_encode([
                'error' => false,
                'mensaje' => "La columna '{$nombreColumna}' se movió exitosamente a la posición '{$posicion}' en la tabla '{$nombreTabla}'.",
                'estructura_actualizada' => $estructuraActualizada
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            // Capturar cualquier excepción y mostrar el mensaje
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'mensaje' => $e->getMessage()
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
