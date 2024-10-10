<?php

namespace Npds\Language;

use Npds\Support\Inflector;
use Npds\Config\Config;
use Npds\Error\Error;

/**
 * Undocumented class
 */
class Language
{
    
    /**
     * [$code description]
     *
     * @var [type]
     */
    private $code   = 'en';

    /**
     * [$info description]
     *
     * @var [type]
     */
    private $info   = 'English';

    /**
     * [$name description]
     *
     * @var [type]
     */
    private $name   = 'English';

    /**
     * [$locale description]
     *
     * @var [type]
     */
    private $locale = 'en-US';

    /**
     * [$messages description]
     *
     * @var [type]
     */
    private $messages = array();

    /**
     * [$instances description]
     *
     * @var [type]
     */
    private static $instances = array();


    /**
     * [__construct description]
     *
     * @param   [type]  $domain  [$domain description]
     * @param   [type]  $code    [$code description]
     *
     * @return  [type]           [return description]
     */
    public function __construct($domain, $code)
    {
        $languages = Config::get('languages');

        if (isset($codes)) {
            $info = $languages[$code];

            $this->code = $code;

            $this->info   = $info['info'];
            $this->name   = $info['name'];
            $this->locale = $info['locale'];
        } else {
            $code = 'en';
        }

        //
        $pathName = Inflector::classify($domain);

        $langPath = '';

        if ($pathName == 'System') {
            $langPath = NPDSPATH;
        } else if ($pathName == 'App') {
            $langPath = APPPATH;
        } else if (is_dir(APPPATH.'Packages'.DS.$pathName)) {
            $langPath = APPPATH.'Packages/'.$pathName;
        } else if (is_dir(APPPATH.'Modules'.DS.$pathName)) {
            $langPath = APPPATH.'Modules/'.$pathName;
        } else if (is_dir(APPPATH.'Themes'.DS.$pathName)) {
            $langPath = APPPATH.'Themes/'.$pathName;
        }

        if (empty($langPath)) {
            return;
        }

        $filePath = str_replace('/', DS, $langPath.'/Language/'.$code.'/messages.php');

        // Check if the language file is readable.
        if (! is_readable($filePath)) {
            return;
        }

        // Get the domain messages from the language file.
        $messages = include($filePath);

        // Final Consistency check.
        if (is_array($messages) && ! empty($messages)) {
            $this->messages = $messages;
        }
    }

    /**
     * [get description]
     *
     * @param   [type]         $domain  [$domain description]
     * @param   app            $code    [$code description]
     * @param   LANGUAGE_CODE           [ description]
     *
     * @return  [type]                  [return description]
     */
    public static function &get($domain = 'app', $code = LANGUAGE_CODE)
    {
        // The ID code is something like: 'en/system', 'en/app', 'en/file_manager' or 'en/template/admin'
        $id = $code.'/'.$domain;

        // Initialize the domain instance, if not already exists.
        if (! isset(self::$instances[$id])) {
            self::$instances[$id] = new self($domain, $code);
        }

        return self::$instances[$id];
    }

    /**
     * [translate description]
     *
     * @param   [type] $message  [$message description]
     * @param   [type] $params   [$params description]
     * @param   array            [ description]
     *
     * @return  [type]           [return description]
     */
    public function translate($message, $params = array())
    {
        // Update the current message with the domain translation, if we have one.
        if (isset($this->messages[$message]) && ! empty($this->messages[$message])) {
            $message = $this->messages[$message];
        }

        if (empty($params)) {
            return $message;
        }

        // Standard Message formatting, using the standard PHP Intl and its MessageFormatter.
        // The message string should be formatted using the standard ICU commands.
        return \MessageFormatter::formatMessage($this->locale, $message, $params);

        // The VSPRINTF alternative for Message formatting, for those die-hard against ICU.
        // The message string should be formatted using the standard PRINTF commands.
        //return vsprintf($message, $arguments);
    }

    /**
     * [code description]
     *
     * @return  [type]  [return description]
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * [info description]
     *
     * @return  [type]  [return description]
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * [name description]
     *
     * @return  [type]  [return description]
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * [locale description]
     *
     * @return  [type]  [return description]
     */
    public function locale()
    {
        return $this->locale;
    }

    /**
     * [messages description]
     *
     * @return  [type]  [return description]
     */
    public function messages()
    {
        return $this->messages;
    }
    
}
