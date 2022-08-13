<?php

declare(strict_types=1);

namespace PhilipRehberger\Geo\Tests;

use PhilipRehberger\Geo\BoundingBox;
use PhilipRehberger\Geo\Coordinate;
use PhilipRehberger\Geo\Geo;
use PhilipRehberger\Geo\Units;
use PHPUnit\Framework\TestCase;

class GeoTest extends TestCase
{
    public function test_coordinate_is_valid(): void
    {
        $coord = new Coordinate(40.7128, -74.0060);
        $this->assertTrue($coord->isValid());
    }

    public function test_coordinate_is_invalid(): void
    {
        $coord = new Coordinate(91.0, -74.0060);
        $this->assertFalse($coord->isValid());

        $coord2 = new Coordinate(40.0, 181.0);
        $this->assertFalse($coord2->isValid());
    }

    public function test_coordinate_to_array(): void
    {
        $coord = new Coordinate(40.7128, -74.0060);
        $this->assertSame(['latitude' => 40.7128, 'longitude' => -74.006], $coord->toArray());
    }

    public function test_coordinate_json_serializable(): void
    {
        $coord = new Coordinate(40.7128, -74.0060);
        $json = json_encode($coord);
        $this->assertJson((string) $json);
        $this->assertStringContainsString('40.7128', (string) $json);
    }

    public function test_coordinate_to_string(): void
    {
        $coord = new Coordinate(40.7128, -74.0060);
        $this->assertStringContainsString('40.7128', (string) $coord);
        $this->assertStringContainsString('-74.006', (string) $coord);
    }

    public function test_haversine_distance_nyc_to_la(): void
    {
        $nyc = new Coordinate(40.7128, -74.0060);
        $la = new Coordinate(33.9425, -118.4081);

        $distance = Geo::distance($nyc, $la);

        // NYC to LA is approximately 3944 km
        $this->assertEqualsWithDelta(3944.0, $distance, 50.0);
    }

    public function test_haversine_distance_in_miles(): void
    {
        $nyc = new Coordinate(40.7128, -74.0060);
        $la = new Coordinate(33.9425, -118.4081);

        $distance = Geo::distance($nyc, $la, 'mi');

        // NYC to LA is approximately 2451 miles
        $this->assertEqualsWithDelta(2451.0, $distance, 30.0);
    }

    public function test_vincenty_distance_nyc_to_la(): void
    {
        $nyc = new Coordinate(40.7128, -74.0060);
        $la = new Coordinate(33.9425, -118.4081);

        $distance = Geo::distanceVincenty($nyc, $la);

        // Vincenty should be close to Haversine for this distance
        $this->assertEqualsWithDelta(3944.0, $distance, 50.0);
    }

    public function test_distance_same_point_is_zero(): void
    {
        $point = new Coordinate(51.5074, -0.1278);
        $this->assertEqualsWithDelta(0.0, Geo::distance($point, $point), 0.001);
    }

    public function test_bounding_box_creation(): void
    {
        $center = new Coordinate(40.7128, -74.0060);
        $box = Geo::boundingBox($center, 10.0);

        $this->assertInstanceOf(BoundingBox::class, $box);
        $this->assertLessThan($center->latitude, $box->minLat);
        $this->assertGreaterThan($center->latitude, $box->maxLat);
        $this->assertLessThan($center->longitude, $box->minLng);
        $this->assertGreaterThan($center->longitude, $box->maxLng);
    }

    public function test_bounding_box_contains_center(): void
    {
        $center = new Coordinate(40.7128, -74.0060);
        $box = Geo::boundingBox($center, 10.0);

        $this->assertTrue($box->contains($center));
    }

    public function test_bounding_box_excludes_far_point(): void
    {
        $center = new Coordinate(40.7128, -74.0060);
        $box = Geo::boundingBox($center, 10.0);

        $farPoint = new Coordinate(33.9425, -118.4081);
        $this->assertFalse($box->contains($farPoint));
    }

    public function test_point_in_polygon(): void
    {
        // Simple square polygon around Central Park, NYC
        $polygon = [
            new Coordinate(40.800, -73.970),
            new Coordinate(40.800, -73.950),
            new Coordinate(40.760, -73.950),
            new Coordinate(40.760, -73.970),
        ];

        $inside = new Coordinate(40.780, -73.960);
        $outside = new Coordinate(40.700, -73.960);

        $this->assertTrue(Geo::contains($polygon, $inside));
        $this->assertFalse(Geo::contains($polygon, $outside));
    }

    public function test_bearing_north(): void
    {
        $a = new Coordinate(0.0, 0.0);
        $b = new Coordinate(1.0, 0.0);

        $bearing = Geo::bearing($a, $b);
        $this->assertEqualsWithDelta(0.0, $bearing, 1.0);
    }

    public function test_bearing_east(): void
    {
        $a = new Coordinate(0.0, 0.0);
        $b = new Coordinate(0.0, 1.0);

        $bearing = Geo::bearing($a, $b);
        $this->assertEqualsWithDelta(90.0, $bearing, 1.0);
    }

