<?php

declare(strict_types=1);

namespace PhilipRehberger\Geo;

use JsonSerializable;
use Stringable;

/**
 * Immutable value object representing a geographic coordinate.
 */
final readonly class Coordinate implements JsonSerializable, Stringable
{
    /**
     * Create a new Coordinate instance.
     */
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    /**
     * Check whether the coordinate has valid latitude and longitude values.
     */
    public function isValid(): bool
    {
        return $this->latitude >= -90.0
            && $this->latitude <= 90.0
            && $this->longitude >= -180.0
            && $this->longitude <= 180.0;
    }

    /**
     * Convert the coordinate to an associative array.
     *
     * @return array{latitude: float, longitude: float}
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Serialize the coordinate for JSON encoding.
     *
     * @return array{latitude: float, longitude: float}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get a string representation of the coordinate.
     */
    public function __toString(): string
    {
        return sprintf('%f,%f', $this->latitude, $this->longitude);
    }
}
