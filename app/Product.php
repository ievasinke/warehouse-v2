<?php

namespace App;

use Carbon\Carbon;
use JsonSerializable;

class Product implements JsonSerializable
{
    private int $id;
    private string $name;
    private ?string $description;
    private int $amount;
    private string $createdBy;
    private Carbon $createdAt;
    private ?Carbon $updatedAt;
    private ?Carbon $deletedAt;

    public function __construct(
        int     $id,
        string  $name,
        ?string $description,
        int     $amount,
        string  $createdBy,
        string  $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->amount = $amount;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt ? Carbon::parse($createdAt) : Carbon::now();
        $this->updatedAt = $updatedAt ? Carbon::parse($updatedAt) : null;
        $this->deletedAt = $deletedAt ? Carbon::parse($deletedAt) : null;

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount += $amount;
        $this->updatedAt = Carbon::now();
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?Carbon
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?Carbon $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'createdBy' => $this->createdBy,
            'createdAt' => $this->getCreatedAt()->toIso8601String(),
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->toIso8601String() : null,
            'deletedAt' => $this->getDeletedAt() ? $this->getDeletedAt()->toIso8601String() : null
        ];
    }
}