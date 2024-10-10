<?php

namespace Npds\Support;

use Npds\Config;

/**
 * Undocumented class
 */
class ReCaptcha
{

    /**
     * 
     */
    const GOOGLEHOST = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $recaptcha_sitekey;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $recaptcha_secret;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $remoteip;


    /**
     * Undocumented function
     */
    public function __construct()
    {
        $this->remoteip = $_SERVER['REMOTE_ADDR'];

        $this->recaptcha_sitekey = Config::get('recaptcha_sitekey');
        $this->recaptcha_secret  = Config::get('recaptcha_secret');
    }

    /**
     * Undocumented function
     *
     * @param [type] $response
     * @return void
     */
    public function checkResponse($response)
    {
        if (empty($response)) {
            return false;
        }

        $google_url = sprintf('%s?secret=%s&response=%s&remoteip=%s', self::GOOGLEHOST, $this->recaptcha_secret, $response, $this->remoteip);

        $response = file_get_contents($google_url);

        if ($response === false) {
            return false;
        }

        $response = json_decode($response, true);

        return ($response['success'] === true);
    }
    
}
