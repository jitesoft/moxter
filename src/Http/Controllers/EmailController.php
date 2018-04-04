<?php
namespace Jitesoft\Moxter\Http\Controllers;

use Exception;
use Jitesoft\Exceptions\Http\Server\HttpInternalServerErrorException;
use Jitesoft\Exceptions\Validation\ValidationException;
use Jitesoft\Moxter\Contracts\ConfigInterface;
use Jitesoft\Moxter\Contracts\EmailServiceInterface;
use Jitesoft\Validator\Rules\Email;
use Jitesoft\Validator\Rules\Text;
use Jitesoft\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;

class EmailController implements LoggerAwareInterface {

    /** @var EmailServiceInterface */
    private $service;
    /** @var Validator */
    private $validator;
    /** @var LoggerInterface */
    private $logger;
    /** @var ConfigInterface */
    private $config;

    public function __construct(LoggerInterface $logger, EmailServiceInterface $emailService, ConfigInterface $config) {
        $this->logger    = $logger;
        $this->service   = $emailService;
        $this->config    = $config;
        $this->validator = new Validator([
            Email::class,
            Text::class
        ], true);
    }

    /**
     * @param array $data
     * @param string $key
     * @return bool
     * @throws ValidationException
     */
    private function keyExists($data, $key) {
        if (!array_key_exists($key, $data)) {
            throw new ValidationException(sprintf('Missing property: "%s".', $key));
        }
        return true;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $appName
     * @return ResponseInterface
     * @throws HttpInternalServerErrorException
     * @throws ValidationException
     */
    public function handle(ServerRequestInterface $request, string $appName): ResponseInterface {
        $this->logger->debug('Fetching body from request.');
        $body = $request->getParsedBody();

        $this->keyExists($body, 'to');
        $this->keyExists($body, 'body');
        $this->keyExists($body, 'subject');

        $this->validator->validate([
            $body['to'] => [
                'email' => [
                    'pattern' => $this->config->get('EMAIL_CONSTRAINT', '/.*?/')
                ]
            ],
            $body['subject'] => [
                'text' => [
                    'length' => [
                        'min' => 1
                    ]
                ]
            ],
            $body['body'] => [
                'text' => [
                    'length' => [
                        'min' => 50
                    ]
                ]
            ]
        ]);

        $this->logger->debug('Validation completed successfully.');

        try {
            $sender = $this->config->get('SENDER', 'do-not-reply@' . $appName . '.x');
            $isHtml = $this->config->get('HTML_EMAILS', false);

            $this->service->send($sender, $appName, $body['to'], $body['subject'], $body['body'], $isHtml);
            $this->logger->info('Email sent.');
        } catch (Exception $ex) {
            $this->logger->alert('Failed to send email!');
            $this->logger->error($ex->getMessage());
            throw new HttpInternalServerErrorException(
                'Could not successfully send email. Please contact administrator.'
            );
        }

        $this->logger->debug('Sending response to user.');
        return new JsonResponse([
            'message' => 'success'
        ], 201);
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
