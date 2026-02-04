<?php

class SesionFormacion implements JsonSerializable
{
    private ?int $id;
    private int $programaId;
    private ?string $programaNombre;
    private string $tipoSesion;
    private string $fechaSesion;
    private string $responsables;
    private ?string $temasTratados;
    private ?string $observaciones;
    private ?array $madresAsistentes;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        int $programaId,
        string $tipoSesion,
        string $fechaSesion,
        string $responsables,
        ?int $id = null,
        ?string $programaNombre = null,
        ?string $temasTratados = null,
        ?string $observaciones = null,
        ?array $madresAsistentes = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->programaId = $programaId;
        $this->programaNombre = $programaNombre;
        $this->tipoSesion = $tipoSesion;
        $this->fechaSesion = $fechaSesion;
        $this->responsables = $responsables;
        $this->temasTratados = $temasTratados;
        $this->observaciones = $observaciones;
        $this->madresAsistentes = $madresAsistentes;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProgramaId(): int
    {
        return $this->programaId;
    }

    public function getProgramaNombre(): ?string
    {
        return $this->programaNombre;
    }

    public function getTipoSesion(): string
    {
        return $this->tipoSesion;
    }

    public function getFechaSesion(): string
    {
        return $this->fechaSesion;
    }

    public function getResponsables(): string
    {
        return $this->responsables;
    }

    public function getTemasTratados(): ?string
    {
        return $this->temasTratados;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function getMadresAsistentes(): ?array
    {
        return $this->madresAsistentes;
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

    public function setProgramaId(int $programaId): void
    {
        $this->programaId = $programaId;
    }

    public function setProgramaNombre(?string $programaNombre): void
    {
        $this->programaNombre = $programaNombre;
    }

    public function setTipoSesion(string $tipoSesion): void
    {
        $this->tipoSesion = $tipoSesion;
    }

    public function setFechaSesion(string $fechaSesion): void
    {
        $this->fechaSesion = $fechaSesion;
    }

    public function setResponsables(string $responsables): void
    {
        $this->responsables = $responsables;
    }

    public function setTemasTratados(?string $temasTratados): void
    {
        $this->temasTratados = $temasTratados;
    }

    public function setObservaciones(?string $observaciones): void
    {
        $this->observaciones = $observaciones;
    }

    public function setMadresAsistentes(?array $madresAsistentes): void
    {
        $this->madresAsistentes = $madresAsistentes;
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
            'programaId' => $this->programaId,
            'programaNombre' => $this->programaNombre,
            'tipoSesion' => $this->tipoSesion,
            'fechaSesion' => $this->fechaSesion,
            'responsables' => $this->responsables,
            'temasTratados' => $this->temasTratados,
            'observaciones' => $this->observaciones,
            'madresAsistentes' => $this->madresAsistentes,
            'totalAsistentes' => $this->madresAsistentes ? count($this->madresAsistentes) : 0,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}
