<?php

require_once 'Orientadora.php';
require_once 'Aliado.php';
require_once 'Eps.php';

class Madre {
    // Atributos privados
    private ?int $id;
    private string $fechaIngreso;
    private bool $esVirtual;
    private ?string $primerNombre;
    private ?string $segundoNombre;
    private ?string $primerApellido;
    private ?string $segundoApellido;
    private ?string $tipoDocumento;
    private ?string $numeroDocumento;
    private ?string $fechaNacimiento;
    private ?int $edad;
    private ?string $sexo;
    private ?string $numeroTelefono;
    private ?string $otroContacto;
    private int $numeroHijos;
    private int $perdidas;
    private string $estadoCivil;
    private ?string $nombrePareja;
    private ?string $telefonoPareja;
    private ?string $deAcuerdoAborto;
    private ?string $nivelEstudio;
    private ?string $ocupacion;
    private ?string $religion;
    private ?int $epsId;
    private ?Eps $eps;
    private ?string $sisben;
    private ?string $enfermedadesMedicamento;
    private ?string $seEnteroPor;
    private ?int $orientadoraId;
    private ?Orientadora $orientadora;
    private ?int $aliadoId;
    private ?Aliado $aliado;
    private ?string $asisteDiscipulado;
    private ?string $desvinculo;
    private ?string $novedades;
    private ?string $createdAt;
    private ?string $updatedAt;

