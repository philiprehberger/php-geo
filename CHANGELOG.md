# Changelog

All notable changes to `php-geo` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.1] - 2026-03-31

### Changed
- Standardize README to 3-badge format with emoji Support section
- Update CI checkout action to v5 for Node.js 24 compatibility

## [1.2.0] - 2026-03-27

### Added
- Geohash encoding and decoding via `Geo::encodeGeohash()` and `Geo::decodeGeohash()`
- Google Encoded Polyline support via `Geo::encodePolyline()` and `Geo::decodePolyline()`
- `Geo::routeDistance()` for calculating total distance along a multi-point route

## [1.1.2] - 2026-03-23

### Changed
- Standardize README requirements format per template guide

## [1.1.1] - 2026-03-23

### Fixed
- Remove decorative dividers from README for template compliance

## [1.1.0] - 2026-03-22

### Added
- `compassDirection()` method to convert bearing to cardinal/intercardinal direction
- `withElevation()` and `getElevation()` for optional elevation support on Coordinate
- `area()` method to calculate polygon area in square meters using the Shoelace formula

## [1.0.3] - 2026-03-17

### Changed
- Standardized package metadata, README structure, and CI workflow per package guide

## [1.0.2] - 2026-03-16

### Changed
- Standardize composer.json: add type, homepage, scripts

## [1.0.1] - 2026-03-15

### Changed
- Standardize README badges

## [1.0.0] - 2026-03-15

### Added
- Initial release
- `Coordinate` immutable value object with validation, JSON serialization, and Stringable support
- `Geo::distance()` using Haversine formula
- `Geo::distanceVincenty()` using Vincenty formula
- `Geo::boundingBox()` for calculating bounding boxes around a center point
- `Geo::contains()` for point-in-polygon detection using ray casting
- `Geo::bearing()` for initial bearing calculation
- `Geo::midpoint()` for geographic midpoint calculation
- `Geo::destination()` for destination point given bearing and distance
- `BoundingBox` value object with containment check
- `Units` enum with Kilometers, Miles, Meters, and NauticalMiles
