<?php

namespace App\Interface;

interface ActionTrackingInterface
{
    public function getCreatedAt(): \DateTimeInterface;

    public function getUpdatedAt(): ?\DateTimeInterface;
}
