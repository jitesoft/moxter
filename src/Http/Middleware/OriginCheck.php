<?php
namespace Jitesoft\Moxter\Http\Middleware;

use Jitesoft\Exceptions\Http\Client\HttpUnauthorizedException;
use Jitesoft\Moxter\Contracts\ConfigInterface;
use Jitesoft\Router\Contracts\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;

class OriginCheck implements MiddlewareInterface, LoggerAwareInterface {

    protected $isDevelopment;
    protected $allowedDomains;
    protected $logger;

    public function __construct(ConfigInterface $config, LoggerInterface $logger) {
        $this->isDevelopment  = $config->get('APP_ENV', 'production') == 'development';
        $this->allowedDomains = $config->get('DOMAINS');
        $this->logger         = $logger;
    }

    /**
     * Handle request.
     * Return result of $next.
     *
     * @param ServerRequestInterface|RequestInterface $request
     * @param callable $next
     * @return JsonResponse
     * @throws HttpUnauthorizedException
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface {
        if ($this->isDevelopment) {
            $this->logger->debug('Is development, ignoring origin constraints.');
            header('Access-Control-Allow-Origin: *');
            return $next($request);
        }

        $this->logger->info('Currently running in production, using the origin constraints.');
        $origin = $request->getServerParams()['HTTP_ORIGIN'];
        $result = preg_match($this->allowedDomains, $origin);
        if (!$result) {
            $this->logger->error('Request was canceled due to invalid origin.');
            throw new HttpUnauthorizedException();
        }

        $this->logger->debug('Request was accepted. Valid origin. Setting CORS header.');
        header('Access-Control-Allow-Origin: *'); // Force header.
        /** @var JsonResponse $result */
        $result = $next($request);
        return $result->withHeader('Access-Control-Allow-Origin', $origin);
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

}
