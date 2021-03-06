<?php
use Jitesoft\Exceptions\JitesoftException;
use Jitesoft\Exceptions\Http\HttpException;
use Jitesoft\Moxter\Kernel;

require '../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Exception $ex) { 
    // Do nothing.
}

$output = [];
$status = 200;

header('Content-Type: application/json');
try {
    // Load all env vars.
    $kernel = new Kernel();

    $result = $kernel->handleRequest();
    foreach ($result->getHeaders() as $header => $values) {
        header(sprintf('%s: %s', $header, implode(',', $values)));
    }
    $result->getBody()->rewind();
    $output = json_decode($result->getBody());
    $status = $result->getStatusCode();
    // If getting here, the request is all good!
    header('Access-Control-Allow-Origin: *'); // Force header.
} catch (JitesoftException $ex) {
    $status = 400;
    if ($ex instanceof HttpException) {
        $status = $ex->getCode();
    }
    $output = [
        'error' => $ex->getMessage()
    ];
    if (isset($_ENV['DEBUG']) && boolval($_ENV['DEBUG']) === true) {
        $output['exception'] = $ex->toArray();
    }

} catch (Exception $ex) {
    $status = 500;
    $output = [
        'error' => 'Internal server error.'
    ];

    if (isset($_ENV['DEBUG']) && boolval($_ENV['DEBUG']) === true) {
        $output['exception'] = $ex->getMessage();
    }

} finally {
    http_response_code($status);
    die(json_encode($output, JSON_PRETTY_PRINT));
}
