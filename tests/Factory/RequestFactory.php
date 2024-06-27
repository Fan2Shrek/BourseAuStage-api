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
            'location' => self::faker()->city(),
            'student' => StudentFactory::new(),
            'isInternship' => self::faker()->boolean(),
            'name' => self::faker()->sentence(),
            'end' => self::faker()->dateTime(),
            'start' => self::faker()->dateTime(),
            'description' => self::faker()->text(255),
            'student' => StudentFactory::random(),
            'offer' => OfferFactory::random(),
        ];
    }

    protected static function getClass(): string
    {
        return Request::class;
    }
}
