<?php
namespace Jitesoft\Moxter\Http\Middleware;

use Jitesoft\Exceptions\Http\Client\HttpUnauthorizedException;
use Jitesoft\Moxter\Contracts\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;

class OriginCheck implements MiddlewareInterface, LoggerAwareInterface {

    protected bool $isDevelopment;
    protected array $allowedDomains;
    protected LoggerInterface $logger;

    public function __construct(ConfigInterface $config,
                                LoggerInterface $logger) {
        $this->isDevelopment  = $config->get(
            'APP_ENV',
            'production'
        ) === 'development';
        $this->allowedDomains = explode(',', $config->get('DOMAINS'));
        $this->logger         = $logger;
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

    /**
     * @inheritDoc
     * @throws HttpUnauthorizedException
     */
    public function process(ServerRequestInterface $request,
                            RequestHandlerInterface $handler)
    : ResponseInterface {
        if ($this->isDevelopment) {
            $this->logger->debug(
                'Is development, ignoring origin constraints.'
            );
            return $handler->handle($request);
        }

        $this->logger->info(
            'Currently running in production, using the origin constraints.'
        );
        $origin = $request->getServerParams()['HTTP_ORIGIN'];
        $result = false;
        foreach ($this->allowedDomains as $domain) {
            $result = preg_match($domain, $origin);
            if ($result) {
                break;
            }
        }

        if (!$result) {
            $this->logger->error('Request was canceled due to invalid origin.');
            throw new HttpUnauthorizedException();
        }

        $this->logger->debug(
            'Request was accepted. Valid origin. Setting CORS header.'
        );
        $result = $handler->handle($request);
        return $result->withHeader('Access-Control-Allow-Origin', $origin);
    }

}
