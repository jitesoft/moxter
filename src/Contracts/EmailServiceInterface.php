<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  EmailServiceInterface.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Contracts;

/**
 * EmailServiceInterface
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
interface EmailServiceInterface {

    /**
     * Send a email.
     *
     * @param string $from
     * @param string $fromName
     * @param string|array $to
     * @param string $subject
     * @param string $body
     * @param bool $html
     * @return bool
     */
    public function send(string $from, string $fromName, $to, string $subject, string $body, bool $html = false);

}
