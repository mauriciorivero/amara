<?php

require_once 'Aliado.php';

class Programa implements JsonSerializable
{
    // Atributos privados
    private ?int $id;
    private string $nombre;
    private ?string $descripcion;
    private bool $esPropio;
    private ?int $aliadoId;
    private ?Aliado $aliado;
    private string $responsableNombre;
    private ?string $responsableTelefono;
    private ?string $responsableCorreo;
    private ?string $responsableCargo;
    private string $estado;
    private ?string $fechaInicio;
    private ?string $fechaFin;
    private ?string $createdAt;
    private ?string $updatedAt;

    // Constructor - Relación de composición con Aliado
    public function __construct(
        string $nombre,
        string $responsableNombre,
        ?int $id = null,
        ?string $descripcion = null,
        bool $esPropio = false,
        ?int $aliadoId = null,
        ?Aliado $aliado = null,
        ?string $responsableTelefono = null,
        ?string $responsableCorreo = null,
        ?string $responsableCargo = null,
        string $estado = 'activo',
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->esPropio = $esPropio;
        $this->aliadoId = $aliadoId;
        $this->aliado = $aliado;
        $this->responsableNombre = $responsableNombre;
        $this->responsableTelefono = $responsableTelefono;
        $this->responsableCorreo = $responsableCorreo;
        $this->responsableCargo = $responsableCargo;
        $this->estado = $estado;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function isEsPropio(): bool
    {
        return $this->esPropio;
    }

    public function getAliadoId(): ?int
    {
        return $this->aliadoId;
    }

    public function getAliado(): ?Aliado
    {
        return $this->aliado;
    }

    public function getResponsableNombre(): string
    {
        return $this->responsableNombre;
    }

    public function getResponsableTelefono(): ?string
    {
        return $this->responsableTelefono;
    }

    public function getResponsableCorreo(): ?string
    {
        return $this->responsableCorreo;
    }

    public function getResponsableCargo(): ?string
    {
        return $this->responsableCargo;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getFechaInicio(): ?string
    {
        return $this->fechaInicio;
    }

    public function getFechaFin(): ?string
    {
        return $this->fechaFin;
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

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setDescripcion(?string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    public function setEsPropio(bool $esPropio): void
    {
        $this->esPropio = $esPropio;
    }

    public function setAliadoId(?int $aliadoId): void
    {
        $this->aliadoId = $aliadoId;
    }

    public function setAliado(?Aliado $aliado): void
    {
        $this->aliado = $aliado;
        if ($aliado !== null) {
            $this->aliadoId = $aliado->getId();
        }
    }

    public function setResponsableNombre(string $responsableNombre): void
    {
        $this->responsableNombre = $responsableNombre;
    }

    public function setResponsableTelefono(?string $responsableTelefono): void
    {
        $this->responsableTelefono = $responsableTelefono;
    }

    public function setResponsableCorreo(?string $responsableCorreo): void
    {
        $this->responsableCorreo = $responsableCorreo;
    }

    public function setResponsableCargo(?string $responsableCargo): void
    {
        $this->responsableCargo = $responsableCargo;
    }

    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    public function setFechaInicio(?string $fechaInicio): void
    {
        $this->fechaInicio = $fechaInicio;
    }

    public function setFechaFin(?string $fechaFin): void
    {
        $this->fechaFin = $fechaFin;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    // Métodos de negocio
    public function esPropio(): bool
    {
        return $this->esPropio === true;
    }

    public function requiereAliado(): bool
    {
        return $this->esPropio === false;
    }

    // JsonSerializable implementation
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'esPropio' => $this->esPropio,
            'aliadoId' => $this->aliadoId,
            'aliado' => $this->aliado,
            'responsableNombre' => $this->responsableNombre,
            'responsableTelefono' => $this->responsableTelefono,
            'responsableCorreo' => $this->responsableCorreo,
            'responsableCargo' => $this->responsableCargo,
            'estado' => $this->estado,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}

