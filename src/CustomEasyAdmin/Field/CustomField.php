<?php

namespace App\CustomEasyAdmin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class CustomField implements FieldInterface
{
    use FieldTrait;

    // Useless ::new method because of the required $propertyName parameter
    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label);
    }

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function create(string $value, $label = null): self
    {
        return (new self())
            ->setProperty('')
            ->setValue($value)
            ->setFormattedValue($value)
            ->setLabel($label);
    }
}
