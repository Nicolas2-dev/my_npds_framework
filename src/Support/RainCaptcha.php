<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class RainCaptcha
{

    /**
     * 
     */
    const HOST = 'http://raincaptcha.driversworld.us/api/v1';

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $sessionId;


    /**
     * Undocumented function
     *
     * @param [type] $sessionId
     */
    public function __construct($sessionId = null)
    {
        if ($sessionId === null) {
            $this->sessionId = md5($_SERVER['SERVER_NAME'] . ':' . $_SERVER['REMOTE_ADDR']);
        } else {
            $this->sessionId = $sessionId;
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getImage()
    {
        return self::HOST . '/image/' . $this->sessionId . '?rand' . rand(100000, 999999);
    }

    /**
     * Undocumented function
     *
     * @param [type] $answer
     * @return void
     */
    public function checkAnswer($answer)
    {
        if (empty($answer)) {
            return false;
        }
        $response = file_get_contents(self::HOST . '/check/' . $this->sessionId. '/' . $answer);
        if ($response === false) {
            return true;
        }
        return $response === 'true';
    }
    
}
