<?php
namespace Swoole;

/**
 * @since 1.9.19
 */
class Client
{
    const MSG_OOB = 1;
    const MSG_PEEK = 2;
    const MSG_DONTWAIT = 64;
    const MSG_WAITALL = 256;

    public $errCode;
    public $sock;
    public $reuse;
    public $reuseCount;
    public $type;
    public $id;
    public $setting;
    public $onReceive;
    public $onBufferFull;
    public $onBufferEmpty;
    public $onSSLReady;
    public $onError;
    public $onMessage;
    public $onConnect;
    public $onClose;

    /**
     * @param $type[required]
     * @param $async[optional]
     * @return mixed
     */
    public function __construct($type, $async=null){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $settings[required]
     * @return mixed
     */
    public function set($settings){}

    /**
     * @param $host[required]
     * @param $port[optional]
     * @param $timeout[optional]
     * @param $sockFlag[optional]
     * @return mixed
     */
    public function connect($host, $port=null, $timeout=null, $sockFlag=null){}

    /**
     * @param $size[optional]
     * @param $flag[optional]
     * @return mixed
     */
    public function recv($size=null, $flag=null){}

    /**
     * @param $data[required]
     * @param $flag[optional]
     * @return mixed
     */
    public function send($data, $flag=null){}

    /**
     * @param $dstSocket[required]
     * @return mixed
     */
    public function pipe($dstSocket){}

    /**
     * @param $filename[required]
     * @param $offset[optional]
     * @param $length[optional]
     * @return mixed
     */
    public function sendfile($filename, $offset=null, $length=null){}

    /**
     * @param $ip[required]
     * @param $port[required]
     * @param $data[required]
     * @return mixed
     */
    public function sendto($ip, $port, $data){}

    /**
     * @return mixed
     */
    public function sleep(){}

    /**
     * @return mixed
     */
    public function wakeup(){}

    /**
     * @return mixed
     */
    public function pause(){}

    /**
     * @return mixed
     */
    public function resume(){}

    /**
     * @param $callback[optional]
     * @return mixed
     */
    public function enableSSL($callback=null){}

    /**
     * @return mixed
     */
    public function getPeerCert(){}

    /**
     * @return mixed
     */
    public function verifyPeerCert(){}

    /**
     * @return mixed
     */
    public function isConnected(){}

    /**
     * @return mixed
     */
    public function getsockname(){}

    /**
     * @return mixed
     */
    public function getpeername(){}

    /**
     * @param $force[optional]
     * @return mixed
     */
    public function close($force=null){}

    /**
     * @param $eventName[required]
     * @param $callback[required]
     * @return mixed
     */
    public function on($eventName, $callback){}

    /**
     * @return mixed
     */
    public function getSocket(){}


}
