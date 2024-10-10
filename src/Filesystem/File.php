<?php

namespace Npds\Filesystem;

/**
 * Undocumented class
 */
class File
{

    /**
     * [$Url description]
     *
     * @var [type]
     */
    private $Url = '';

    /**
     * [$Extention description]
     *
     * @var [type]
     */
    private $Extention = '';

    /**
     * [$Size description]
     *
     * @var [type]
     */
    private $Size = 0;


    /**
     * [__construct description]
     *
     * @param   [type]  $Url  [$Url description]
     *
     * @return  [type]        [return description]
     */
    public function __construct($Url)
    {
        $this->Url = $Url;
    }

    /**
     * [Size description]
     *
     * @return  [type]  [return description]
     */
    public function Size()
    {
        $this->Size = @filesize($this->Url);
    }

    /**
     * [Extention description]
     *
     * @return  [type]  [return description]
     */
    public function Extention()
    {
        $extension = strtolower(substr(strrchr($this->Url, '.'), 1));
        $this->Extention = $extension;
    }

    /**
     * [Affiche_Size description]
     *
     * @param   [type]    $Format  [$Format description]
     * @param   CONVERTI           [ description]
     *
     * @return  [type]             [return description]
     */
    public function Affiche_Size($Format = "CONVERTI")
    {
        $this->Size();

        if (!$this->Size) return '<span class="text-danger"><strong>?</strong></span>';

        switch ($Format) {
            case "CONVERTI": // en kilo/mega ou giga
                //            return ($this->pretty_Size($this->Size));
                return ('!!bug!!');
                break;

            case "NORMAL": // en octet
                return $this->Size;
                break;
        }
    }

    /**
     * [Affiche_Extention description]
     *
     * @param   [type]  $Format  [$Format description]
     *
     * @return  [type]           [return description]
     */
    public function Affiche_Extention($Format)
    {
        $this->Extention();

        switch ($Format) {
            case "IMG":
                if ($ibid = theme_image("upload/file_types/" . $this->Extention . ".gif")) {
                    $imgtmp = $ibid;
                } else {
                    $imgtmp = "assets/images/upload/file_types/" . $this->Extention . ".gif";
                }

                if (@file_exists($imgtmp)) 
                    return '<img src="' . $imgtmp . '" />';
                else 
                    return '<img src="assets/images/upload/file_types/unknown.gif" />';
                break;

            case "webfont":
                return '
                <span class="fa-stack">
                <i class="fa fa-file fa-stack-2x"></i>
                <span class="fa-stack-1x filetype-text">' . $this->Extention . '</span>
                </span>';
                break;
        }
    }

}
