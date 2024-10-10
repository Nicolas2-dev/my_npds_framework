<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class Ftp
{

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $conn;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $basePath;


    /**
     * Undocumented function
     *
     * @param [type] $host
     * @param [type] $user
     * @param [type] $pass
     * @param [type] $base
     */
    public function __construct($host, $user, $pass, $base)
    {
        //set the basepath
        $this->basePath = $base.'/';

        //open a connection
        $this->conn = ftp_connect($host);

        //login to server
        ftp_login($this->conn, $user, $pass);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function close()
    {
        ftp_close($this->conn);
    }

    /**
     * Undocumented function
     *
     * @param [type] $dirToCreate
     * @return void
     */
    public function makeDirectory($dirToCreate)
    {
        if (!file_exists($this->basePath.$dirToCreate)) {
            ftp_mkdir($this->conn, $this->basePath.$dirToCreate);
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $dir
     * @return void
     */
    public function deleteDirectory($dir)
    {
        ftp_rmdir($this->conn, $this->basePath.$dir);
    }

    /**
     * Undocumented function
     *
     * @param [type] $folderChmod
     * @param [type] $permission
     * @return void
     */
    public function folderPermission($folderChmod, $permission)
    {
        if (ftp_chmod($this->conn, $permission, $folderChmod) !== false) {
            return "<p>$folderChmod chmoded successfully to ".$permission."</p>\n";
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $remoteFile
     * @param [type] $localFile
     * @return void
     */
    public function uploadFile($remoteFile, $localFile)
    {
        if (ftp_put($this->conn, $this->basePath.$remoteFile, $localFile, FTP_ASCII)) {
            return "<p>successfully uploaded $localFile to $remoteFile</p>\n";
        } else {
            return "<p>There was a problem while uploading $remoteFile</p>\n";
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $file
     * @return void
     */
    public function deleteFile($file)
    {
        ftp_delete($this->conn, $this->basePath.$file);
    }
    
}
