<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class Document
{

    /**
     * Undocumented function
     *
     * @param [type] $extension
     * @return void
     */
    public static function getFileType($extension)
    {
        $images = array('jpg', 'gif', 'png', 'bmp');
        $docs   = array('txt', 'rtf', 'doc', 'docx', 'pdf');
        $apps   = array('zip', 'rar', 'exe', 'html');
        $video  = array('mpg', 'wmv', 'avi', 'mp4');
        $audio  = array('wav', 'mp3');
        $db     = array('sql', 'csv', 'xls','xlsx');

        if (in_array($extension, $images)) {
            return "Image";
        }
        if (in_array($extension, $docs)) {
            return "Document";
        }
        if (in_array($extension, $apps)) {
            return "Application";
        }
        if (in_array($extension, $video)) {
            return "Video";
        }
        if (in_array($extension, $audio)) {
            return "Audio";
        }
        if (in_array($extension, $db)) {
            return "Database/Spreadsheet";
        }
        return "Other";
    }

    /**
     * Undocumented function
     *
     * @param [type] $bytes
     * @param integer $precision
     * @return void
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return void
     */
    public static function getBytesSize($value)
    {
        return preg_replace_callback('/^\s*(\d+)\s*(?:([kmgt]?)b?)?\s*$/i', function ($m) {
            switch (strtolower($m[2])) {
                case 't':
                    $m[1] *= 1024;
                    break;
                case 'g':
                    $m[1] *= 1024;
                    break;
                case 'm':
                    $m[1] *= 1024;
                    break;
                case 'k':
                    $m[1] *= 1024;
                    break;
            }
            return $m[1];
        }, $value);
    }

    /**
     * Undocumented function
     *
     * @param [type] $path
     * @return void
     */
    public static function getFolderSize($path)
    {
        $io = popen('/usr/bin/du -sb '.$path, 'r');
        $size = intval(fgets($io, 80));
        pclose($io);
        return $size;
    }

    /**
     * Undocumented function
     *
     * @param [type] $file
     * @return void
     */
    public static function getExtension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * Undocumented function
     *
     * @param [type] $file
     * @return void
     */
    public static function removeExtension($file)
    {
        if (strpos($file, '.')) {
            $file = pathinfo($file, PATHINFO_FILENAME);
        }
        return $file;
    }
    
}
