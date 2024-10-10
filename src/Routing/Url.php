<?php

namespace Npds\Routing;

/**
 * Undocumented class
 */
class Url
{

    /**
     * [$segments description]
     *
     * @var [type]
     */
    private static $segments = array();


    /**
     * [redirect description]
     *
     * @param   [type] $url       [$url description]
     * @param   [type] $fullpath  [$fullpath description]
     * @param   false             [ description]
     *
     * @return  [type]            [return description]
     */
    public static function redirect($url = null, $fullpath = false)
    {
        if ($fullpath == true) {
            $url = APPPATH . $url;
        }

        header('Location: '.site_url($url));
        exit;
    }

    /**
     * [detectUri description]
     *
     * @return  [type]  [return description]
     */
    public static function detectUri()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'];

        $pathName = dirname($scriptName);

        if (strpos($requestUri, $scriptName) === 0) {
            $requestUri = substr($requestUri, strlen($scriptName));
        } else if (strpos($requestUri, $pathName) === 0) {
            $requestUri = substr($requestUri, strlen($pathName));
        }

        $uri = parse_url(ltrim($requestUri, '/'), PHP_URL_PATH);

        if (! empty($uri)) {
            return str_replace(array('//', '../'), '/', $uri);
        }

        // Empty URI of homepage; internally encoded as '/'
        return '/';
    }

    /**
     * [templatePath description]
     *
     * @param   [type]    $custom  [$custom description]
     * @param   TEMPLATE           [ description]
     *
     * @return  [type]             [return description]
     */
    public static function templatePath($custom = 'default')
    {
        return WEBPATH.'app/themes/'.$custom.'/assets/';

    }

    /**
     * [relativeTemplatePath description]
     *
     * @param   [type]    $custom  [$custom description]
     * @param   TEMPLATE           [ description]
     *
     * @return  [type]             [return description]
     */
    public static function relativeTemplatePath($custom = 'default')
    {
        return "app/themes/".$custom."/assets/";
    }

    /**
     * [autoLink description]
     *
     * @param   [type]  $text    [$text description]
     * @param   [type]  $custom  [$custom description]
     *
     * @return  [type]           [return description]
     */
    public static function autoLink($text, $custom = null)
    {
        $regex   = '@(http)?(s)?(://)?(([-\w]+\.)+([^\s]+)+[^,.\s])@';

        if ($custom === null) {
            $replace = '<a href="http$2://$4">$1$2$3$4</a>';
        } else {
            $replace = '<a href="http$2://$4">'.$custom.'</a>';
        }

        return preg_replace($regex, $replace, $text);
    }

    /**
     * [generateSafeSlug description]
     *
     * @param   [type]  $slug  [$slug description]
     *
     * @return  [type]         [return description]
     */
    public static function generateSafeSlug($slug)
    {
        setlocale(LC_ALL, "en_US.utf8");

        $slug = preg_replace('/[`^~\'"]/', null, iconv('UTF-8', 'ASCII//TRANSLIT', $slug));

        $slug = htmlentities($slug, ENT_QUOTES, 'UTF-8');

        $pattern = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $slug = preg_replace($pattern, '$1', $slug);

        $slug = html_entity_decode($slug, ENT_QUOTES, 'UTF-8');

        $pattern = '~[^0-9a-z]+~i';
        $slug = preg_replace($pattern, '-', $slug);

        return strtolower(trim($slug, '-'));
    }

    /**
     * [previous description]
     *
     * @return  [type]  [return description]
     */
    public static function previous()
    {
        header('Location: '. $_SERVER['HTTP_REFERER']);
        exit;
    }

    /**
     * [segments description]
     *
     * @return  [type]  [return description]
     */
    public static function segments()
    {
        if (empty(self::$segments)) {
            $uri = self::detectUri();

            self::$segments = array_filter(explode('/', $uri), 'strlen');
        }

        return self::$segments;
    }

    /**
     * [segment description]
     *
     * @param   [type]  $id  [$id description]
     *
     * @return  [type]       [return description]
     */
    public static function segment($id)
    {
        $segments = self::segments();

        return self::getSegment($segments, $id);
    }

    /**
     * [getSegment description]
     *
     * @param   [type]  $segments  [$segments description]
     * @param   [type]  $id        [$id description]
     *
     * @return  [type]             [return description]
     */
    public static function getSegment($segments, $id)
    {
        if (array_key_exists($id, $segments)) {
            return $segments[$id];
        }
        return null;
    }

    /**
     * [lastSegment description]
     *
     * @param   [type]  $segments  [$segments description]
     *
     * @return  [type]             [return description]
     */
    public static function lastSegment($segments)
    {
        return end($segments);
    }

    /**
     * [firstSegment description]
     *
     * @param   [type]  $segments  [$segments description]
     *
     * @return  [type]             [return description]
     */
    public static function firstSegment($segments)
    {
        return $segments[0];
    }
    
}
