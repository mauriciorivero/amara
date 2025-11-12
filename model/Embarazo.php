<?php

require_once 'Madre.php';

class Embarazo {
    // Atributos privados
    private ?int $id;
    private int $madreId;
    private ?Madre $madre; // Relación de composición
    private int $totalBebesNacidos;
    private int $totalBebesPorNacer;
    private int $bebesNoNacidos;
    private int $bebesFallecidos;
    private bool $esMultiple;
    private ?string $createdAt;
    private ?string $updatedAt;

    // Constructor - Relación de composición con Madre (ON DELETE CASCADE)
    public function __construct(
        int $madreId,
        ?int $id = null,
        ?Madre $madre = null,
        int $totalBebesNacidos = 0,
        int $totalBebesPorNacer = 0,
        int $bebesNoNacidos = 0,
        int $bebesFallecidos = 0,
        bool $esMultiple = false,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->madreId = $madreId;
        $this->madre = $madre;
        $this->totalBebesNacidos = $totalBebesNacidos;
        $this->totalBebesPorNacer = $totalBebesPorNacer;
        $this->bebesNoNacidos = $bebesNoNacidos;
        $this->bebesFallecidos = $bebesFallecidos;
        $this->esMultiple = $esMultiple;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getMadreId(): int {
        return $this->madreId;
    }

    public function getMadre(): ?Madre {
        return $this->madre;
    }

    public function getTotalBebesNacidos(): int {
        return $this->totalBebesNacidos;
    }

    public function getTotalBebesPorNacer(): int {
        return $this->totalBebesPorNacer;
    }

    public function getBebesNoNacidos(): int {
        return $this->bebesNoNacidos;
    }

    public function getBebesFallecidos(): int {
        return $this->bebesFallecidos;
    }

    public function isEsMultiple(): bool {
        return $this->esMultiple;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }

    public function getTotalBebes(): int {
        return $this->totalBebesNacidos + $this->totalBebesPorNacer + 
               $this->bebesNoNacidos + $this->bebesFallecidos;
    }

    // Setters
    public function setId(?int $id): void {
        $this->id = $id;
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

    public function setTotalBebesNacidos(int $totalBebesNacidos): void {
        $this->totalBebesNacidos = $totalBebesNacidos;
    }

    public function setTotalBebesPorNacer(int $totalBebesPorNacer): void {
        $this->totalBebesPorNacer = $totalBebesPorNacer;
    }

    public function setBebesNoNacidos(int $bebesNoNacidos): void {
        $this->bebesNoNacidos = $bebesNoNacidos;
    }

    public function setBebesFallecidos(int $bebesFallecidos): void {
        $this->bebesFallecidos = $bebesFallecidos;
    }

    public function setEsMultiple(bool $esMultiple): void {
        $this->esMultiple = $esMultiple;
    }

    public function setCreatedAt(?string $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(?string $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
}

