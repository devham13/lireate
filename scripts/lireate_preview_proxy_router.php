<?php
/**
 * PHP built-in server router: local service pages + proxy /images and /_shared from live.
 * Usage: php -S 127.0.0.1:8888 -t .dev-docroot scripts/lireate_preview_proxy_router.php
 */
$uri = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
if ($uri === false || $uri === '') {
    $uri = '/';
}

$docroot = getenv('LIREATE_PREVIEW_DOCROOT');
if ($docroot === false || $docroot === '') {
    $docroot = dirname(__DIR__) . '/.dev-docroot';
}
$docroot = rtrim($docroot, '/\\');

$live = getenv('LIREATE_PUBLIC_SITE_URL');
$live = $live !== false ? rtrim((string) $live, '/') : '';

if ($live !== '' && preg_match('#^/(images/|_shared/)#', $uri)) {
    $qs = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== ''
        ? ('?' . $_SERVER['QUERY_STRING'])
        : '';
    $url = $live . $uri . $qs;
    $ctx = stream_context_create(
        array(
            'http' => array(
                'method' => 'GET',
                'timeout' => 20,
                'ignore_errors' => true,
                'header' => "User-Agent: lireate-preview-proxy/1.0\r\n",
            ),
        )
    );
    $body = @file_get_contents($url, false, $ctx);
    if ($body !== false && isset($http_response_header[0])) {
        if (preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $code = (int) $m[1];
            if ($code >= 200 && $code < 400) {
                $ctype = 'application/octet-stream';
                foreach ($http_response_header as $h) {
                    if (stripos($h, 'Content-Type:') === 0) {
                        $ctype = trim(substr($h, 13));
                        break;
                    }
                }
                header('Content-Type: ' . $ctype);
                header('Cache-Control: public, max-age=300');
                echo $body;
                return true;
            }
        }
    }
    header('HTTP/1.1 502 Bad Gateway');
    echo 'Preview proxy failed for ' . htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');
    return true;
}

$file = $docroot . $uri;
if (is_dir($file)) {
    $index = rtrim($file, '/\\') . '/index.php';
    if (is_file($index)) {
        $_SERVER['SCRIPT_FILENAME'] = $index;
        require $index;
        return true;
    }
}
if (is_file($file)) {
  return false;
}

if (preg_match('#\.php$#', $uri) && is_file($docroot . $uri)) {
    require $docroot . $uri;
    return true;
}

header('HTTP/1.1 404 Not Found');
echo 'Not found';
return true;
