<?php
use Jitesoft\Exceptions\JitesoftException;
use Jitesoft\Exceptions\Http\HttpException;
use Jitesoft\Moxter\Kernel;

require '../vendor/autoload.php';

(new Dotenv\Dotenv('../'))->load();

$output = [];
$status = 200;

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
} catch (JitesoftException $ex) {
    $code = 400;
    if ($ex instanceof HttpException) {
        $status = $ex->getCode();
    }
    $output = [
        'error' => $ex->getMessage()
    ];
    if (getenv('DEBUG') == true) {
        $output['exception'] = $ex->toArray();
    }

} catch (Exception $ex) {
    $status = 500;
    $output = [
        'error' => 'Internal server error.'
    ];

    if (getenv('DEBUG') == true) {
        $output['exception'] = $ex->getMessage();
    }

} finally {
    http_response_code($status);
    die(json_encode($output, JSON_PRETTY_PRINT));
}
