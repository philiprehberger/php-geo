<?php

declare(strict_types=1);

namespace PhilipRehberger\Geo;

/**
 * Units of distance measurement with conversion factors.
 */
enum Units: string
{
    case Kilometers = 'km';
    case Miles = 'mi';
    case Meters = 'm';
    case NauticalMiles = 'nmi';

    /**
     * Get the Earth's mean radius in this unit.
     */
    public function earthRadius(): float
    {
        return match ($this) {
            self::Kilometers => 6371.0,
            self::Miles => 3958.8,
            self::Meters => 6_371_000.0,
            self::NauticalMiles => 3440.065,
        };
    }

    /**
     * Get the conversion factor from kilometers to this unit.
     */
    public function fromKilometers(): float
    {
        return match ($this) {
            self::Kilometers => 1.0,
            self::Miles => 0.621371,
            self::Meters => 1000.0,
            self::NauticalMiles => 0.539957,
        };
    }

    /**
     * Create a Units instance from a string abbreviation.
     */
    public static function fromString(string $unit): self
    {
        return self::from($unit);
    }
}
