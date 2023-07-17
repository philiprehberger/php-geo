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
     * Convert a bearing in degrees (0-360) to a cardinal or intercardinal direction.
     */
    public static function compassDirection(float $bearing): string
    {
        $bearing = fmod($bearing, 360.0);

        if ($bearing < 0.0) {
            $bearing += 360.0;
        }

        $directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        $index = (int) round($bearing / 45.0) % 8;

        return $directions[$index];
    }

    /**
     * Calculate the area of a polygon in square meters using the Shoelace formula
     * adapted for geodesic coordinates.
     *
     * @param  array<Coordinate>  $polygon  Array of Coordinate objects forming the polygon vertices
     */
    public static function area(array $polygon): float
    {
        $count = count($polygon);

        if ($count < 3) {
            return 0.0;
        }

        $area = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $j = ($i + 1) % $count;

            $latI = deg2rad($polygon[$i]->latitude);
            $latJ = deg2rad($polygon[$j]->latitude);
            $dLng = deg2rad($polygon[$j]->longitude - $polygon[$i]->longitude);

            $area += $dLng * (2 + sin($latI) + sin($latJ));
        }

        $area = abs($area) * 6_371_000.0 ** 2 / 2;

        return $area;
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

    private const GEOHASH_ALPHABET = '0123456789bcdefghjkmnpqrstuvwxyz';

    /**
     * Encode a coordinate into a geohash string.
     */
    public static function encodeGeohash(Coordinate $coord, int $precision = 12): string
    {
        $minLat = -90.0;
        $maxLat = 90.0;
        $minLng = -180.0;
        $maxLng = 180.0;

        $hash = '';
        $bit = 0;
        $ch = 0;
        $isLng = true;

        while (strlen($hash) < $precision) {
            if ($isLng) {
                $mid = ($minLng + $maxLng) / 2;
                if ($coord->longitude >= $mid) {
                    $ch |= (1 << (4 - $bit));
                    $minLng = $mid;
                } else {
                    $maxLng = $mid;
                }
            } else {
                $mid = ($minLat + $maxLat) / 2;
                if ($coord->latitude >= $mid) {
                    $ch |= (1 << (4 - $bit));
                    $minLat = $mid;
                } else {
                    $maxLat = $mid;
                }
            }

            $isLng = ! $isLng;
            $bit++;

            if ($bit === 5) {
                $hash .= self::GEOHASH_ALPHABET[$ch];
                $bit = 0;
                $ch = 0;
            }
        }

        return $hash;
    }

    /**
     * Decode a geohash string to a Coordinate (center of the bounding box).
     */
    public static function decodeGeohash(string $hash): Coordinate
    {
        $bounds = self::geohashBounds($hash);

        return new Coordinate(
            latitude: ($bounds->minLat + $bounds->maxLat) / 2,
            longitude: ($bounds->minLng + $bounds->maxLng) / 2,
        );
    }

    /**
     * Get the bounding box of a geohash cell.
     */
    public static function geohashBounds(string $hash): BoundingBox
    {
        $minLat = -90.0;
        $maxLat = 90.0;
        $minLng = -180.0;
        $maxLng = 180.0;
        $isLng = true;

        for ($i = 0; $i < strlen($hash); $i++) {
            $charIndex = strpos(self::GEOHASH_ALPHABET, $hash[$i]);

            if ($charIndex === false) {
                break;
            }

            for ($bit = 4; $bit >= 0; $bit--) {
                if ($isLng) {
                    $mid = ($minLng + $maxLng) / 2;
                    if (($charIndex >> $bit) & 1) {
                        $minLng = $mid;
                    } else {
                        $maxLng = $mid;
                    }
                } else {
                    $mid = ($minLat + $maxLat) / 2;
                    if (($charIndex >> $bit) & 1) {
                        $minLat = $mid;
                    } else {
                        $maxLat = $mid;
                    }
                }

                $isLng = ! $isLng;
            }
        }

        return new BoundingBox(
            minLat: $minLat,
            maxLat: $maxLat,
            minLng: $minLng,
            maxLng: $maxLng,
        );
    }

    /**
     * Encode an array of coordinates using Google's Encoded Polyline Algorithm.
     *
     * @param  array<Coordinate>  $coordinates
     */
    public static function encodePolyline(array $coordinates): string
    {
        $encoded = '';
        $prevLat = 0;
        $prevLng = 0;

        foreach ($coordinates as $coord) {
            $lat = (int) round($coord->latitude * 1e5);
            $lng = (int) round($coord->longitude * 1e5);

            $encoded .= self::encodePolylineValue($lat - $prevLat);
            $encoded .= self::encodePolylineValue($lng - $prevLng);

            $prevLat = $lat;
            $prevLng = $lng;
        }

        return $encoded;
    }

    /**
     * Decode a Google Encoded Polyline string to an array of Coordinates.
     *
     * @return array<Coordinate>
     */
    public static function decodePolyline(string $encoded): array
    {
        $coordinates = [];
        $index = 0;
        $lat = 0;
        $lng = 0;
        $length = strlen($encoded);

        while ($index < $length) {
            $lat += self::decodePolylineValue($encoded, $index);
            $lng += self::decodePolylineValue($encoded, $index);

            $coordinates[] = new Coordinate(
                latitude: $lat / 1e5,
                longitude: $lng / 1e5,
            );
        }

        return $coordinates;
    }

    /**
     * Calculate the total distance along a route of coordinates.
     *
     * @param  array<Coordinate>  $coordinates
     */
    public static function routeDistance(array $coordinates, Units $unit = Units::Kilometers): float
    {
        $total = 0.0;
        $count = count($coordinates);

        for ($i = 1; $i < $count; $i++) {
            $total += self::distance($coordinates[$i - 1], $coordinates[$i], $unit->value);
        }

        return $total;
    }

    private static function encodePolylineValue(int $value): string
    {
        $value = $value < 0 ? ~($value << 1) : ($value << 1);
        $encoded = '';

        while ($value >= 0x20) {
            $encoded .= chr((($value & 0x1F) | 0x20) + 63);
            $value >>= 5;
        }

        $encoded .= chr($value + 63);

        return $encoded;
    }

    private static function decodePolylineValue(string $encoded, int &$index): int
    {
        $result = 0;
        $shift = 0;

        do {
            $byte = ord($encoded[$index]) - 63;
            $index++;
            $result |= ($byte & 0x1F) << $shift;
            $shift += 5;
        } while ($byte >= 0x20);

        return ($result & 1) ? ~($result >> 1) : ($result >> 1);
    }
}
