<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  EmailService.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Services;

use Jitesoft\Moxter\Contracts\ConfigInterface;
use Jitesoft\Moxter\Contracts\EmailServiceInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * EmailService
 *
 * @author  Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class EmailService implements EmailServiceInterface, LoggerAwareInterface {
    private LoggerInterface $logger;
    private PHPMailer $mailer;

    private function convertTo($type) {
        switch (mb_strtoupper($type)) {
            case 'INT':
                return static fn($val) => (int)$val;
            case 'BOOL':
                return static fn($val) => strtolower($val) === 'true';
        }

        return static fn($val) => $val;
    }

    public function __construct(ConfigInterface $config,
                                LoggerInterface $logger,
                                PHPMailer $mailer) {
        $this->logger = $logger;

        // Set up the mailer.
        $this->mailer = $mailer;
        $this->mailer->isSMTP();
        $this->mailer->SMTPAuth = isset($_ENV['SMTP_AUTH']);

        if ($config->get('SMTP_AUTH', false, $this->convertTo('bool'))) {
            $logger->info('SMTP Auth is enabled.');
            $this->mailer->Username = $config->get('SMTP_USER');
            $this->mailer->Password = $config->get('SMTP_PASSWORD');
        }

        $this->mailer->SMTPAutoTLS = true;
        $this->mailer->Port        = $config->get(
            'SMTP_PORT',
            25,
            $this->convertTo('int')
        );
        $this->mailer->Host        = $config->get('SMTP_SERVER', 'localhost');
        $this->mailer->SMTPSecure  = $config->get(
            'TLS',
            false,
            $this->convertTo('bool')
        ) ? 'tls' : '';
        $this->mailer->SMTPDebug   = $config->get(
            'SMTP_DEBUG',
            false,
            $this->convertTo('bool')
        ) ? 2 : 0;

        $logger->info(
            'Querying SMTP server at {ip}:{port}', [
                'ip'   => $this->mailer->Host,
                'port' => $this->mailer->Port
            ]
        );

        $logger->info(
            'TLS is {active}.', [
                $this->mailer->SMTPSecure ? 'active' : 'inactive'
            ]
        );

        if ($config->get('SIGN_CERT', null) !== null) {
            $logger->info('Certificate found, signing message.');
            $mailer->sign(
                $config->get('SIGN_CERT'),
                $config->get('SIGN_KEY'),
                $config->get('SIGN_KEY_PASS', '')
            );
        }

        if ($config->get('SMTP_INSECURE',false, $this->convertTo('bool'))) {
            $this->logger->warning(
                'SMTP_INSECURE set to true. Do you really want this?'
            );
            $this->mailer->SMTPOptions = array(
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            );
        }
    }

    /**
     * Send a email.
     *
     * @param string       $from     Email address for the FROM field.
     * @param string       $fromName Name for the FROM field.
     * @param string|array $to       Email address/es to send email to.
     * @param string       $subject  Subject line of email.
     * @param string       $body     Email body.
     * @param boolean      $html     If body is HTML or not.
     * @return boolean
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send(string $from,
                         string $fromName,
                         $to,
                         string $subject,
                         string $body,
                         bool $html = false) {
        $this->logger->info('Email service queried to send email.');

        $to = (is_array($to) ? $to : [$to]);

        $this->mailer->isHTML($html);
        $this->mailer->setFrom($from, $fromName);

        foreach ($to as $recipient) {
            $this->mailer->addAddress($recipient);
        }

        $this->mailer->Subject = $subject;
        $this->mailer->Body    = $body;

        $this->logger->debug(
            'Email created, sending to {rec} recipients.', [
                'rec' => count($to)
            ]
        );

        return $this->mailer->send();
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
