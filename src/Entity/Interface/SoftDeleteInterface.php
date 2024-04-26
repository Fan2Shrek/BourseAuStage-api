<?php

namespace App\Entity\Interface;

interface SoftDeleteInterface
{
    public function getDeletedAt(): ?\DateTimeInterface;

    public function isDeleted(): bool;
}
