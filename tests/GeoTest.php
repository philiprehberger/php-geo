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

    public function test_units_enum_earth_radius(): void
    {
        $this->assertSame(6371.0, Units::Kilometers->earthRadius());
        $this->assertSame(3958.8, Units::Miles->earthRadius());
        $this->assertSame(6_371_000.0, Units::Meters->earthRadius());
        $this->assertSame(3440.065, Units::NauticalMiles->earthRadius());
    }
}
