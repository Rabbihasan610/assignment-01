<?php

if (!function_exists('haversine_distance')) {
    function haversine_distance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($dLon / 2)
            * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 3);
    }
}


if (!function_exists('is_within_delivery_geofence')) {
    function is_within_delivery_geofence(
        float $targetLat,
        float $targetLng,
        float $actualLat,
        float $actualLng,
        float $thresholdKm = 0.100
    ): bool {
        $distance = haversine_distance(
            $targetLat,
            $targetLng,
            $actualLat,
            $actualLng
        );

        return $distance <= $thresholdKm;
    }
}