    public function test_midpoint(): void
    {
        $a = new Coordinate(0.0, 0.0);
        $b = new Coordinate(0.0, 10.0);

        $mid = Geo::midpoint($a, $b);
        $this->assertEqualsWithDelta(0.0, $mid->latitude, 0.01);
        $this->assertEqualsWithDelta(5.0, $mid->longitude, 0.01);
    }

    public function test_destination(): void
    {
        $start = new Coordinate(0.0, 0.0);

        // Travel 111.195 km north (approximately 1 degree of latitude)
        $dest = Geo::destination($start, 0.0, 111.195);
        $this->assertEqualsWithDelta(1.0, $dest->latitude, 0.01);
        $this->assertEqualsWithDelta(0.0, $dest->longitude, 0.01);
    }

    public function test_compass_direction_cardinal_bearings(): void
    {
        $this->assertSame('N', Geo::compassDirection(0.0));
        $this->assertSame('NE', Geo::compassDirection(45.0));
        $this->assertSame('E', Geo::compassDirection(90.0));
        $this->assertSame('SE', Geo::compassDirection(135.0));
        $this->assertSame('S', Geo::compassDirection(180.0));
        $this->assertSame('SW', Geo::compassDirection(225.0));
        $this->assertSame('W', Geo::compassDirection(270.0));
        $this->assertSame('NW', Geo::compassDirection(315.0));
    }

    public function test_compass_direction_boundary_values(): void
    {
        $this->assertSame('N', Geo::compassDirection(22.4));
        $this->assertSame('NE', Geo::compassDirection(22.5));
        $this->assertSame('NE', Geo::compassDirection(67.4));
        $this->assertSame('E', Geo::compassDirection(67.5));
        $this->assertSame('E', Geo::compassDirection(112.4));
        $this->assertSame('SE', Geo::compassDirection(112.5));
        $this->assertSame('SE', Geo::compassDirection(157.4));
        $this->assertSame('S', Geo::compassDirection(157.5));
        $this->assertSame('S', Geo::compassDirection(202.4));
        $this->assertSame('SW', Geo::compassDirection(202.5));
        $this->assertSame('SW', Geo::compassDirection(247.4));
        $this->assertSame('W', Geo::compassDirection(247.5));
        $this->assertSame('W', Geo::compassDirection(292.4));
        $this->assertSame('NW', Geo::compassDirection(292.5));
        $this->assertSame('NW', Geo::compassDirection(337.4));
        $this->assertSame('N', Geo::compassDirection(337.5));
    }

    public function test_compass_direction_full_circle(): void
    {
        $this->assertSame('N', Geo::compassDirection(360.0));
    }

    public function test_with_elevation_and_get_elevation(): void
    {
        $coord = new Coordinate(40.7128, -74.0060);
        $this->assertNull($coord->getElevation());

        $elevated = $coord->withElevation(100.5);
        $this->assertSame(100.5, $elevated->getElevation());
        $this->assertSame(40.7128, $elevated->latitude);
        $this->assertSame(-74.006, $elevated->longitude);

        // Original remains unchanged
        $this->assertNull($coord->getElevation());
    }

    public function test_with_elevation_negative(): void
    {
        $coord = new Coordinate(0.0, 0.0);
        $below = $coord->withElevation(-50.0);
        $this->assertSame(-50.0, $below->getElevation());
    }

    public function test_area_square_polygon(): void
    {
        // A roughly 1-degree square at the equator (~111 km x 111 km ≈ 12321 km²)
        $polygon = [
            new Coordinate(0.0, 0.0),
            new Coordinate(0.0, 1.0),
            new Coordinate(1.0, 1.0),
            new Coordinate(1.0, 0.0),
        ];

        $area = Geo::area($polygon);

        // Expected ~12,308 km² = ~1.2308e10 m²
        $this->assertEqualsWithDelta(1.2308e10, $area, 1.0e9);
    }

    public function test_area_triangle(): void
    {
        $polygon = [
            new Coordinate(0.0, 0.0),
            new Coordinate(0.0, 1.0),
            new Coordinate(1.0, 0.0),
        ];

        $area = Geo::area($polygon);

        // Triangle is half the square, ~6154 km² = ~6.154e9 m²
        $this->assertEqualsWithDelta(6.154e9, $area, 5.0e8);
    }

    public function test_area_insufficient_points(): void
    {
        $this->assertSame(0.0, Geo::area([]));
        $this->assertSame(0.0, Geo::area([new Coordinate(0.0, 0.0)]));
        $this->assertSame(0.0, Geo::area([new Coordinate(0.0, 0.0), new Coordinate(1.0, 1.0)]));
    }

    public function test_units_enum_earth_radius(): void
    {
        $this->assertSame(6371.0, Units::Kilometers->earthRadius());
        $this->assertSame(3958.8, Units::Miles->earthRadius());
        $this->assertSame(6_371_000.0, Units::Meters->earthRadius());
        $this->assertSame(3440.065, Units::NauticalMiles->earthRadius());
    }

