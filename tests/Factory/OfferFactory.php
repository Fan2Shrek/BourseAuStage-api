<?php

namespace App\Tests\Factory;

use App\Entity\Offer;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Offer>
 */
final class OfferFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        $availableAt = self::faker()->randomElement([new \DateTime('+1 month'), new \DateTime('+2 days'), new \DateTime('+7 days')]);
        $startDate = (clone $availableAt)->add(self::faker()->randomElement([
            \DateInterval::createFromDateString('1 day'),
            \DateInterval::createFromDateString('2 days'),
            \DateInterval::createFromDateString('3 days'),
            \DateInterval::createFromDateString('4 days'),
            \DateInterval::createFromDateString('5 days'),
            \DateInterval::createFromDateString('6 days'),
        ]));
        $endDate = (clone $startDate)->add(self::faker()->randomElement([
            \DateInterval::createFromDateString('1 week'),
            \DateInterval::createFromDateString('2 weeks'),
            \DateInterval::createFromDateString('1 month'),
            \DateInterval::createFromDateString('2 months'),
            \DateInterval::createFromDateString('6 months'),
        ]));

        return [
            'isInternship' => self::faker()->boolean(),
            'pay' => rand(1, 10) > 7 ? self::faker()->randomNumber(4) : null,
            'name' => join(' ', self::faker()->words(3)),
            'end' => $endDate,
            'start' => $startDate,
            'company' => CompanyFactory::random(),
            'availableAt' => $availableAt,
            'description' => self::faker()->text(255),
            'studyLevel' => StudyLevelFactory::random(),
            'missions' => self::faker()->text(255),
            'profils' => self::faker()->text(255),
        ];
    }

    protected static function getClass(): string
    {
        return Offer::class;
    }
}
