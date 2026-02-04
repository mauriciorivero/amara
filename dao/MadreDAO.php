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

    public function getAll($limit = 25, $offset = 0, $filters = [])
    {
        $where = "WHERE 1=1";
        $params = [];

        // Filtros
        if (!empty($filters['search'])) {
            $where .= " AND (
                m.primer_nombre LIKE :search OR 
                m.segundo_nombre LIKE :search OR 
                m.primer_apellido LIKE :search OR 
                m.segundo_apellido LIKE :search OR
                m.numero_documento LIKE :search OR
                CONCAT(m.primer_nombre, ' ', m.primer_apellido) LIKE :search
            )";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            if ($filters['estado'] === 'activa') {
                $where .= " AND (m.desvinculo IS NULL OR m.desvinculo = '')";
            } elseif ($filters['estado'] === 'desvinculada') {
                $where .= " AND (m.desvinculo IS NOT NULL AND m.desvinculo != '')";
            }
        }

        if (!empty($filters['orientadora'])) {
            $where .= " AND m.orientadora_id = :orientadora";
            $params[':orientadora'] = $filters['orientadora'];
        }

        if (!empty($filters['eps'])) {
            $where .= " AND m.eps_id = :eps";
            $params[':eps'] = $filters['eps'];
        }

        if (!empty($filters['edad'])) {
            switch ($filters['edad']) {
                case 'menor':
                    $where .= " AND m.edad < 18";
                    break;
                case '18-25':
                    $where .= " AND m.edad BETWEEN 18 AND 25";
                    break;
                case '26-35':
                    $where .= " AND m.edad BETWEEN 26 AND 35";
                    break;
                case '36-45':
                    $where .= " AND m.edad BETWEEN 36 AND 45";
                    break;
                case 'mayor':
                    $where .= " AND m.edad > 45";
                    break;
            }
        }

        $sql = "SELECT m.*, 
                       o.id as orientadora_id_real, o.nombre as orientadora_nombre,
                       a.id as aliado_id_real, a.nombre as aliado_nombre,
                       e.id as eps_id_real, e.nombre as eps_nombre
                FROM madres m
                LEFT JOIN orientadoras o ON m.orientadora_id = o.id
                LEFT JOIN aliados a ON m.aliado_id = a.id
                LEFT JOIN eps e ON m.eps_id = e.id
                $where
                ORDER BY m.fecha_ingreso DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();

        $madres = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $madres[] = $this->mapRowToMadre($row);
        }
        return $madres;
    }

    public function countAll($filters = [])
    {
        $where = "WHERE 1=1";
        $params = [];

        // Copiar lógica de filtros (idealmente refactorizar en método privado)
        if (!empty($filters['search'])) {
            $where .= " AND (
                m.primer_nombre LIKE :search OR 
                m.segundo_nombre LIKE :search OR 
                m.primer_apellido LIKE :search OR 
                m.segundo_apellido LIKE :search OR
                m.numero_documento LIKE :search OR
                CONCAT(m.primer_nombre, ' ', m.primer_apellido) LIKE :search
            )";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            if ($filters['estado'] === 'activa') {
                $where .= " AND (m.desvinculo IS NULL OR m.desvinculo = '')";
            } elseif ($filters['estado'] === 'desvinculada') {
                $where .= " AND (m.desvinculo IS NOT NULL AND m.desvinculo != '')";
            }
        }

        if (!empty($filters['orientadora'])) {
            $where .= " AND m.orientadora_id = :orientadora";
            $params[':orientadora'] = $filters['orientadora'];
        }

        if (!empty($filters['eps'])) {
            $where .= " AND m.eps_id = :eps";
            $params[':eps'] = $filters['eps'];
        }

        if (!empty($filters['edad'])) {
            switch ($filters['edad']) {
                case 'menor':
                    $where .= " AND m.edad < 18";
                    break;
                case '18-25':
                    $where .= " AND m.edad BETWEEN 18 AND 25";
                    break;
                case '26-35':
                    $where .= " AND m.edad BETWEEN 26 AND 35";
                    break;
                case '36-45':
                    $where .= " AND m.edad BETWEEN 36 AND 45";
                    break;
                case 'mayor':
                    $where .= " AND m.edad > 45";
                    break;
            }
        }

        $sql = "SELECT COUNT(*) as total FROM madres m $where";
        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
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

    public function create(Madre $madre): bool
    {
        $sql = "INSERT INTO madres (
            fecha_ingreso, es_virtual, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
            tipo_documento, numero_documento, fecha_nacimiento, edad, sexo, numero_telefono, otro_contacto,
            correo_electronico, redes_sociales, direccion, barrio, ciudad,
            numero_hijos, perdidas, estado_civil, nombre_pareja, telefono_pareja, de_acuerdo_aborto,
            nivel_estudio, ocupacion, religion, eps_id, sisben, enfermedades_medicamento, se_entero_por,
            orientadora_id, aliado_id, asiste_discipulado, desvinculo, novedades
        ) VALUES (
            :fechaIngreso, :esVirtual, :primerNombre, :segundoNombre, :primerApellido, :segundoApellido,
            :tipoDocumento, :numeroDocumento, :fechaNacimiento, :edad, :sexo, :numeroTelefono, :otroContacto,
            :correoElectronico, :redesSociales, :direccion, :barrio, :ciudad,
            :numeroHijos, :perdidas, :estadoCivil, :nombrePareja, :telefonoPareja, :deAcuerdoAborto,
            :nivelEstudio, :ocupacion, :religion, :epsId, :sisben, :enfermedadesMedicamento, :seEnteroPor,
            :orientadoraId, :aliadoId, :asisteDiscipulado, :desvinculo, :novedades
        )";

        $stmt = $this->conn->prepare($sql);

        $params = $this->mapParams($madre);
        return $stmt->execute($params);
    }

    public function update(Madre $madre): bool
    {
        $sql = "UPDATE madres SET 
            fecha_ingreso = :fechaIngreso, es_virtual = :esVirtual, primer_nombre = :primerNombre, 
            segundo_nombre = :segundoNombre, primer_apellido = :primerApellido, segundo_apellido = :segundoApellido,
            tipo_documento = :tipoDocumento, numero_documento = :numeroDocumento, fecha_nacimiento = :fechaNacimiento,
            edad = :edad, sexo = :sexo, numero_telefono = :numeroTelefono, otro_contacto = :otroContacto,
            correo_electronico = :correoElectronico, redes_sociales = :redesSociales, direccion = :direccion,
            barrio = :barrio, ciudad = :ciudad,
            numero_hijos = :numeroHijos, perdidas = :perdidas, estado_civil = :estadoCivil, 
            nombre_pareja = :nombrePareja, telefono_pareja = :telefonoPareja, de_acuerdo_aborto = :deAcuerdoAborto,
            nivel_estudio = :nivelEstudio, ocupacion = :ocupacion, religion = :religion, eps_id = :epsId,
            sisben = :sisben, enfermedades_medicamento = :enfermedadesMedicamento, se_entero_por = :seEnteroPor,
            orientadora_id = :orientadoraId, aliado_id = :aliadoId, asiste_discipulado = :asisteDiscipulado,
            desvinculo = :desvinculo, novedades = :novedades
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $params = $this->mapParams($madre);
        $params[':id'] = $madre->getId();

        return $stmt->execute($params);
    }

    private function mapParams(Madre $madre): array
    {
        return [
            ':fechaIngreso' => $madre->getFechaIngreso(),
            ':esVirtual' => $madre->isEsVirtual() ? 1 : 0,
            ':primerNombre' => $madre->getPrimerNombre(),
            ':segundoNombre' => $madre->getSegundoNombre(),
            ':primerApellido' => $madre->getPrimerApellido(),
            ':segundoApellido' => $madre->getSegundoApellido(),
            ':tipoDocumento' => $madre->getTipoDocumento(),
            ':numeroDocumento' => $madre->getNumeroDocumento(),
            ':fechaNacimiento' => $madre->getFechaNacimiento(),
            ':edad' => $madre->getEdad(),
            ':sexo' => $madre->getSexo(),
            ':numeroTelefono' => $madre->getNumeroTelefono(),
            ':otroContacto' => $madre->getOtroContacto(),
            ':correoElectronico' => $madre->getCorreoElectronico(),
            ':redesSociales' => $madre->getRedesSociales(),
            ':direccion' => $madre->getDireccion(),
            ':barrio' => $madre->getBarrio(),
            ':ciudad' => $madre->getCiudad(),
            ':numeroHijos' => $madre->getNumeroHijos(),
            ':perdidas' => $madre->getPerdidas(),
            ':estadoCivil' => $madre->getEstadoCivil(),
            ':nombrePareja' => $madre->getNombrePareja(),
            ':telefonoPareja' => $madre->getTelefonoPareja(),
            ':deAcuerdoAborto' => $madre->getDeAcuerdoAborto(),
            ':nivelEstudio' => $madre->getNivelEstudio(),
            ':ocupacion' => $madre->getOcupacion(),
            ':religion' => $madre->getReligion(),
            ':epsId' => $madre->getEpsId(),
            ':sisben' => $madre->getSisben(),
            ':enfermedadesMedicamento' => $madre->getEnfermedadesMedicamento(),
            ':seEnteroPor' => $madre->getSeEnteroPor(),
            ':orientadoraId' => $madre->getOrientadoraId(),
            ':aliadoId' => $madre->getAliadoId(),
            ':asisteDiscipulado' => $madre->getAsisteDiscipulado(),
            ':desvinculo' => $madre->getDesvinculo(),
            ':novedades' => $madre->getNovedades()
        ];
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
        $madre->setCorreoElectronico($row['correo_electronico']);
        $madre->setRedesSociales($row['redes_sociales']);
        $madre->setDireccion($row['direccion']);
        $madre->setBarrio($row['barrio']);
        $madre->setCiudad($row['ciudad']);
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

    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN desvinculo IS NULL OR desvinculo = '' THEN 1 ELSE 0 END) as activas,
                    SUM(CASE WHEN desvinculo IS NOT NULL AND desvinculo != '' THEN 1 ELSE 0 END) as inactivas
                FROM madres";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
