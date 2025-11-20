<?php

require_once 'Madre.php';
require_once 'Programa.php';

class MadrePrograma implements JsonSerializable
{
    // Atributos privados
    private ?int $id;
    private int $madreId;
    private ?Madre $madre;
    private int $programaId;
    private ?Programa $programa;
    private string $fechaInscripcion;
    private string $estado;
    private ?string $observacionesSeguimiento;
    private ?string $createdAt;
    private ?string $updatedAt;

    // Constructor - Relación de composición con Madre y Programa
    public function __construct(
        int $madreId,
        int $programaId,
        string $fechaInscripcion,
        ?int $id = null,
        ?Madre $madre = null,
        ?Programa $programa = null,
        string $estado = 'inscrita',
        ?string $observacionesSeguimiento = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->madreId = $madreId;
        $this->madre = $madre;
        $this->programaId = $programaId;
        $this->programa = $programa;
        $this->fechaInscripcion = $fechaInscripcion;
        $this->estado = $estado;
        $this->observacionesSeguimiento = $observacionesSeguimiento;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMadreId(): int
    {
        return $this->madreId;
    }

    public function getMadre(): ?Madre
    {
        return $this->madre;
    }

    public function getProgramaId(): int
    {
        return $this->programaId;
    }

    public function getPrograma(): ?Programa
    {
        return $this->programa;
    }

    public function getFechaInscripcion(): string
    {
        return $this->fechaInscripcion;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getObservacionesSeguimiento(): ?string
    {
        return $this->observacionesSeguimiento;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setMadreId(int $madreId): void
    {
        $this->madreId = $madreId;
    }

    public function setMadre(?Madre $madre): void
    {
        $this->madre = $madre;
        if ($madre !== null) {
            $this->madreId = $madre->getId();
        }
    }

    public function setProgramaId(int $programaId): void
    {
        $this->programaId = $programaId;
    }

    public function setPrograma(?Programa $programa): void
    {
        $this->programa = $programa;
        if ($programa !== null) {
            $this->programaId = $programa->getId();
        }
    }

    public function setFechaInscripcion(string $fechaInscripcion): void
    {
        $this->fechaInscripcion = $fechaInscripcion;
    }

    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    public function setObservacionesSeguimiento(?string $observacionesSeguimiento): void
    {
        $this->observacionesSeguimiento = $observacionesSeguimiento;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    // JsonSerializable implementation
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'madreId' => $this->madreId,
            'madre' => $this->madre,
            'programaId' => $this->programaId,
            'programa' => $this->programa,
            'fechaInscripcion' => $this->fechaInscripcion,
            'estado' => $this->estado,
            'observacionesSeguimiento' => $this->observacionesSeguimiento,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}

