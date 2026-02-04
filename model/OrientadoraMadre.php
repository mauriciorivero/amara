<?php

require_once 'Madre.php';
require_once 'Orientadora.php';

class OrientadoraMadre implements JsonSerializable
{
    // Atributos privados
    private ?int $id;
    private int $orientadoraId;
    private ?Orientadora $orientadora;
    private int $madreId;
    private ?Madre $madre;
    private string $fechaAsignacion;
    private ?string $fechaFin;
    private bool $activa;
    private ?string $motivoCambio;
    private ?string $observaciones;
    private ?string $createdAt;
    private ?string $updatedAt;

    // Constructor - Relación de composición con Madre y Orientadora
    public function __construct(
        int $orientadoraId,
        int $madreId,
        string $fechaAsignacion,
        ?int $id = null,
        ?Orientadora $orientadora = null,
        ?Madre $madre = null,
        ?string $fechaFin = null,
        bool $activa = true,
        ?string $motivoCambio = null,
        ?string $observaciones = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->orientadoraId = $orientadoraId;
        $this->orientadora = $orientadora;
        $this->madreId = $madreId;
        $this->madre = $madre;
        $this->fechaAsignacion = $fechaAsignacion;
        $this->fechaFin = $fechaFin;
        $this->activa = $activa;
        $this->motivoCambio = $motivoCambio;
        $this->observaciones = $observaciones;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrientadoraId(): int
    {
        return $this->orientadoraId;
    }

    public function getOrientadora(): ?Orientadora
    {
        return $this->orientadora;
    }

    public function getMadreId(): int
    {
        return $this->madreId;
    }

    public function getMadre(): ?Madre
    {
        return $this->madre;
    }

    public function getFechaAsignacion(): string
    {
        return $this->fechaAsignacion;
    }

    public function getFechaFin(): ?string
    {
        return $this->fechaFin;
    }

    public function getActiva(): bool
    {
        return $this->activa;
    }

    public function getMotivoCambio(): ?string
    {
        return $this->motivoCambio;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
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

    public function setOrientadoraId(int $orientadoraId): void
    {
        $this->orientadoraId = $orientadoraId;
    }

    public function setOrientadora(?Orientadora $orientadora): void
    {
        $this->orientadora = $orientadora;
        if ($orientadora !== null) {
            $this->orientadoraId = $orientadora->getId();
        }
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

    public function setFechaAsignacion(string $fechaAsignacion): void
    {
        $this->fechaAsignacion = $fechaAsignacion;
    }

    public function setFechaFin(?string $fechaFin): void
    {
        $this->fechaFin = $fechaFin;
    }

    public function setActiva(bool $activa): void
    {
        $this->activa = $activa;
    }

    public function setMotivoCambio(?string $motivoCambio): void
    {
        $this->motivoCambio = $motivoCambio;
    }

    public function setObservaciones(?string $observaciones): void
    {
        $this->observaciones = $observaciones;
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
            'orientadoraId' => $this->orientadoraId,
            'orientadora' => $this->orientadora,
            'madreId' => $this->madreId,
            'madre' => $this->madre,
            'fechaAsignacion' => $this->fechaAsignacion,
            'fechaFin' => $this->fechaFin,
            'activa' => $this->activa,
            'motivoCambio' => $this->motivoCambio,
            'observaciones' => $this->observaciones,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}
