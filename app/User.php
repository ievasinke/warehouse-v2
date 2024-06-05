<?php

namespace App;

class User
{
    private string $name;
    private string $accessCode;

    public function __construct(string $name, string $accessCode)
    {
        $this->name = $name;
        $this->accessCode = $accessCode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAccessCode(): string
    {
        return $this->accessCode;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}