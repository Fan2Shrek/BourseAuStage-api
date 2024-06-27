<?php

namespace App\Tests\Factory;

use App\Entity\Request;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Request>
 */
final class RequestFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'student' => StudentFactory::random(),
            'offer' => OfferFactory::random(),
        ];
    }

    protected static function getClass(): string
    {
        return Request::class;
    }
}
