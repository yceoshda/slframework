<?php
namespace spacelife\core;

/**
* Log
*/
class Log
{
    protected $fd = false;

    public function __construct($config)
    {
        $this->fd = ROOT.DS.$config->logfile;
        $this->user = isset($_SESSION['login']) ? $_SESSION['login'] : 'anonymous';
    }

    /*
    *   write
    *       write data to the log file
    */
    public function write($data, $sender = '')
    {
        $now = date('Y/m/d h:i:s');
        $buff = "[$now] - $sender - $data - {$this->user} \n";
        file_put_contents($this->fd, $buff, FILE_APPEND);
    }
}