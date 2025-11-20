<?php

require_once 'Madre.php';
require_once 'Bebe.php';

class Ayuda implements JsonSerializable
{
    // Atributos privados
    private ?int $id;
    private int $madreId;
    private ?Madre $madre;
    private ?int $bebeId;
    private ?Bebe $bebe;
    private string $tipoAyuda;
    private string $origenAyuda;
    private string $fechaRecepcion;
    private ?float $valor;
    private string $estado;
    private ?string $observaciones;
    private ?string $createdAt;
    private ?string $updatedAt;

    // Constructor - Relación de composición con Madre y Bebe
    public function __construct(
        int $madreId,
        string $tipoAyuda,
        string $fechaRecepcion,
        ?int $id = null,
        ?Madre $madre = null,
        ?int $bebeId = null,
        ?Bebe $bebe = null,
        string $origenAyuda = 'corporacion',
        ?float $valor = 0.0,
        string $estado = 'pendiente',
        ?string $observaciones = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->madreId = $madreId;
        $this->madre = $madre;
        $this->bebeId = $bebeId;
        $this->bebe = $bebe;
        $this->tipoAyuda = $tipoAyuda;
        $this->origenAyuda = $origenAyuda;
        $this->fechaRecepcion = $fechaRecepcion;
        $this->valor = $valor;
        $this->estado = $estado;
        $this->observaciones = $observaciones;
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

    public function getBebeId(): ?int
    {
        return $this->bebeId;
    }

    public function getBebe(): ?Bebe
    {
        return $this->bebe;
    }

    public function getTipoAyuda(): string
    {
        return $this->tipoAyuda;
    }

    public function getOrigenAyuda(): string
    {
        return $this->origenAyuda;
    }

    public function getFechaRecepcion(): string
    {
        return $this->fechaRecepcion;
    }

    public function getValor(): ?float
    {
        return $this->valor;
    }

    public function getEstado(): string
    {
        return $this->estado;
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

    public function setBebeId(?int $bebeId): void
    {
        $this->bebeId = $bebeId;
    }

    public function setBebe(?Bebe $bebe): void
    {
        $this->bebe = $bebe;
        if ($bebe !== null) {
            $this->bebeId = $bebe->getId();
        }
    }

    public function setTipoAyuda(string $tipoAyuda): void
    {
        $this->tipoAyuda = $tipoAyuda;
    }

    public function setOrigenAyuda(string $origenAyuda): void
    {
        $this->origenAyuda = $origenAyuda;
    }

    public function setFechaRecepcion(string $fechaRecepcion): void
    {
        $this->fechaRecepcion = $fechaRecepcion;
    }

    public function setValor(?float $valor): void
    {
        $this->valor = $valor;
    }

    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
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

    // Métodos de negocio
    public function esParaBebe(): bool
    {
        $tiposParaBebe = ['kit_recien_nacido', 'salud_recien_nacido', 'elementos_recien_nacido'];
        return in_array($this->tipoAyuda, $tiposParaBebe) && $this->bebeId !== null;
    }

    public function requiereBebeId(): bool
    {
        $tiposParaBebe = ['kit_recien_nacido', 'salud_recien_nacido', 'elementos_recien_nacido'];
        return in_array($this->tipoAyuda, $tiposParaBebe);
    }

    // JsonSerializable implementation
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'madreId' => $this->madreId,
            'madre' => $this->madre,
            'bebeId' => $this->bebeId,
            'bebe' => $this->bebe,
            'tipoAyuda' => $this->tipoAyuda,
            'origenAyuda' => $this->origenAyuda,
            'fechaRecepcion' => $this->fechaRecepcion,
            'valor' => $this->valor,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'esParaBebe' => $this->esParaBebe(),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}

