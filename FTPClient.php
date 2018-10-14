<?php
/**
 * Class FTPClient
 * @author https://github.com/h-rafiee [Hossein Rafiee]
 * @license MIT
 * @version 0.1
 */
class FTPClient {
    /**
     * @var
     */
    private $connectionId;
    /**
     * @var bool
     */
    private $loginOk = false;
    /**
     * @var array
     */
    private $messageArray = array();

    /**
     * FTPClient constructor.
     */
    public function __construct() { }

    /**
     *
     */
    public function __deconstruct()
    {
        if ($this->connectionId) {
            ftp_close($this->connectionId);
        }
    }

    /**
     * @param $message
     */
    private function logMessage($message)
    {
        $this->messageArray[] = $message;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messageArray;
    }

    /**
     * @param $server
     * @param $ftpUser
     * @param $ftpPassword
     * @param bool $isPassive
     * @return bool
     */
    public function connect ($server, $ftpUser, $ftpPassword, $isPassive = false)
    {
        // *** Set up basic connection
        $this->connectionId = ftp_connect($server);
        // *** Login with username and password
        $loginResult = ftp_login($this->connectionId, $ftpUser, $ftpPassword);
        // *** Sets passive mode on/off (default off)
        ftp_pasv($this->connectionId, $isPassive);
        // *** Check connection
        if ((!$this->connectionId) || (!$loginResult)) {
            $this->logMessage('FTP connection has failed!');
            $this->logMessage('Attempted to connect to ' . $server . ' for user ' . $ftpUser, true);
            return false;
        } else {
            $this->logMessage('Connected to ' . $server . ', for user ' . $ftpUser);
            $this->loginOk = true;
            return true;
        }
    }

    /**
     * @param $directory
     * @return bool
     */
    public function makeDir($directory)
    {
        // *** If creating a directory is successful...
        if (ftp_mkdir($this->connectionId, $directory)) {
            $this->logMessage('Directory "' . $directory . '" created successfully');
            return true;
        } else {
            $this->logMessage('Failed creating directory "' . $directory . '"');
            return false;
        }
    }

    /**
     * @param $directory
     */
    public function makeDirRecursive ($directory){
        $parts = explode('/',$directory);
        foreach($parts as $part){
            if(!@ftp_chdir($this->connectionId, $part)){
                ftp_mkdir($this->connectionId, $part);
                ftp_chdir($this->connectionId, $part);
            }
        }
    }

    /**
     * @param $fileFrom
     * @param $fileTo
     * @return bool
     */
    public function uploadFile ($fileFrom, $fileTo)
    {
        // *** Set the transfer mode
        $asciiArray = array('txt', 'csv');
        $file_explode = explode('.', $fileFrom);
        $extension = end($file_explode);
        if (in_array($extension, $asciiArray)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }
        // *** Upload the file
        $upload = ftp_put($this->connectionId, $fileTo, $fileFrom, $mode);
        // *** Check upload status
        if (!$upload) {
            $this->logMessage('FTP upload has failed!');
            return false;
        } else {
            $this->logMessage('Uploaded "' . $fileFrom . '" as "' . $fileTo);
            return true;
        }
    }

    /**
     * @param $directory
     * @return bool
     */
    public function changeDir($directory)
    {
        if (ftp_chdir($this->connectionId, $directory)) {
            $this->logMessage('Current directory is now: ' . ftp_pwd($this->connectionId));
            return true;
        } else {
            $this->logMessage('Couldn\'t change directory');
            return false;
        }
    }

    /**
     * @param string $directory
     * @param string $parameters
     * @return array
     */
    public function getDirListing($directory = '.', $parameters = '-la')
    {
        // get contents of the current directory
        $contentsArray = ftp_nlist($this->connectionId, $parameters . '  ' . $directory);
        return $contentsArray;
    }

    /**
     * @param $fileFrom
     * @param $fileTo
     * @return bool
     */
    public function downloadFile ($fileFrom, $fileTo)
    {
        // *** Set the transfer mode
        $asciiArray = array('txt', 'csv');
        $file_split = explode('.', $fileFrom);
        $extension = end($file_split);
        if (in_array($extension, $asciiArray)) {
            $mode = FTP_ASCII;
        } else {
            $mode = FTP_BINARY;
        }
        // try to download $remote_file and save it to $handle
        if (ftp_get($this->connectionId, $fileTo, $fileFrom, $mode, 0)) {
            $this->logMessage(' file "' . $fileTo . '" successfully downloaded');
            return true;
        } else {
            $this->logMessage('There was an error downloading file "' . $fileFrom . '" to "' . $fileTo . '"');
            return false;
        }
    }
}
