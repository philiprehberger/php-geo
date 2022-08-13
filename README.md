# PHP Geo

[![Tests](https://github.com/philiprehberger/php-geo/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-geo/actions/workflows/tests.yml)
[![Packagist Version](https://img.shields.io/packagist/v/philiprehberger/php-geo.svg)](https://packagist.org/packages/philiprehberger/php-geo)
[![GitHub Release](https://img.shields.io/github/v/release/philiprehberger/php-geo)](https://github.com/philiprehberger/php-geo/releases)
[![Last Updated](https://img.shields.io/github/last-commit/philiprehberger/php-geo)](https://github.com/philiprehberger/php-geo/commits/main)
[![License](https://img.shields.io/github/license/philiprehberger/php-geo)](LICENSE)
[![Bug Reports](https://img.shields.io/github/issues/philiprehberger/php-geo/bug)](https://github.com/philiprehberger/php-geo/issues?q=is%3Aissue+is%3Aopen+label%3Abug)
[![Feature Requests](https://img.shields.io/github/issues/philiprehberger/php-geo/enhancement)](https://github.com/philiprehberger/php-geo/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)
[![Sponsor](https://img.shields.io/badge/sponsor-GitHub%20Sponsors-ec6cb9)](https://github.com/sponsors/philiprehberger)

Geospatial utilities for distance, bounding box, and point-in-polygon calculations.

## Requirements

- PHP 8.2+

## Installation

```bash
composer require philiprehberger/php-geo
```

## Usage

### Distance Calculation (Haversine)

```php
use PhilipRehberger\Geo\Coordinate;
use PhilipRehberger\Geo\Geo;

$nyc = new Coordinate(40.7128, -74.0060);
$la = new Coordinate(33.9425, -118.4081);

$km = Geo::distance($nyc, $la);          // ~3944 km
$miles = Geo::distance($nyc, $la, 'mi'); // ~2451 mi
```

### Vincenty Distance

```php
$km = Geo::distanceVincenty($nyc, $la); // More accurate for long distances
```

### Bounding Box

```php
$center = new Coordinate(40.7128, -74.0060);
$box = Geo::boundingBox($center, 10.0); // 10 km radius

$box->contains($center); // true
$box->toArray();          // ['min_lat' => ..., 'max_lat' => ..., 'min_lng' => ..., 'max_lng' => ...]
```

### Point in Polygon

```php
$polygon = [
    new Coordinate(40.800, -73.970),
    new Coordinate(40.800, -73.950),
    new Coordinate(40.760, -73.950),
    new Coordinate(40.760, -73.970),
];

$point = new Coordinate(40.780, -73.960);
Geo::contains($polygon, $point); // true
```

### Bearing

```php
$bearing = Geo::bearing($nyc, $la); // Initial bearing in degrees (0-360)
```

### Midpoint

```php
$mid = Geo::midpoint($nyc, $la); // Geographic midpoint
```

### Destination

```php
// Travel 100 km north from the equator
$start = new Coordinate(0.0, 0.0);
$dest = Geo::destination($start, 0.0, 100.0); // bearing=0 (north), 100 km
```

### Compass Direction

```php
$bearing = Geo::bearing($nyc, $la);
$direction = Geo::compassDirection($bearing); // e.g. 'SW'

Geo::compassDirection(0.0);   // 'N'
Geo::compassDirection(90.0);  // 'E'
Geo::compassDirection(225.0); // 'SW'
```

### Elevation

```php
$coord = new Coordinate(40.7128, -74.0060);
$elevated = $coord->withElevation(100.5); // New Coordinate with elevation
$elevated->getElevation(); // 100.5
$coord->getElevation();    // null (original unchanged)
```

### Polygon Area

```php
$polygon = [
    new Coordinate(0.0, 0.0),
    new Coordinate(0.0, 1.0),
    new Coordinate(1.0, 1.0),
    new Coordinate(1.0, 0.0),
];

$area = Geo::area($polygon); // Area in square meters
```

### Geohash Encoding/Decoding

```php
$coord = new Coordinate(57.64911, 10.40744);
$hash = Geo::encodeGeohash($coord);          // 'u4pruydqqvj8'
$decoded = Geo::decodeGeohash($hash);        // Coordinate near original
$bounds = Geo::geohashBounds($hash);         // BoundingBox of geohash cell
```

### Polyline Encoding/Decoding

```php
$coordinates = [
    new Coordinate(38.5, -120.2),
    new Coordinate(40.7, -120.95),
    new Coordinate(43.252, -126.453),
];

$encoded = Geo::encodePolyline($coordinates);  // '_p~iF~ps|U_ulLnnqC_mqNvxq`@'
$decoded = Geo::decodePolyline($encoded);      // Array of Coordinate objects
```

### Route Distance

```php
$route = [
    new Coordinate(40.7128, -74.0060),
    new Coordinate(41.8781, -87.6298),
    new Coordinate(33.9425, -118.4081),
];

$total = Geo::routeDistance($route); // Total distance in km
```

### Units

Supported units: `km` (kilometers), `mi` (miles), `m` (meters), `nmi` (nautical miles).

```php
Geo::distance($a, $b, 'mi');   // miles
Geo::distance($a, $b, 'm');    // meters
Geo::distance($a, $b, 'nmi');  // nautical miles
```

## API

| Method | Description |
|--------|-------------|
| `Geo::distance($a, $b, $unit)` | Haversine distance between two coordinates |
| `Geo::distanceVincenty($a, $b, $unit)` | Vincenty distance between two coordinates |
| `Geo::boundingBox($center, $radius, $unit)` | Bounding box around a center point |
| `Geo::contains($polygon, $point)` | Point-in-polygon test (ray casting) |
| `Geo::bearing($from, $to)` | Initial bearing in degrees |
| `Geo::midpoint($a, $b)` | Geographic midpoint |
| `Geo::destination($start, $bearing, $distance, $unit)` | Destination from start given bearing and distance |
| `Geo::compassDirection($bearing)` | Convert bearing to cardinal/intercardinal direction |
| `Geo::area($polygon)` | Polygon area in square meters (Shoelace formula) |
| `Geo::encodeGeohash($coord, $precision)` | Encode coordinate to geohash string |
| `Geo::decodeGeohash($hash)` | Decode geohash to coordinate (center of cell) |
| `Geo::geohashBounds($hash)` | Get bounding box of a geohash cell |
| `Geo::encodePolyline($coordinates)` | Encode coordinates to Google polyline string |
| `Geo::decodePolyline($encoded)` | Decode Google polyline string to coordinates |
| `Geo::routeDistance($coordinates, $unit)` | Total distance along a multi-point route |
| `Coordinate::withElevation($meters)` | Return new Coordinate with elevation |
| `Coordinate::getElevation()` | Get elevation in meters or null |

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
```

## Support

[![LinkedIn](https://img.shields.io/badge/LinkedIn-Connect-0A66C2)](https://www.linkedin.com/in/philiprehberger)
[![All Packages](https://img.shields.io/badge/Packages-View%20All-blue)](https://github.com/philiprehberger/packages)

## License

[MIT](LICENSE)
