<?php

class Usuario {
    // Atributos privados
    private ?int $id;
    private ?string $tipoDocumento;
    private ?string $numeroDocumento;
    private string $primerNombre;
    private ?string $segundoNombre;
    private string $primerApellido;
    private ?string $segundoApellido;
    private ?string $correo;
    private ?string $clave;
    private ?string $tipoUsuario;

    // Constructor
    public function __construct(
        string $primerNombre,
        string $primerApellido,
        ?int $id = null,
        ?string $tipoDocumento = null,
        ?string $numeroDocumento = null,
        ?string $segundoNombre = null,
        ?string $segundoApellido = null,
        ?string $correo = null,
        ?string $clave = null,
        ?string $tipoUsuario = null
    ) {
        $this->id = $id;
        $this->tipoDocumento = $tipoDocumento;
        $this->numeroDocumento = $numeroDocumento;
        $this->primerNombre = $primerNombre;
        $this->segundoNombre = $segundoNombre;
        $this->primerApellido = $primerApellido;
        $this->segundoApellido = $segundoApellido;
        $this->correo = $correo;
        $this->clave = $clave;
        $this->tipoUsuario = $tipoUsuario;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getTipoDocumento(): ?string {
        return $this->tipoDocumento;
    }

    public function getNumeroDocumento(): ?string {
        return $this->numeroDocumento;
    }

    public function getPrimerNombre(): string {
        return $this->primerNombre;
    }

    public function getSegundoNombre(): ?string {
        return $this->segundoNombre;
    }

    public function getPrimerApellido(): string {
        return $this->primerApellido;
    }

    public function getSegundoApellido(): ?string {
        return $this->segundoApellido;
    }

    public function getCorreo(): ?string {
        return $this->correo;
    }

    public function getClave(): ?string {
        return $this->clave;
    }

    public function getTipoUsuario(): ?string {
        return $this->tipoUsuario;
    }

    public function getNombreCompleto(): string {
        return trim($this->primerNombre . ' ' . 
                   ($this->segundoNombre ?? '') . ' ' . 
                   $this->primerApellido . ' ' . 
                   ($this->segundoApellido ?? ''));
    }

    // Setters
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setTipoDocumento(?string $tipoDocumento): void {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function setNumeroDocumento(?string $numeroDocumento): void {
        $this->numeroDocumento = $numeroDocumento;
    }

    public function setPrimerNombre(string $primerNombre): void {
        $this->primerNombre = $primerNombre;
    }

    public function setSegundoNombre(?string $segundoNombre): void {
        $this->segundoNombre = $segundoNombre;
    }

    public function setPrimerApellido(string $primerApellido): void {
        $this->primerApellido = $primerApellido;
    }

    public function setSegundoApellido(?string $segundoApellido): void {
        $this->segundoApellido = $segundoApellido;
    }

    public function setCorreo(?string $correo): void {
        $this->correo = $correo;
    }

    public function setClave(?string $clave): void {
        $this->clave = $clave;
    }

    public function setTipoUsuario(?string $tipoUsuario): void {
        $this->tipoUsuario = $tipoUsuario;
    }
}

