<?php

namespace Npds\Error;

/**
 * Undocumented class
 */
class Error
{
    
    /**
     * [display description]
     *
     * @param   [type]  $error  [$error description]
     * @param   [type]  $class  [$class description]
     *
     * @return  [type]          [return description]
     */
    public static function display($error, $class = 'alert alert-danger')
    {
        $row = '';
        if (is_array($error)) {
            foreach ($error as $what) {
                $row.= "<div class='$class'>$what</div>";
            }
            return $row;
        } else {
            if (isset($error)) {
                return "<div class='$class'>$error</div>";
            }
        }
        return null;
    }

}
