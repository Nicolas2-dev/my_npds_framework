<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class Number
{

    /**
     * Undocumented function
     *
     * @param [type] $number
     * @param string $prefix
     * @return void
     */
    public static function format($number, $prefix = '4')
    {
        //remove any spaces in the number
        $number = str_replace(" ", "", $number);
        $number = trim($number);

        //make sure the number is actually a number
        if (is_numeric($number)) {
            //if number doesn't start with a 0 or a $prefix add a 0 to the start.
            if ($number[0] != 0 && $number[0] != $prefix) {
                $number = "0".$number;
            }

            //if number starts with a 0 replace with $prefix
            if ($number[0] == 0) {
                $number[0] = str_replace("0", $prefix, $number[0]);
                $number = $prefix.$number;
            }

            //return the number
            return $number;

        //number is not a number
        } else {
            //return nothing
            return false;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $val1
     * @param [type] $val2
     * @return void
     */
    public static function percentage($val1, $val2)
    {
        if ($val1 > 0 && $val2 > 0) {
            $division = $val1 / $val2;
            $res = $division * 100;
            return round($res).'%';
        } else {
            return '0%';
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $bytes
     * @param integer $decimals
     * @return void
     */
    public static function humanSize($bytes, $decimals = 2)
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
    
}
