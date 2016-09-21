<?php

/**
 * CookPHP framework
 *
 * @name CookPHP framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 1.0 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */
return[
    'must_be_array' => 'The email validation method must be passed an array.',
    'invalid_address' => 'Invalid email address: %s',
    'attachment_missing' => 'Unable to locate the following email attachment: %s',
    'attachment_unreadable' => 'Unable to open this attachment: %s',
    'no_from' => 'Cannot send mail with no "From" header.',
    'no_recipients' => 'You must include recipients: To, Cc, or Bcc',
    'send_failure_phpmail' => 'Unable to send email using PHP mail(). Your server might not be configured to send mail using this method.',
    'send_failure_sendmail' => 'Unable to send email using PHP Sendmail. Your server might not be configured to send mail using this method.',
    'send_failure_smtp' => 'Unable to send email using PHP SMTP. Your server might not be configured to send mail using this method.',
    'sent' => 'Your message has been successfully sent using the following protocol: %s',
    'no_socket' => 'Unable to open a socket to Sendmail. Please check settings.',
    'no_hostname' => 'You did not specify a SMTP hostname.',
    'smtp_error' => 'The following SMTP error was encountered: %s',
    'no_smtp_unpw' => 'Error: You must assign a SMTP username and password.',
    'failed_smtp_login' => 'Failed to send AUTH LOGIN command. Error: %s',
    'smtp_auth_un' => 'Failed to authenticate username. Error: %s',
    'smtp_auth_pw' => 'Failed to authenticate password. Error: %s',
    'smtp_data_failure' => 'Unable to send data: %s',
    'exit_status' => 'Exit status code: %s'
];
