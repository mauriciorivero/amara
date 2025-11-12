<?php

require_once 'Usuario.php';

class LogActividad {
    // Atributos privados
    private ?int $id;
    private int $usuarioId;
    private ?Usuario $usuario; // Relación de composición
    private string $accion;
    private ?string $detalle;
    private ?string $fechaHora;
    private ?string $ipAddress;

    // Constructor - Relación de composición con Usuario (ON DELETE CASCADE)
    public function __construct(
        int $usuarioId,
        string $accion,
        ?int $id = null,
        ?Usuario $usuario = null,
        ?string $detalle = null,
        ?string $fechaHora = null,
        ?string $ipAddress = null
    ) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->usuario = $usuario;
        $this->accion = $accion;
        $this->detalle = $detalle;
        $this->fechaHora = $fechaHora;
        $this->ipAddress = $ipAddress;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getUsuarioId(): int {
        return $this->usuarioId;
    }

    public function getUsuario(): ?Usuario {
        return $this->usuario;
    }

    public function getAccion(): string {
        return $this->accion;
    }

    public function getDetalle(): ?string {
        return $this->detalle;
    }

    public function getFechaHora(): ?string {
        return $this->fechaHora;
    }

    public function getIpAddress(): ?string {
        return $this->ipAddress;
    }

    // Setters
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setUsuarioId(int $usuarioId): void {
        $this->usuarioId = $usuarioId;
    }

    public function setUsuario(?Usuario $usuario): void {
        $this->usuario = $usuario;
        if ($usuario !== null) {
            $this->usuarioId = $usuario->getId();
        }
    }

    public function setAccion(string $accion): void {
        $this->accion = $accion;
    }

    public function setDetalle(?string $detalle): void {
        $this->detalle = $detalle;
    }

    public function setFechaHora(?string $fechaHora): void {
        $this->fechaHora = $fechaHora;
    }

    public function setIpAddress(?string $ipAddress): void {
        $this->ipAddress = $ipAddress;
    }
}

