<?php

namespace Npds\Support\Debug;

use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;

/**
 * 
 */
class Dumper
{

    /**
     * [dump description]
     *
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    public function dump($value)
    {
        $cloner = new VarCloner();
        
        if (in_array(PHP_SAPI, array('cli', 'phpdbg'))) 
        {
            $dumper = new CliDumper();
        } 
        else 
        {
            $dumper = new HtmlDumper();
        }
        
        $dumper->dump($cloner->cloneVar($value));
    }

}