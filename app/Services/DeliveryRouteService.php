<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DeliveryRouteService
{
    private const ROUTES_API_URL = 'https://routes.googleapis.com/directions/v2:computeRoutes';

    private const MAX_STOPS = 10;

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) config('services.google_maps.key');
    }

    public function optimizeRoute(
        float $originLat,
        float $originLng,
        array $stops
    ): array {
        $this->ensureApiKey();
        $this->validateCoordinates($originLat, $originLng);

        if (count($stops) === 0) {
            throw new RuntimeException('No active delivery stops found.');
        }

        if (count($stops) > self::MAX_STOPS) {
            throw new RuntimeException(
                'Maximum ' . self::MAX_STOPS .
                    ' delivery stops are allowed per calculation.'
            );
        }

        foreach ($stops as $stop) {
            if (
                !isset($stop['latitude'], $stop['longitude']) ||
                !is_numeric($stop['latitude']) ||
                !is_numeric($stop['longitude'])
            ) {
                throw new RuntimeException(
                    'A delivery stop has missing coordinates.'
                );
            }

            $this->validateCoordinates(
                (float) $stop['latitude'],
                (float) $stop['longitude']
            );
        }

        $bestRoute = null;


        foreach ($stops as $destinationIndex => $destinationStop) {
            $intermediateStops = [];

            foreach ($stops as $index => $stop) {
                if ($index !== $destinationIndex) {
                    $intermediateStops[] = [
                        'original_index' => $index,
                        'waypoint' => $this->waypoint(
                            (float) $stop['latitude'],
                            (float) $stop['longitude']
                        ),
                    ];
                }
            }

            $requestBody = [
                'origin' => $this->waypoint($originLat, $originLng),
                'destination' => $this->waypoint(
                    (float) $destinationStop['latitude'],
                    (float) $destinationStop['longitude']
                ),
                'intermediates' => array_values(array_map(
                    fn(array $item) => $item['waypoint'],
                    $intermediateStops
                )),
                'travelMode' => 'DRIVE',
                'routingPreference' => 'TRAFFIC_AWARE',
                'optimizeWaypointOrder' => count($intermediateStops) > 1,
                'computeAlternativeRoutes' => false,
                'languageCode' => 'en',
                'units' => 'METRIC',
            ];

            $route = $this->requestGoogleRoute($requestBody);

            if (!$route) {
                continue;
            }

            $optimizedIndexes = $route['optimizedIntermediateWaypointIndices'] ?? array_keys($intermediateStops);

            $orderedStops = [];

            foreach ($optimizedIndexes as $optimizedIndex) {
                if (!isset($intermediateStops[$optimizedIndex])) {
                    continue;
                }

                $originalIndex = $intermediateStops[$optimizedIndex]['original_index'];

                $orderedStops[] = $stops[$originalIndex];
            }

            $orderedStops[] = $destinationStop;

            $durationSeconds = $this->durationToSeconds(
                $route['duration'] ?? '0s'
            );

            if (
                $bestRoute === null ||
                $durationSeconds < $bestRoute['duration_seconds']
            ) {
                $bestRoute = [
                    'duration_seconds' => $durationSeconds,
                    'distance_meters' => (int) (
                        $route['distanceMeters'] ?? 0
                    ),
                    'polyline' => $route['polyline']['encodedPolyline'] ?? '',
                    'stops' => array_values($orderedStops),
                    'legs' => $this->formatLegs(
                        $route['legs'] ?? [],
                        $orderedStops
                    ),
                ];
            }
        }

        if ($bestRoute === null) {
            throw new RuntimeException(
                'Google Maps could not calculate a route for these locations.'
            );
        }

        $bestRoute['straight_line_distance_km'] =
            $this->calculateStraightLineDistance(
                $originLat,
                $originLng,
                $bestRoute['stops']
            );

        $bestRoute['alternatives'] = $this->getAlternativeRoutes(
            $originLat,
            $originLng,
            $bestRoute['stops'][0]
        );

        return $bestRoute;
    }


    public function optimizeRouteLocally(
        float $originLat,
        float $originLng,
        array $stops
    ): array {
        $remaining = array_values($stops);
        $orderedStops = [];
        $legs = [];

        $currentLat = $originLat;
        $currentLng = $originLng;
        $totalDistanceKm = 0;

        while (!empty($remaining)) {
            $nearestIndex = null;
            $nearestDistance = INF;

            foreach ($remaining as $index => $stop) {
                $distance = haversine_distance(
                    $currentLat,
                    $currentLng,
                    (float) $stop['latitude'],
                    (float) $stop['longitude']
                );

                if ($distance < $nearestDistance) {
                    $nearestDistance = $distance;
                    $nearestIndex = $index;
                }
            }

            $stop = $remaining[$nearestIndex];

            $distanceMeters = (int) round($nearestDistance * 1000);

            $durationSeconds = (int) round(
                ($nearestDistance / 20) * 3600
            );

            $legs[] = [
                'order_id' => $stop['order_id'],
                'name' => $stop['name'] ?? 'Customer',
                'duration_seconds' => $durationSeconds,
                'distance_meters' => $distanceMeters,
            ];

            $totalDistanceKm += $nearestDistance;
            $orderedStops[] = $stop;

            $currentLat = (float) $stop['latitude'];
            $currentLng = (float) $stop['longitude'];

            array_splice($remaining, $nearestIndex, 1);
        }

        $totalDurationSeconds = (int) round(
            ($totalDistanceKm / 20) * 3600
        );

        return [
            'route_source' => 'local_fallback',
            'estimated' => true,
            'duration_seconds' => $totalDurationSeconds,
            'distance_meters' => (int) round($totalDistanceKm * 1000),
            'polyline' => null,
            'stops' => $orderedStops,
            'legs' => $legs,
            'straight_line_distance_km' => round($totalDistanceKm, 3),
            'alternatives' => [],
        ];
    }



    private function getAlternativeRoutes(
        float $originLat,
        float $originLng,
        array $firstStop
    ): array {
        $body = [
            'origin' => $this->waypoint($originLat, $originLng),
            'destination' => $this->waypoint(
                (float) $firstStop['latitude'],
                (float) $firstStop['longitude']
            ),
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_AWARE',
            'computeAlternativeRoutes' => true,
            'languageCode' => 'en',
            'units' => 'METRIC',
        ];

        $response = $this->sendRequest($body, [
            'routes.duration',
            'routes.distanceMeters',
            'routes.polyline.encodedPolyline',
            'routes.routeLabels',
        ]);

        if (!$response->successful()) {
            Log::warning('Google alternative routes unavailable.', [
                'http_status' => $response->status(),
            ]);

            return [];
        }

        return array_map(
            fn(array $route) => [
                'label' => $route['routeLabels'][0]
                    ?? 'Alternative route',
                'duration_seconds' => $this->durationToSeconds(
                    $route['duration'] ?? '0s'
                ),
                'distance_meters' => (int) (
                    $route['distanceMeters'] ?? 0
                ),
                'polyline' => $route['polyline']['encodedPolyline'] ?? '',
            ],
            $response->json('routes', [])
        );
    }


    private function requestGoogleRoute(array $body): ?array
    {
        $response = $this->sendRequest($body, [
            'routes.duration',
            'routes.distanceMeters',
            'routes.polyline.encodedPolyline',
            'routes.legs.duration',
            'routes.legs.distanceMeters',
            'routes.optimizedIntermediateWaypointIndices',
        ]);

        if (!$response->successful()) {
            Log::error('Google Routes API request failed.', [
                'http_status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new RuntimeException(
                'Google Routes API request failed. Check API enablement, billing and key restrictions.'
            );
        }

        return $response->json('routes.0');
    }

  
    private function sendRequest(
        array $body,
        array $fieldMask
    ) {
        return Http::timeout(25)
            ->withHeaders([
                'X-Goog-Api-Key' => $this->apiKey,
                'X-Goog-FieldMask' => implode(',', $fieldMask),
            ])
            ->post(self::ROUTES_API_URL, $body);
    }


    private function formatLegs(
        array $legs,
        array $orderedStops
    ): array {
        $result = [];

        foreach ($legs as $index => $leg) {
            $stop = $orderedStops[$index] ?? [];

            $result[] = [
                'order_id' => $stop['order_id'] ?? null,
                'name' => $stop['name'] ?? null,
                'duration_seconds' => $this->durationToSeconds(
                    $leg['duration'] ?? '0s'
                ),
                'distance_meters' => (int) (
                    $leg['distanceMeters'] ?? 0
                ),
            ];
        }

        return $result;
    }


    private function calculateStraightLineDistance(
        float $originLat,
        float $originLng,
        array $stops
    ): float {
        $total = 0.0;
        $previousLat = $originLat;
        $previousLng = $originLng;

        foreach ($stops as $stop) {
            $lat = (float) $stop['latitude'];
            $lng = (float) $stop['longitude'];

            $total += haversine_distance(
                $previousLat,
                $previousLng,
                $lat,
                $lng
            );

            $previousLat = $lat;
            $previousLng = $lng;
        }

        return round($total, 3);
    }

    private function waypoint(float $latitude, float $longitude): array
    {
        return [
            'location' => [
                'latLng' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ],
            ],
        ];
    }

    private function validateCoordinates(
        float $latitude,
        float $longitude
    ): void {
        if (
            !is_finite($latitude) ||
            !is_finite($longitude) ||
            $latitude < -90 ||
            $latitude > 90 ||
            $longitude < -180 ||
            $longitude > 180
        ) {
            throw new RuntimeException(
                'Invalid latitude or longitude.'
            );
        }
    }

    private function ensureApiKey(): void
    {
        if (trim($this->apiKey) === '') {
            throw new RuntimeException(
                'Google Maps API key is not configured.'
            );
        }
    }

    private function durationToSeconds(string $duration): int
    {
        return (int) round((float) rtrim($duration, 's'));
    }
}