    public function test_encode_geohash_known_coordinate(): void
    {
        $coord = new Coordinate(57.64911, 10.40744);
        $hash = Geo::encodeGeohash($coord, 11);

        $this->assertSame('u4pruydqqvj', $hash);
    }

    public function test_decode_geohash_back_to_coordinate(): void
    {
        $hash = 'u4pruydqqvj';
        $decoded = Geo::decodeGeohash($hash);

        $this->assertEqualsWithDelta(57.64911, $decoded->latitude, 0.001);
        $this->assertEqualsWithDelta(10.40744, $decoded->longitude, 0.001);
    }

    public function test_geohash_bounds_returns_bounding_box(): void
    {
        $bounds = Geo::geohashBounds('u4pruydqqvj');

        $this->assertInstanceOf(BoundingBox::class, $bounds);
        $this->assertLessThan($bounds->maxLat, $bounds->minLat);
        $this->assertLessThan($bounds->maxLng, $bounds->minLng);
        $this->assertTrue($bounds->contains(new Coordinate(57.64911, 10.40744)));
    }

    public function test_geohash_roundtrip(): void
    {
        $original = new Coordinate(48.8566, 2.3522);
        $hash = Geo::encodeGeohash($original, 12);
        $decoded = Geo::decodeGeohash($hash);

        $this->assertEqualsWithDelta($original->latitude, $decoded->latitude, 0.001);
        $this->assertEqualsWithDelta($original->longitude, $decoded->longitude, 0.001);
    }

    public function test_encode_polyline_known_path(): void
    {
        $coordinates = [
            new Coordinate(38.5, -120.2),
            new Coordinate(40.7, -120.95),
            new Coordinate(43.252, -126.453),
        ];

        $encoded = Geo::encodePolyline($coordinates);

        $this->assertSame('_p~iF~ps|U_ulLnnqC_mqNvxq`@', $encoded);
    }

    public function test_decode_polyline_known_string(): void
    {
        $decoded = Geo::decodePolyline('_p~iF~ps|U_ulLnnqC_mqNvxq`@');

        $this->assertCount(3, $decoded);
        $this->assertEqualsWithDelta(38.5, $decoded[0]->latitude, 0.001);
        $this->assertEqualsWithDelta(-120.2, $decoded[0]->longitude, 0.001);
        $this->assertEqualsWithDelta(40.7, $decoded[1]->latitude, 0.001);
        $this->assertEqualsWithDelta(-120.95, $decoded[1]->longitude, 0.001);
        $this->assertEqualsWithDelta(43.252, $decoded[2]->latitude, 0.001);
        $this->assertEqualsWithDelta(-126.453, $decoded[2]->longitude, 0.001);
    }

    public function test_polyline_roundtrip(): void
    {
        $original = [
            new Coordinate(51.5074, -0.1278),
            new Coordinate(48.8566, 2.3522),
            new Coordinate(40.4168, -3.7038),
        ];

        $encoded = Geo::encodePolyline($original);
        $decoded = Geo::decodePolyline($encoded);

        $this->assertCount(3, $decoded);
        for ($i = 0; $i < 3; $i++) {
            $this->assertEqualsWithDelta($original[$i]->latitude, $decoded[$i]->latitude, 0.001);
            $this->assertEqualsWithDelta($original[$i]->longitude, $decoded[$i]->longitude, 0.001);
        }
    }

    public function test_route_distance_sum_of_segments(): void
    {
        $nyc = new Coordinate(40.7128, -74.0060);
        $chicago = new Coordinate(41.8781, -87.6298);
        $la = new Coordinate(33.9425, -118.4081);

        $route = [$nyc, $chicago, $la];
        $total = Geo::routeDistance($route);

        $segmentA = Geo::distance($nyc, $chicago);
        $segmentB = Geo::distance($chicago, $la);

        $this->assertEqualsWithDelta($segmentA + $segmentB, $total, 0.01);
    }

    public function test_route_distance_in_miles(): void
    {
        $a = new Coordinate(40.7128, -74.0060);
        $b = new Coordinate(41.8781, -87.6298);
        $c = new Coordinate(33.9425, -118.4081);

        $totalMiles = Geo::routeDistance([$a, $b, $c], Units::Miles);
        $totalKm = Geo::routeDistance([$a, $b, $c]);

        // Miles should be less than km
        $this->assertLessThan($totalKm, $totalMiles);
        $this->assertGreaterThan(0.0, $totalMiles);
    }

    public function test_route_distance_single_point(): void
    {
        $point = new Coordinate(40.7128, -74.0060);
        $this->assertSame(0.0, Geo::routeDistance([$point]));
    }

    public function test_route_distance_empty_array(): void
    {
        $this->assertSame(0.0, Geo::routeDistance([]));
    }
}
