# Changelog

All notable changes to `php-geo` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
