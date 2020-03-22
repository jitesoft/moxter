<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ExceptionHandler.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Http\Middleware;

use Exception;
use Jitesoft\Exceptions\Http\HttpException;
use Jitesoft\Exceptions\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * ExceptionHandler
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ExceptionHandler implements MiddlewareInterface, LoggerAwareInterface {
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
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
     */
    public function process(ServerRequestInterface $request,
                            RequestHandlerInterface $handler)
    : ResponseInterface {
        try {
            return $handler->handle($request);
        } catch (ValidationException $ex) {
            $this->logger->notice($ex->getMessage());
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        } catch (HttpException $ex) {
            $this->logger->error($ex->getMessage());
            return new JsonResponse(
                ['error' => $ex->getMessage()],
                $ex->getCode()
            );
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            return new JsonResponse(['error' => 'Unknown error.'], 500);
        }
    }

}
