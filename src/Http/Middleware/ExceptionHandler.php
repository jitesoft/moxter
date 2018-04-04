<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ExceptionHandler.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Http\Middleware;

use Exception;
use Jitesoft\Exceptions\Http\HttpException;
use Jitesoft\Exceptions\Validation\ValidationException;
use Jitesoft\Router\Contracts\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * ExceptionHandler
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ExceptionHandler implements MiddlewareInterface, LoggerAwareInterface {

    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @param RequestInterface $request
     * @param callable $next
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface {
        try {
            return $next($request);
        } catch (ValidationException $ex) {
            $this->logger->notice($ex->getMessage());
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        } catch (HttpException $ex) {
            $this->logger->error($ex->getMessage());
            return new JsonResponse(['error' => $ex->getMessage()], $ex->getCode());
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            return new JsonResponse(['error' => 'Unknown error.'], 500);
        }
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
