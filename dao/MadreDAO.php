<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Madre.php';
require_once __DIR__ . '/../model/Orientadora.php';
require_once __DIR__ . '/../model/Aliado.php';
require_once __DIR__ . '/../model/Eps.php';

class MadreDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT m.*, 
                       o.id as orientadora_id_real, o.nombre as orientadora_nombre,
                       a.id as aliado_id_real, a.nombre as aliado_nombre,
                       e.id as eps_id_real, e.nombre as eps_nombre
                FROM madres m
                LEFT JOIN orientadoras o ON m.orientadora_id = o.id
                LEFT JOIN aliados a ON m.aliado_id = a.id
                LEFT JOIN eps e ON m.eps_id = e.id
                ORDER BY m.fecha_ingreso DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $madres = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $madres[] = $this->mapRowToMadre($row);
        }
        return $madres;
    }

    public function getById($id)
    {
        $sql = "SELECT m.*, 
                       o.id as orientadora_id_real, o.nombre as orientadora_nombre,
                       a.id as aliado_id_real, a.nombre as aliado_nombre,
                       e.id as eps_id_real, e.nombre as eps_nombre
                FROM madres m
                LEFT JOIN orientadoras o ON m.orientadora_id = o.id
                LEFT JOIN aliados a ON m.aliado_id = a.id
                LEFT JOIN eps e ON m.eps_id = e.id
                WHERE m.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToMadre($row);
        }
        return null;
    }

    private function mapRowToMadre($row)
    {
        // Construir objetos relacionados si existen
        $orientadora = null;
        if ($row['orientadora_id']) {
            $orientadora = new Orientadora($row['orientadora_nombre']);
            $orientadora->setId($row['orientadora_id']);
        }

        $aliado = null;
        if ($row['aliado_id']) {
            $aliado = new Aliado($row['aliado_nombre']);
            $aliado->setId($row['aliado_id']);
        }

        $eps = null;
        if ($row['eps_id']) {
            $eps = new Eps($row['eps_nombre']);
            $eps->setId($row['eps_id']);
        }

        // Crear objeto Madre
        // Nota: El constructor de Madre es muy largo, usaremos los setters para mayor claridad o el constructor si es necesario.
        // Usaré el constructor con los campos obligatorios y setters para el resto para mantenerlo legible.

        $madre = new Madre(
            $row['fecha_ingreso'],
            $row['id']
        );

        // Setters básicos
        $madre->setEsVirtual((bool) $row['es_virtual']);
        $madre->setPrimerNombre($row['primer_nombre']);
        $madre->setSegundoNombre($row['segundo_nombre']);
        $madre->setPrimerApellido($row['primer_apellido']);
        $madre->setSegundoApellido($row['segundo_apellido']);
        $madre->setTipoDocumento($row['tipo_documento']);
        $madre->setNumeroDocumento($row['numero_documento']);
        $madre->setFechaNacimiento($row['fecha_nacimiento']);
        $madre->setEdad($row['edad']);
        $madre->setSexo($row['sexo']);
        $madre->setNumeroTelefono($row['numero_telefono']);
        $madre->setOtroContacto($row['otro_contacto']);
        $madre->setNumeroHijos((int) $row['numero_hijos']);
        $madre->setPerdidas((int) $row['perdidas']);
        $madre->setEstadoCivil($row['estado_civil']);
        $madre->setNombrePareja($row['nombre_pareja']);
        $madre->setTelefonoPareja($row['telefono_pareja']);
        $madre->setDeAcuerdoAborto($row['de_acuerdo_aborto']);
        $madre->setNivelEstudio($row['nivel_estudio']);
        $madre->setOcupacion($row['ocupacion']);
        $madre->setReligion($row['religion']);
        $madre->setSisben($row['sisben']);
        $madre->setEnfermedadesMedicamento($row['enfermedades_medicamento']);
        $madre->setSeEnteroPor($row['se_entero_por']);
        $madre->setAsisteDiscipulado($row['asiste_discipulado']);
        $madre->setDesvinculo($row['desvinculo']);
        $madre->setNovedades($row['novedades']);
        $madre->setCreatedAt($row['created_at']);
        $madre->setUpdatedAt($row['updated_at']);

        // Relaciones
        $madre->setOrientadora($orientadora);
        $madre->setAliado($aliado);
        $madre->setEps($eps);

        return $madre;
    }
}
