<?php

namespace App\Interface;

interface SoftDeleteInterface
{
    public function getDeletedAt(): ?\DateTimeInterface;

    public function isDeleted(): bool;
}
