<?php

declare(strict_types=1);

namespace PhilipRehberger\Geo;

/**
 * Immutable value object representing a geographic bounding box.
 */
final readonly class BoundingBox
{
    /**
     * Create a new BoundingBox instance.
     */
    public function __construct(
        public float $minLat,
        public float $maxLat,
        public float $minLng,
        public float $maxLng,
    ) {}

    /**
     * Check whether a coordinate falls within this bounding box.
     */
    public function contains(Coordinate $point): bool
    {
        return $point->latitude >= $this->minLat
            && $point->latitude <= $this->maxLat
            && $point->longitude >= $this->minLng
            && $point->longitude <= $this->maxLng;
    }

    /**
     * Convert the bounding box to an associative array.
     *
     * @return array{min_lat: float, max_lat: float, min_lng: float, max_lng: float}
     */
    public function toArray(): array
    {
        return [
            'min_lat' => $this->minLat,
            'max_lat' => $this->maxLat,
            'min_lng' => $this->minLng,
            'max_lng' => $this->maxLng,
        ];
    }
}
