<?php
namespace Jitesoft\Moxter;

use Exception;
use Jitesoft\Container\Container;
use Jitesoft\Exceptions\Http\Server\HttpInternalServerErrorException;
use Jitesoft\Log\FileLogger;
use Jitesoft\Moxter\Config\Config;
use Jitesoft\Moxter\Contracts\ConfigInterface;
use Jitesoft\Moxter\Contracts\EmailServiceInterface;
use Jitesoft\Moxter\Http\Controllers\EmailController;
use Jitesoft\Moxter\Http\Middleware\ExceptionHandler;
use Jitesoft\Moxter\Http\Middleware\OriginCheck;
use Jitesoft\Moxter\Services\EmailService;
use Jitesoft\Router\Router;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class Kernel
 */
class Kernel {

    /** @var Container */
    protected $container;
    /** @var Router */
    protected $router;
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @return Router
     */
    public function getRouter(): Router {
        return $this->router;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws HttpInternalServerErrorException
     * @throws NotFoundExceptionInterface
     */
    public function __construct() {
        try {
            $this->container = new Container();
            $this->container->set(ContainerInterface::class, $this->container);
            $this->container->set(Router::class, Router::class, true);
            $this->container->set(ConfigInterface::class, Config::class, true);
            $this->container->set(EmailServiceInterface::class, EmailService::class, true);
            $this->container->set(PHPMailer::class, new PHPMailer(true));

            $config = $this->container->get(ConfigInterface::class);

            $this->container->set(
                LoggerInterface::class,
                new FileLogger(
                    $config->get('LOG_FILE', sys_get_temp_dir() . '/moxter.log')
                )
            );

            try {
                $this->logger = $this->container->get(LoggerInterface::class);
                $this->router = $this->container->get(Router::class);

                $this->router->registerMiddleWares([
                    OriginCheck::class,
                    ExceptionHandler::class
                ]);
                $this->router->post('/api/v1/{app}/send', EmailController::class . '@handle', [
                    ExceptionHandler::class,
                    OriginCheck::class
                ]);
            } catch (NotFoundExceptionInterface $e) {
                throw new Exception($e->getMessage());
            } catch (ContainerExceptionInterface $e) {
                throw new Exception($e->getMessage());
            }
        } catch (Exception $ex) {
            throw new HttpInternalServerErrorException('Failure in kernel creation.', 500, $ex);
        }
    }

    /**
     * @return JsonResponse
     * @throws HttpInternalServerErrorException
     */
    public function handleRequest(): JsonResponse {

        $this->logger->info('Handling request. Application is currently in {mode} mode.', [
            'mode' => $this->container->get(ConfigInterface::class)->get('APP_ENV')
        ]);

        try {
            return $this->router->handle();
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw new HttpInternalServerErrorException();
        } catch (NotFoundExceptionInterface $e) {
            throw new HttpInternalServerErrorException();
        } catch (ContainerExceptionInterface $e) {
            throw new HttpInternalServerErrorException();
        }
    }

}
