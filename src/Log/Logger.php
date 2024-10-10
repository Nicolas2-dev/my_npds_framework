<?php

namespace Npds\Log;

use Npds\Config\Config;
use Npds\Support\PhpMailer as Mailer;


/**
 * Undocumented class
 */
class Logger
{

    /**
     * [$emailError description]
     *
     * @var [type]
     */
    private static $emailError = false;

    /**
     * [$clear description]
     *
     * @var [type]
     */
    private static $clear = false;

    /**
     * [$display description]
     *
     * @var [type]
     */
    private static $display = false;

    /**
     * [$errorFile description]
     *
     * @var [type]
     */
    public static $errorFile = STORAGE_PATH.'framework' .DS. 'error.log';

    /**
     * [$error description]
     *
     * @var [type]
     */
    public static $error;


    /**
     * [initialize description]
     *
     * @return  [type]  [return description]
     */
    public static function initialize()
    {
        $options = Config::get('logger');

        if ($options === null) {
            return;
        }

        self::$display = $options['display_errors'];
    }

    /**
     * [customErrorMsg description]
     *
     * @return  [type]  [return description]
     */
    public static function customErrorMsg()
    {
        if (self::$display) {
            echo '<pre>'.self::$error.'</pre>';
        } else {
            echo "<p>" .__d('system', 'An error occurred. The error has been reported.') ."</p>";
            exit;
        }

    }

    /**
     * [exceptionHandler description]
     *
     * @param   [type]  $e  [$e description]
     *
     * @return  [type]      [return description]
     */
    public static function exceptionHandler($e)
    {
        self::newMessage($e);
    }

    /**
     * [errorHandler description]
     *
     * @param   [type]  $number   [$number description]
     * @param   [type]  $message  [$message description]
     * @param   [type]  $file     [$file description]
     * @param   [type]  $line     [$line description]
     *
     * @return  [type]            [return description]
     */
    public static function errorHandler($number, $message, $file, $line)
    {
        $msg = "$message in $file on line $line";

        if (($number !== E_NOTICE) && ($number < 2048)) {
            self::errorMessage($msg);
            self::$error = $msg;
            self::customErrorMsg();
        }

        return 0;
    }

    /**
     * [newMessage description]
     *
     * @param   [type]  $exception  [$exception description]
     *
     * @return  [type]              [return description]
     */
    public static function newMessage($exception)
    {
        $message = $exception->getMessage();
        $code = $exception->getCode();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        $date = date('M d, Y G:iA');

        $logMessage = "Exception information:\n
           Date: {$date}\n
           Message: {$message}\n
           Code: {$code}\n
           File: {$file}\n
           Line: {$line}\n
           Stack trace:\n
           {$trace}\n
           ---------\n\n";

        if (is_file(self::$errorFile) === false) {
            file_put_contents(self::$errorFile, '');
        }

        if (self::$clear) {
            $f = fopen(self::$errorFile, "r+");
            if ($f !== false) {
                ftruncate($f, 0);
                fclose($f);
            }
        }

        // Append
        file_put_contents(self::$errorFile, $logMessage, FILE_APPEND);

        self::$error = $logMessage;
        self::customErrorMsg();

        //send email
        self::sendEmail($logMessage);
    }

    /**
     * [errorMessage description]
     *
     * @param   [type]  $error  [$error description]
     *
     * @return  [type]          [return description]
     */
    public static function errorMessage($error)
    {
        $date = date('Y-m-d G:iA');
        $logMessage = "$date - $error\n\n";

        if (is_file(self::$errorFile) === false) {
            file_put_contents(self::$errorFile, '');
        }

        if (self::$clear) {
            $f = fopen(self::$errorFile, "r+");
            if ($f !== false) {
                ftruncate($f, 0);
                fclose($f);
            }

            $content = null;
        } else {
            // Append
            file_put_contents(self::$errorFile, $logMessage, FILE_APPEND);
        }

        /** send email */
        self::sendEmail($logMessage);
    }

    /**
     * [sendEmail description]
     *
     * @param   [type]  $message  [$message description]
     *
     * @return  [type]            [return description]
     */
    public static function sendEmail($message)
    {
        if (self::$emailError == true) {
            $mail = new Mailer();

            $mail->setFrom(SITE_EMAIL);
            $mail->addAddress(SITE_EMAIL);
            $mail->Subject = 'New error on '.SITE_TITLE;
            $mail->Body = $message;

            $mail->send();
        }
    }
    
}