    // Constructor - Relaciones de agregación con Orientadora, Aliado y Eps
    public function __construct(
        string $fechaIngreso,
        ?int $id = null,
        bool $esVirtual = false,
        ?string $primerNombre = null,
        ?string $segundoNombre = null,
        ?string $primerApellido = null,
        ?string $segundoApellido = null,
        ?string $tipoDocumento = 'CC',
        ?string $numeroDocumento = null,
        ?string $fechaNacimiento = null,
        ?int $edad = null,
        ?string $sexo = null,
        ?string $numeroTelefono = null,
        ?string $otroContacto = null,
        int $numeroHijos = 0,
        int $perdidas = 0,
        string $estadoCivil = 'Soltera',
        ?string $nombrePareja = null,
        ?string $telefonoPareja = null,
        ?string $deAcuerdoAborto = null,
        ?string $nivelEstudio = null,
        ?string $ocupacion = null,
        ?string $religion = null,
        ?int $epsId = null,
        ?Eps $eps = null,
        ?string $sisben = null,
        ?string $enfermedadesMedicamento = null,
        ?string $seEnteroPor = null,
        ?int $orientadoraId = null,
        ?Orientadora $orientadora = null,
        ?int $aliadoId = null,
        ?Aliado $aliado = null,
        ?string $asisteDiscipulado = null,
        ?string $desvinculo = null,
        ?string $novedades = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->fechaIngreso = $fechaIngreso;
        $this->esVirtual = $esVirtual;
        $this->primerNombre = $primerNombre;
        $this->segundoNombre = $segundoNombre;
        $this->primerApellido = $primerApellido;
        $this->segundoApellido = $segundoApellido;
        $this->tipoDocumento = $tipoDocumento;
        $this->numeroDocumento = $numeroDocumento;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->edad = $edad;
        $this->sexo = $sexo;
        $this->numeroTelefono = $numeroTelefono;
        $this->otroContacto = $otroContacto;
        $this->numeroHijos = $numeroHijos;
        $this->perdidas = $perdidas;
        $this->estadoCivil = $estadoCivil;
        $this->nombrePareja = $nombrePareja;
        $this->telefonoPareja = $telefonoPareja;
        $this->deAcuerdoAborto = $deAcuerdoAborto;
        $this->nivelEstudio = $nivelEstudio;
        $this->ocupacion = $ocupacion;
        $this->religion = $religion;
        $this->epsId = $epsId;
        $this->eps = $eps;
        $this->sisben = $sisben;
        $this->enfermedadesMedicamento = $enfermedadesMedicamento;
        $this->seEnteroPor = $seEnteroPor;
        $this->orientadoraId = $orientadoraId;
        $this->orientadora = $orientadora;
        $this->aliadoId = $aliadoId;
        $this->aliado = $aliado;
        $this->asisteDiscipulado = $asisteDiscipulado;
        $this->desvinculo = $desvinculo;
        $this->novedades = $novedades;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getFechaIngreso(): string {
        return $this->fechaIngreso;
    }

    public function isEsVirtual(): bool {
        return $this->esVirtual;
    }

    public function getPrimerNombre(): ?string {
        return $this->primerNombre;
    }

    public function getSegundoNombre(): ?string {
        return $this->segundoNombre;
    }

    public function getPrimerApellido(): ?string {
        return $this->primerApellido;
    }

    public function getSegundoApellido(): ?string {
        return $this->segundoApellido;
    }

    public function getTipoDocumento(): ?string {
        return $this->tipoDocumento;
    }

    public function getNumeroDocumento(): ?string {
        return $this->numeroDocumento;
    }

    public function getFechaNacimiento(): ?string {
        return $this->fechaNacimiento;
    }

    public function getEdad(): ?int {
        return $this->edad;
    }

    public function getSexo(): ?string {
        return $this->sexo;
    }

    public function getNumeroTelefono(): ?string {
        return $this->numeroTelefono;
    }

    public function getOtroContacto(): ?string {
        return $this->otroContacto;
    }

    public function getNumeroHijos(): int {
        return $this->numeroHijos;
    }

    public function getPerdidas(): int {
        return $this->perdidas;
    }

    public function getEstadoCivil(): string {
        return $this->estadoCivil;
    }

    public function getNombrePareja(): ?string {
        return $this->nombrePareja;
    }

    public function getTelefonoPareja(): ?string {
        return $this->telefonoPareja;
    }

    public function getDeAcuerdoAborto(): ?string {
        return $this->deAcuerdoAborto;
    }

    public function getNivelEstudio(): ?string {
        return $this->nivelEstudio;
    }

    public function getOcupacion(): ?string {
        return $this->ocupacion;
    }

    public function getReligion(): ?string {
        return $this->religion;
    }

    public function getEpsId(): ?int {
        return $this->epsId;
    }

    public function getEps(): ?Eps {
        return $this->eps;
    }

    public function getSisben(): ?string {
        return $this->sisben;
    }

    public function getEnfermedadesMedicamento(): ?string {
        return $this->enfermedadesMedicamento;
    }

    public function getSeEnteroPor(): ?string {
        return $this->seEnteroPor;
    }

    public function getOrientadoraId(): ?int {
        return $this->orientadoraId;
    }

    public function getOrientadora(): ?Orientadora {
        return $this->orientadora;
    }

    public function getAlliadoId(): ?int {
        return $this->aliadoId;
    }

    public function getAliado(): ?Aliado {
        return $this->aliado;
    }

    public function getAsisteDiscipulado(): ?string {
        return $this->asisteDiscipulado;
    }

    public function getDesvinculo(): ?string {
        return $this->desvinculo;
    }

    public function getNovedades(): ?string {
        return $this->novedades;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }

    public function getNombreCompleto(): string {
        return trim(($this->primerNombre ?? '') . ' ' . 
                   ($this->segundoNombre ?? '') . ' ' . 
                   ($this->primerApellido ?? '') . ' ' . 
                   ($this->segundoApellido ?? ''));
    }

    public function isActiva(): bool {
        return $this->desvinculo !== 'X';
    }

    // Setters
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setFechaIngreso(string $fechaIngreso): void {
        $this->fechaIngreso = $fechaIngreso;
    }

    public function setEsVirtual(bool $esVirtual): void {
        $this->esVirtual = $esVirtual;
    }

    public function setPrimerNombre(?string $primerNombre): void {
        $this->primerNombre = $primerNombre;
    }

    public function setSegundoNombre(?string $segundoNombre): void {
        $this->segundoNombre = $segundoNombre;
    }

    public function setPrimerApellido(?string $primerApellido): void {
        $this->primerApellido = $primerApellido;
    }

    public function setSegundoApellido(?string $segundoApellido): void {
        $this->segundoApellido = $segundoApellido;
    }

    public function setTipoDocumento(?string $tipoDocumento): void {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function setNumeroDocumento(?string $numeroDocumento): void {
        $this->numeroDocumento = $numeroDocumento;
    }

    public function setFechaNacimiento(?string $fechaNacimiento): void {
        $this->fechaNacimiento = $fechaNacimiento;
    }

    public function setEdad(?int $edad): void {
        $this->edad = $edad;
    }

    public function setSexo(?string $sexo): void {
        $this->sexo = $sexo;
    }

    public function setNumeroTelefono(?string $numeroTelefono): void {
        $this->numeroTelefono = $numeroTelefono;
    }

    public function setOtroContacto(?string $otroContacto): void {
        $this->otroContacto = $otroContacto;
    }

    public function setNumeroHijos(int $numeroHijos): void {
        $this->numeroHijos = $numeroHijos;
    }

    public function setPerdidas(int $perdidas): void {
        $this->perdidas = $perdidas;
    }

    public function setEstadoCivil(string $estadoCivil): void {
        $this->estadoCivil = $estadoCivil;
    }

    public function setNombrePareja(?string $nombrePareja): void {
        $this->nombrePareja = $nombrePareja;
    }

    public function setTelefonoPareja(?string $telefonoPareja): void {
        $this->telefonoPareja = $telefonoPareja;
    }

    public function setDeAcuerdoAborto(?string $deAcuerdoAborto): void {
        $this->deAcuerdoAborto = $deAcuerdoAborto;
    }

    public function setNivelEstudio(?string $nivelEstudio): void {
        $this->nivelEstudio = $nivelEstudio;
    }

    public function setOcupacion(?string $ocupacion): void {
        $this->ocupacion = $ocupacion;
    }

    public function setReligion(?string $religion): void {
        $this->religion = $religion;
    }

    public function setEpsId(?int $epsId): void {
        $this->epsId = $epsId;
    }

    public function setEps(?Eps $eps): void {
        $this->eps = $eps;
        if ($eps !== null) {
            $this->epsId = $eps->getId();
        }
    }

    public function setSisben(?string $sisben): void {
        $this->sisben = $sisben;
    }

    public function setEnfermedadesMedicamento(?string $enfermedadesMedicamento): void {
        $this->enfermedadesMedicamento = $enfermedadesMedicamento;
    }

    public function setSeEnteroPor(?string $seEnteroPor): void {
        $this->seEnteroPor = $seEnteroPor;
    }

    public function setOrientadoraId(?int $orientadoraId): void {
        $this->orientadoraId = $orientadoraId;
    }

    public function setOrientadora(?Orientadora $orientadora): void {
        $this->orientadora = $orientadora;
        if ($orientadora !== null) {
            $this->orientadoraId = $orientadora->getId();
        }
    }

    public function setAliadoId(?int $aliadoId): void {
        $this->aliadoId = $aliadoId;
    }

    public function setAliado(?Aliado $aliado): void {
        $this->aliado = $aliado;
        if ($aliado !== null) {
            $this->aliadoId = $aliado->getId();
        }
    }

    public function setAsisteDiscipulado(?string $asisteDiscipulado): void {
        $this->asisteDiscipulado = $asisteDiscipulado;
    }

    public function setDesvinculo(?string $desvinculo): void {
        $this->desvinculo = $desvinculo;
    }

    public function setNovedades(?string $novedades): void {
        $this->novedades = $novedades;
    }

    public function setCreatedAt(?string $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(?string $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
}

