<?php

require_once 'Embarazo.php';
require_once 'Madre.php';

class Bebe {
    // Atributos privados
    private ?int $id;
    private int $embarazoId;
    private ?Embarazo $embarazo; // Relación de composición
    private int $madreId;
    private ?Madre $madre; // Relación de composición
    private ?string $nombre;
    private ?string $sexo;
    private ?string $fechaNacimiento;
    private bool $esMellizo;
    private string $estado;
    private ?string $fechaIncidente;
    private ?string $observaciones;
    private ?string $createdAt;
    private ?string $updatedAt;

    // Constructor - Relaciones de composición con Embarazo y Madre (ON DELETE CASCADE)
    public function __construct(
        int $embarazoId,
        int $madreId,
        ?int $id = null,
        ?Embarazo $embarazo = null,
        ?Madre $madre = null,
        ?string $nombre = null,
        ?string $sexo = null,
        ?string $fechaNacimiento = null,
        bool $esMellizo = false,
        string $estado = 'Por nacer',
        ?string $fechaIncidente = null,
        ?string $observaciones = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->embarazoId = $embarazoId;
        $this->embarazo = $embarazo;
        $this->madreId = $madreId;
        $this->madre = $madre;
        $this->nombre = $nombre;
        $this->sexo = $sexo;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->esMellizo = $esMellizo;
        $this->estado = $estado;
        $this->fechaIncidente = $fechaIncidente;
        $this->observaciones = $observaciones;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getEmbarazoId(): int {
        return $this->embarazoId;
    }

    public function getEmbarazo(): ?Embarazo {
        return $this->embarazo;
    }

    public function getMadreId(): int {
        return $this->madreId;
    }

    public function getMadre(): ?Madre {
        return $this->madre;
    }

    public function getNombre(): ?string {
        return $this->nombre;
    }

    public function getSexo(): ?string {
        return $this->sexo;
    }

    public function getFechaNacimiento(): ?string {
        return $this->fechaNacimiento;
    }

    public function isEsMellizo(): bool {
        return $this->esMellizo;
    }

    public function getEstado(): string {
        return $this->estado;
    }

    public function getFechaIncidente(): ?string {
        return $this->fechaIncidente;
    }

    public function getObservaciones(): ?string {
        return $this->observaciones;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }

    public function hasNacido(): bool {
        return $this->fechaNacimiento !== null;
    }

    // Setters
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setEmbarazoId(int $embarazoId): void {
        $this->embarazoId = $embarazoId;
    }

    public function setEmbarazo(?Embarazo $embarazo): void {
        $this->embarazo = $embarazo;
        if ($embarazo !== null) {
            $this->embarazoId = $embarazo->getId();
        }
    }

    public function setMadreId(int $madreId): void {
        $this->madreId = $madreId;
    }

    public function setMadre(?Madre $madre): void {
        $this->madre = $madre;
        if ($madre !== null) {
            $this->madreId = $madre->getId();
        }
    }

    public function setNombre(?string $nombre): void {
        $this->nombre = $nombre;
    }

    public function setSexo(?string $sexo): void {
        $this->sexo = $sexo;
    }

    public function setFechaNacimiento(?string $fechaNacimiento): void {
        $this->fechaNacimiento = $fechaNacimiento;
    }

    public function setEsMellizo(bool $esMellizo): void {
        $this->esMellizo = $esMellizo;
    }

    public function setEstado(string $estado): void {
        $this->estado = $estado;
    }

    public function setFechaIncidente(?string $fechaIncidente): void {
        $this->fechaIncidente = $fechaIncidente;
    }

    public function setObservaciones(?string $observaciones): void {
        $this->observaciones = $observaciones;
    }

    public function setCreatedAt(?string $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(?string $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
}

