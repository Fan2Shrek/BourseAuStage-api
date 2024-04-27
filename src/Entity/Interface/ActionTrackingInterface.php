<?php

namespace App\Entity\Interface;

interface ActionTrackingInterface
{
    public function getCreatedAt(): \DateTimeInterface;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static;
}
