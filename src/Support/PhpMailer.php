<?php

namespace Npds\Support;

use Npds\Config;

/**
 * Undocumented class
 */
class PhpMailer extends \PHPMailer
{

    /**
     * Undocumented function
     */
    public function __construct()
    {
        parent::__construct();

        // Get the Mailer configuration.
        $config = Config::get('emailer');

        // Set all Config options to phpMailer engine.
        $this->CharSet  = $config['charset'];
        $this->FromName = $config['from_name'];
        $this->From     = $config['from_email'];
        $this->Mailer   = $config['mailer'];

        if ($this->Mailer !== 'smtp') {
            // Let's make Tom happy!
            return null;
        }

        // SMTP only options.
        $this->Host       = $config['smtp_host'];
        $this->Port       = $config['smtp_port'];
        $this->SMTPSecure = $config['smtp_secure'];
        $this->SMTPAuth   = $config['smtp_auth'];
        $this->Username   = $config['smtp_user'];
        $this->Password   = $config['smtp_pass'];
        $this->AuthType   = $config['smtp_authtype'];

        // Let's make Tom even more happy!
        return null;
    }
    
}
