<?php

// require Kint debugging library on local only (only include it if it exists)
$global_file = env('CONFIG_MAP');
$path = pathinfo(base_path($global_file));
if (file_exists($path['dirname'] . '/../lab/kint_init.php')) {
    require_once($path['dirname'] . '/../lab/kint_init.php');
}

/**
 * Return an array from our config map file
 */
function get_config_map($org_id = null) {
    $global_file = env('CONFIG_MAP');
    if (!file_exists(base_path($global_file))) {
        die('No global config map found');
    }
    $config_map = include(base_path($global_file));
    if ($org_id != null) {
        $cached_data = \DklCreations\SHCommon\Helpers\Identity::getOrgConfig($org_id);
        if (!empty($cached_data['db_username'])) {
            foreach ($cached_data['db_databases'] as $service => $db_name) {
                $config_map['db_credentials'][$service] = [
                    'DB_DATABASE' => $db_name,
                    'DB_USERNAME' => $cached_data['db_username'],
                    'DB_PASSWORD' => $cached_data['db_password'],
                ];
            }
        }
    }
    return $config_map;
}

/**
 * Get our db credentials from our config map
 */
function get_db_creds($service, $org_id = '') {
    $config_map = get_config_map($org_id);
    if ( isset($config_map['db_credentials'][$service]) ) {
        $service_creds = $config_map['db_credentials'][$service];
    }
    return isset($service_creds) ? $service_creds : null;
}

/**
 * Generate an absoulte URL to a microservice URL
 */
function api_url($service, $path = '') {
    $config_map = get_config_map();
    if (isset($_SERVER['HTTP_HOST'])) {
        $host_parts = explode('.', $_SERVER['HTTP_HOST']);
        $host_count = count($host_parts);
        if ( $service == null && isset($host_parts[$host_count - 4]) ) {
            $service = $host_parts[$host_count - 4];
        }
        $base = $host_parts[$host_count - 2] . '.' . $host_parts[$host_count - 1];
        $url = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . $service . '.' . $config_map['server'] . '.' . $base . (preg_match('/^\//', $path) ? '' : '/') . $path;
    } else {
        $url = (env('APP_ENV') == 'production' ? 'https' : 'http') . '://' . $service . '.' . $config_map['server'] . '.' . $config_map['domain'] . (preg_match('/^\//', $path) ? '' : '/') . $path;
    }
    return $url;
}

/**
 * Get our app data if it exists
 */
function data($key) {
    $data = null;
    try {
        $data = app($key);
    } catch (\Exception $e) {
        // do nothing
    }
    return $data;
}

/**
 * Abort with custom message and status code
 */
function fail($message, $status_code = 403) {
    abort($status_code, $message);
}

/**
 * Get our current auth token value
 */
function get_current_token() {
    $request = \Illuminate\Http\Request::capture();
    $token = preg_replace('/^Token\s/', '', $request->header('authorization'));
    return $token;
}

/**
 * Check if current user has permission for key
 */
function has_permission($key) {
    if (isset(data('user')['super_admin_enabled']) && data('user')['super_admin_enabled']) {
        return true;
    }
    $permissions = data('permissions');
    return is_array($permissions) && array_key_exists($key, $permissions) ? true : false;
}

/**
 * Convert our permissions collection into a flat array for cache
 */
function prepare_cache_permissions($permissions) {
    $perms = [];
    foreach ($permissions as $perm) {
        $perms[$perm['key'] . '|' . $perm['module']] = $perm['value'];
    }
    return $perms;
}