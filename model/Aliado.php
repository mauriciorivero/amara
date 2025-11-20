<?php

class Aliado implements JsonSerializable
{
    // Atributos privados
    private ?int $id;
    private string $nombre;
    private ?string $descripcion;
    private ?string $personaContactoExterno;
    private ?int $usuarioRegistroId;
    private ?string $telefono;
    private ?string $correo;
    private ?string $direccion;
    private string $estado;
    private bool $activo;
    private ?string $createdAt;
    private ?string $updatedAt;

    // Constructor
    public function __construct(
        string $nombre,
        ?int $id = null,
        ?string $descripcion = null,
        ?string $personaContactoExterno = null,
        ?int $usuarioRegistroId = null,
        ?string $telefono = null,
        ?string $correo = null,
        ?string $direccion = null,
        string $estado = 'activo',
        bool $activo = true,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->personaContactoExterno = $personaContactoExterno;
        $this->usuarioRegistroId = $usuarioRegistroId;
        $this->telefono = $telefono;
        $this->correo = $correo;
        $this->direccion = $direccion;
        $this->estado = $estado;
        $this->activo = $activo;
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

    public function getPersonaContactoExterno(): ?string
    {
        return $this->personaContactoExterno;
    }

    public function getUsuarioRegistroId(): ?int
    {
        return $this->usuarioRegistroId;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function isActivo(): bool
    {
        return $this->activo;
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

    public function setPersonaContactoExterno(?string $personaContactoExterno): void
    {
        $this->personaContactoExterno = $personaContactoExterno;
    }

    public function setUsuarioRegistroId(?int $usuarioRegistroId): void
    {
        $this->usuarioRegistroId = $usuarioRegistroId;
    }

    public function setTelefono(?string $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function setCorreo(?string $correo): void
    {
        $this->correo = $correo;
    }

    public function setDireccion(?string $direccion): void
    {
        $this->direccion = $direccion;
    }

    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'personaContactoExterno' => $this->personaContactoExterno,
            'usuarioRegistroId' => $this->usuarioRegistroId,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'direccion' => $this->direccion,
            'estado' => $this->estado,
            'activo' => $this->activo,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}

