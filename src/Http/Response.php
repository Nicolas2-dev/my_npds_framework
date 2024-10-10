<?php

namespace Npds\Http;

/**
 * Undocumented class
 */
class Response
{
    /**
     * [$headers description]
     *
     * @var [type]
     */
    private static $headers = array();

    /**
     * [$status description]
     *
     * @var [type]
     */
    public static $status = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );


    /**
     * [redirect description]
     *
     * @param   [type] $url       [$url description]
     * @param   [type] $fullPath  [$fullPath description]
     * @param   false             [ description]
     *
     * @return  [type]            [return description]
     */
    public static function redirect($url = null, $fullPath = false)
    {
        if ($fullPath !== false) {
            $url = site_url($url);
        }

        header('Location: '.$url);

        // Stop processing and quit.
        exit;
    }

    /**
     * [addStatus description]
     *
     * @param   [type]  $code  [$code description]
     *
     * @return  [type]         [return description]
     */
    public static function addStatus($code)
    {
        if (! isset(self::$status[$code])) {
            return;
        }

        $httpProtocol = $_SERVER['SERVER_PROTOCOL'];

        self::addHeader("$httpProtocol $code " . self::$status[$code]);
    }

    /**
     * [addHeader description]
     *
     * @param   [type]  $header  [$header description]
     *
     * @return  [type]           [return description]
     */
    public static function addHeader($header)
    {
        self::$headers[] = $header;
    }

    /**
     * [addHeaders description]
     *
     * @param   array  $headers  [$headers description]
     * @param   array            [ description]
     *
     * @return  [type]           [return description]
     */
    public static function addHeaders(array $headers = array())
    {
        self::$headers = array_merge(self::$headers, $headers);
    }

    /**
     * [sendHeaders description]
     *
     * @return  [type]  [return description]
     */
    public static function sendHeaders()
    {
        if (!headers_sent()) {
            foreach (self::$headers as $header) {
                header($header, true);
            }
        }
    }

    /**
     * [serveFile description]
     *
     * @param   [type]  $filePath  [$filePath description]
     *
     * @return  [type]             [return description]
     */
    public static function serveFile($filePath)
    {
        $httpProtocol = $_SERVER['SERVER_PROTOCOL'];

        $expires = 60 * 60 * 24 * 365; // Cache for one year

        if (! file_exists($filePath)) {
            header("$httpProtocol 404 Not Found");

            return false;
        } else if (! is_readable($filePath)) {
            header("$httpProtocol 403 Forbidden");

            return false;
        }

        //
        // Collect the current file information.

        $finfo = \finfo_open(FILEINFO_MIME_TYPE); // Return mime type ala mimetype extension

        $contentType = \finfo_file($finfo, $filePath);

        \finfo_close($finfo);

        // There is a bug with finfo_file();
        // https://bugs.php.net/bug.php?id=53035
        //
        // Hard coding the correct mime types for presently needed file extensions
        switch ($fileExt = pathinfo($filePath, PATHINFO_EXTENSION)) {
            case 'css':
                $contentType = 'text/css';
                break;
            case 'js':
                $contentType = 'application/javascript';
                break;
            default:
                break;
        }

        //
        // Prepare and send the headers with browser-side caching support.

        // Get the last-modified-date of this very file
        $lastModified = filemtime($filePath);

        // Get the HTTP_IF_MODIFIED_SINCE header if set
        $ifModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;

        // Firstly, we finalize the output buffering.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-type: ' .$contentType);
        header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires).' GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModified).' GMT');
        //header('Etag: '.$etagFile);
        header('Cache-Control: max-age='.$expires);

        // Check if page has changed. If not, send 304 and exit
        if (@strtotime($ifModifiedSince) == $lastModified) {
            header("$httpProtocol 304 Not Modified");

            return true;
        }

        //
        // Send the current file.

        header("$httpProtocol 200 OK");
        header('Content-Length: ' .filesize($filePath));

        // Send the current file content.
        readfile($filePath);

        return true;
    }
    
}
