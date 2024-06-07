<?php

namespace App;

use Carbon\Carbon;
use JsonSerializable;

class Product implements JsonSerializable
{
    private string $id;
    private string $name;
    private int $amount;
    private string $createdBy;
    private float $price;
    private ?Carbon $expiresInDays;
    private Carbon $createdAt;
    private ?Carbon $updatedAt;
    private ?Carbon $deletedAt;

    public function __construct(
        string  $id,
        string  $name,
        int     $amount,
        string  $createdBy,
        float   $price,
        ?string $expiresInDays = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
        $this->createdBy = $createdBy;
        $this->price = $price;
        $this->expiresInDays = $expiresInDays ? Carbon::parse($expiresInDays) : null;
        $this->createdAt = $createdAt ? Carbon::parse($createdAt) : Carbon::now();
        $this->updatedAt = $updatedAt ? Carbon::parse($updatedAt) : null;
        $this->deletedAt = $deletedAt ? Carbon::parse($deletedAt) : null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getExpiresInDays(): ?Carbon
    {
        return $this->expiresInDays;
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
            'amount' => $this->amount,
            'createdBy' => $this->createdBy,
            'price' => $this->price,
            'expiresInDays' => $this->expiresInDays ? $this->expiresInDays->toIso8601String() : null,
            'createdAt' => $this->createdAt->toIso8601String(),
            'updatedAt' => $this->updatedAt ? $this->updatedAt->toIso8601String() : null,
            'deletedAt' => $this->deletedAt ? $this->deletedAt->toIso8601String() : null,
        ];
    }
}