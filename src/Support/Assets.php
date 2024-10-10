<?php

namespace Npds\Support;

use Npds\Routing\Url;

/**
 * Undocumented class
 */
class Assets
{

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected static $templates = array
    (
        'js'  => '<script src="%s" type="text/javascript"></script>',
        'css' => '<link href="%s" rel="stylesheet" type="text/css">'
    );

    /**
     * Undocumented function
     *
     * @param [type] $files
     * @param [type] $template
     * @return void
     */
    protected static function resource($files, $template)
    {
        $template = self::$templates[$template];

        if (is_array($files)) {
            foreach ($files as $file) {
                echo sprintf($template, $file) . "\n";
            }
        } else {
            echo sprintf($template, $files) . "\n";
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $files
     * @param boolean $cache
     * @param boolean $refresh
     * @param string $cachedMins
     * @return void
     */
    public static function js($files, $cache = false, $refresh = false, $cachedMins = '1440')
    {
        if (is_null($files)) {
            return;
        }

        $path = Url::relativeTemplatePath().'js/compressed.min.js';
        $type = 'js';

        if ($cache == false) {
            static::resource($files, $type);
        } else {
            if ($refresh == false && file_exists($path) && (filemtime($path) > (time() - 60 * $cachedMins))) {
                static::resource(WEBPATH.$path, $type);
            } else {
                $source = static::collect($files, $type);
                $source = JsMin::minify($source);// Minify::js($source);
                file_put_contents($path, $source);
                static::resource(WEBPATH.$path, $type);
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $files
     * @param boolean $cache
     * @param boolean $refresh
     * @param string $cachedMins
     * @return void
     */
    public static function css($files, $cache = false, $refresh = false, $cachedMins = '1440')
    {
        if (is_null($files)) {
            return;
        }

        $path = Url::relativeTemplatePath().'css/compressed.min.css';
        $type = 'css';

        if ($cache == false) {
            static::resource($files, $type);
        } else {
            if ($refresh == false && file_exists($path) && (filemtime($path) > (time() - 60 * $cachedMins))) {
                static::resource(WEBPATH.$path, $type);
            } else {
                $source = static::collect($files, $type);
                $source = static::compress($source);
                file_put_contents($path, $source);
                static::resource(WEBPATH.$path, $type);
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $files
     * @param [type] $type
     * @return void
     */
    private static function collect($files, $type)
    {
        $content = null;
        if (is_array($files)) {
            foreach ($files as $file) {
                if (!empty($file)) {
                    if (strpos(basename($file), '.min.') === false && $type == 'css') { //compress files that aren't minified
                        $content.= static::compress(file_get_contents($file));
                    } else {
                        $content.= file_get_contents($file);
                    }
                }
            }
        } else {
            if (!empty($files)) {
                if (strpos(basename($files), '.min.') === false && $type == 'css') { //compress files that aren't minified
                    $content.= static::compress(file_get_contents($files));
                } else {
                    $content.= file_get_contents($files);
                }
            }
        }

        return $content;
    }

    /**
     * Undocumented function
     *
     * @param [type] $buffer
     * @return void
     */
    private static function compress($buffer)
    {
        /* remove comments */
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        /* remove tabs, spaces, newlines, etc. */
        $buffer = str_replace(array("\r\n","\r","\n","\t",'  ','    ','     '), '', $buffer);
        /* remove other spaces before/after ; */
        $buffer = preg_replace(array('(( )+{)','({( )+)'), '{', $buffer);
        $buffer = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $buffer);
        $buffer = preg_replace(array('(;( )+)','(( )+;)'), ';', $buffer);
        return $buffer;
    }
    
}
