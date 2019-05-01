<?php

// require Kint debugging library on local only (only include it if it exists)
if ( file_exists(__DIR__ . '/../../../../../lab/kint_init.php') ) {
    require_once(__DIR__ . '/../../../../../lab/kint_init.php');
}

/**
 * Generate an absoulte URL to a microservice URL
 */
function api_url($service, $path = '') {
    if ( file_exists(base_path('../config_map.php')) ) {
        $config_map = include(base_path('../config_map.php'));
        $host_parts = explode('.', $_SERVER['HTTP_HOST']);
        $host_count = count($host_parts);
        if ( $service == null && isset($host_parts[$host_count - 4]) ) {
            $service = $host_parts[$host_count - 4];
        }
        $base = $host_parts[$host_count - 2] . '.' . $host_parts[$host_count - 1];
        $url = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . $service . '.' . $config_map['server'] . '.' . $base . (preg_match('/^\//', $path) ? '' : '/') . $path;
        return $url;
    } else {
        die('No config map file found');
    }
}

/**
 * Return a standardized JSON response
 *
 * @param $data
 */
function json($data = [], $status_code = 200) {
    $defaults = [
        'message' => null,
        'data' => [],
        'links' => [],
        'meta' => [],
    ];
    $response = array_merge($defaults, $data);
    header('MS-Name: Microservice Name');
    header('MS-Version: 1.0');
    return response()->json($response, $status_code);
}