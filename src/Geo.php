<?php

declare(strict_types=1);

namespace PhilipRehberger\Geo;

/**
 * Static API for geospatial calculations.
 */
final class Geo
{
    /**
     * Calculate the distance between two coordinates using the Haversine formula.
     */
    public static function distance(Coordinate $a, Coordinate $b, string $unit = 'km'): float
    {
        $u = Units::fromString($unit);
        $R = $u->earthRadius();

        $latA = deg2rad($a->latitude);
        $latB = deg2rad($b->latitude);
        $dLat = deg2rad($b->latitude - $a->latitude);
        $dLng = deg2rad($b->longitude - $a->longitude);

        $h = sin($dLat / 2) ** 2
            + cos($latA) * cos($latB) * sin($dLng / 2) ** 2;

        return 2 * $R * asin(sqrt($h));
    }

    /**
     * Calculate the distance between two coordinates using the Vincenty formula.
     *
     * More accurate than Haversine for long distances and near-antipodal points.
     */
    public static function distanceVincenty(Coordinate $a, Coordinate $b, string $unit = 'km'): float
    {
        $u = Units::fromString($unit);
        $R = $u->earthRadius();

        $latA = deg2rad($a->latitude);
        $latB = deg2rad($b->latitude);
        $dLng = deg2rad($b->longitude - $a->longitude);

        $cosLatA = cos($latA);
        $cosLatB = cos($latB);
        $sinLatA = sin($latA);
        $sinLatB = sin($latB);
        $cosDLng = cos($dLng);
        $sinDLng = sin($dLng);

        $numerator = sqrt(
            ($cosLatB * $sinDLng) ** 2
            + ($cosLatA * $sinLatB - $sinLatA * $cosLatB * $cosDLng) ** 2
        );

        $denominator = $sinLatA * $sinLatB + $cosLatA * $cosLatB * $cosDLng;

        return $R * atan2($numerator, $denominator);
    }

    /**
     * Calculate a bounding box around a center coordinate with the given radius.
     */
    public static function boundingBox(Coordinate $center, float $radius, string $unit = 'km'): BoundingBox
    {
        $u = Units::fromString($unit);
        $R = $u->earthRadius();

        $lat = deg2rad($center->latitude);
        $lng = deg2rad($center->longitude);

        $angularRadius = $radius / $R;

        $minLat = rad2deg($lat - $angularRadius);
        $maxLat = rad2deg($lat + $angularRadius);

        $deltaLng = asin(sin($angularRadius) / cos($lat));
        $minLng = rad2deg($lng - $deltaLng);
        $maxLng = rad2deg($lng + $deltaLng);

        return new BoundingBox(
            minLat: max($minLat, -90.0),
            maxLat: min($maxLat, 90.0),
            minLng: max($minLng, -180.0),
            maxLng: min($maxLng, 180.0),
        );
    }

    /**
     * Determine whether a point lies inside a polygon using the ray casting algorithm.
     *
     * @param  array<Coordinate>  $polygon  Array of Coordinate objects forming the polygon vertices
     */
    public static function contains(array $polygon, Coordinate $point): bool
    {
        $count = count($polygon);

        if ($count < 3) {
            return false;
        }

        $inside = false;

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = $polygon[$i]->longitude;
            $yi = $polygon[$i]->latitude;
            $xj = $polygon[$j]->longitude;
            $yj = $polygon[$j]->latitude;

            $intersect = (($yi > $point->latitude) !== ($yj > $point->latitude))
                && ($point->longitude < ($xj - $xi) * ($point->latitude - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * Calculate the initial bearing from one coordinate to another.
     *
     * Returns the bearing in degrees (0-360).
     */
    public static function bearing(Coordinate $from, Coordinate $to): float
    {
        $latA = deg2rad($from->latitude);
        $latB = deg2rad($to->latitude);
        $dLng = deg2rad($to->longitude - $from->longitude);

        $x = cos($latB) * sin($dLng);
        $y = cos($latA) * sin($latB) - sin($latA) * cos($latB) * cos($dLng);

        $bearing = rad2deg(atan2($x, $y));

        return fmod($bearing + 360.0, 360.0);
    }

    /**
     * Calculate the midpoint between two coordinates.
     */
    public static function midpoint(Coordinate $a, Coordinate $b): Coordinate
    {
        $latA = deg2rad($a->latitude);
        $lngA = deg2rad($a->longitude);
        $latB = deg2rad($b->latitude);
        $dLng = deg2rad($b->longitude - $a->longitude);

        $bx = cos($latB) * cos($dLng);
        $by = cos($latB) * sin($dLng);

        $lat = atan2(
            sin($latA) + sin($latB),
            sqrt((cos($latA) + $bx) ** 2 + $by ** 2)
        );

        $lng = $lngA + atan2($by, cos($latA) + $bx);

        return new Coordinate(
            latitude: rad2deg($lat),
            longitude: rad2deg($lng),
        );
    }

    /**
     * Calculate the destination coordinate given a start point, bearing, and distance.
     */
    public static function destination(Coordinate $start, float $bearing, float $distance, string $unit = 'km'): Coordinate
    {
        $u = Units::fromString($unit);
        $R = $u->earthRadius();

        $lat = deg2rad($start->latitude);
        $lng = deg2rad($start->longitude);
        $brng = deg2rad($bearing);
        $angularDist = $distance / $R;

        $destLat = asin(
            sin($lat) * cos($angularDist)
            + cos($lat) * sin($angularDist) * cos($brng)
        );

        $destLng = $lng + atan2(
            sin($brng) * sin($angularDist) * cos($lat),
            cos($angularDist) - sin($lat) * sin($destLat)
        );

        return new Coordinate(
            latitude: rad2deg($destLat),
            longitude: rad2deg($destLng),
        );
    }
}
