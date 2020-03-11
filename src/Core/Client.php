<?php namespace CiSwoole\Core;

/**
 * ------------------------------------------------------------------------------------
 * Swoole Client
 * ------------------------------------------------------------------------------------
 *
 * @author  lanlin
 * @change  2018/06/30
 */
class Client
{

    // ------------------------------------------------------------------------------

    /**
     * server config
     *
     * warning: do not change this
     *
     * @var array
     */
    private static $config =
    [
        'package_eof' => '☯',         // \u262F
        'server_port' => null,
        'server_host' => '/var/run/swoole.sock',
        'debug_file'  => APPPATH . 'logs/swoole_debug.log',
    ];

	private static $_instance = null;
	private $client = null;

	/**
	 * Singleton magic :)
	 */
	private function __construct () {
//		$client = new \Swoole\Client(SWOOLE_TCP);
//		$this->client->set(TCPHelper::TCP_OPTIONS);
//		$this->tcp_client->connect('127.0.0.1', 9502);
	}

	private function __clone () {}
	private function __wakeup () {}

	public static function get_instance()
	{
		if (self::$_instance === null) {
			self::$_instance = new self();
		}


		return self::$_instance;
	}
    // ------------------------------------------------------------------------------

    /**
     * connect to swoole server then send data
     *
     * @param array $data
     * [
     *     'route'   => 'your/route/uri', // the route uri will be call
     *     'params'  => [],               // params will be passed to your method
     * ];
     */
    public static function send(array $data, $fd = NULL, callable $callback = NULL)
    {
        self::initConfig();

        // select mode
        $sapi = php_sapi_name();
        $mode = ($sapi === 'cli') ? SWOOLE_SOCK_ASYNC : SWOOLE_SOCK_SYNC;

        /**
         * @property array $stamsel
         */
        switch ($mode) {
			case SWOOLE_SOCK_ASYNC:
				$client = new \Swoole\Coroutine\Client(SWOOLE_UNIX_STREAM);
				break;
			default:
				$client = new \Swoole\Client(SWOOLE_UNIX_STREAM, $mode);
		}

        // dynamic custom data
        $client->CiSwooleData = $data;
        $client->response_to = $fd;
        $client->callback = $callback;

        // set eof charactor
        $client->set(
        [
            'open_eof_split' => true,
            'package_eof'    => self::$config['package_eof'],
        ]);

        // client init
        ($mode === SWOOLE_SOCK_ASYNC) ? self::asyncInit($client) : self::syncInit($client);
    }

    // ------------------------------------------------------------------------------

    /**
     * reload swoole server
     */
    public static function reload()
    {
        self::send(['reload' => true]);
    }

    // ------------------------------------------------------------------------------

    /**
     * shutdown swoole server
     */
    public static function shutdown()
    {
        self::send(['shutdown' => true]);
    }

    // ------------------------------------------------------------------------------

    /**
     * trigger when connect
     *
     * @param \Swoole\Client $client
     */
    public static function onConnect(\Swoole\Client $client)
    {
        $post  = serialize($client->CiSwooleData);
        $post .= self::$config['package_eof'];

        $client->send($post);
    }

    // ------------------------------------------------------------------------------

    /**
     * trigger on error
     *
     * @param \Swoole\Client $client
     */
    public static function onError(\Swoole\Client $client)
    {
        $msg = "Swoole client (onError) error code: {$client->errCode} socket_strerror: ".socket_strerror($client->errCode);

        self::logs($msg);
//        error_log($msg, 3, self::$config['debug_file']);
    }

    // ------------------------------------------------------------------------------

    /**
     * trigger on buffer empty
     *
     * @param \Swoole\Client $client
     */
    public static function onBufferEmpty(\Swoole\Client $client)
    {
        $client->close();
    }

    // ------------------------------------------------------------------------------

    /**
     * trigger when receive
     *
     * @param \Swoole\Client $client
     * @param string $data
     */
    public static function onReceive(\Swoole\Client $client, $data)
    {
    	echo "Receive fuck\n";
    	var_dump($data);
        return;
    }

    // ------------------------------------------------------------------------------

    /**
     * trigger when close
     *
     * @param \Swoole\Client $client
     */
    public static function onClose(\Swoole\Client $client)
    {
    	echo "Fuck, I closed :)\n";
        return;
    }

    // ------------------------------------------------------------------------------

    /**
     * init config
     *
     * @throws \Exception
     */
    private static function initConfig()
    {
        $config = getCiSwooleConfig('swoole');

        self::$config = array_merge(self::$config, $config);
    }

    // ------------------------------------------------------------------------------

    /**
     * async mode init
     *
     * @param \Swoole\Coroutine\Client $client
     */
    private static function asyncInit(\Swoole\Coroutine\Client $client)
    {
    	go(function() use ($client) {
			$cnnt = $client->connect(self::$config['server_host'], self::$config['server_port']);

			if (!$cnnt) {
//				$msg = "swoole client error code: {$client->errCode}";
                $msg = "Swoole client (async connect fail) error code: {$client->errCode} socket_strerror: ".socket_strerror($client->errCode);

                self::logs($msg);
//				error_log($msg, 3, self::$config['debug_file']);
				return;
			}

			$post = serialize($client->CiSwooleData);
			$post .= self::$config['package_eof'];
			$check = $client->send($post);

			if ($check === FALSE) {
//				$msg = "swoole client error code: {$client->errCode}";
                $msg = "Swoole client (async send false) error code: {$client->errCode} socket_strerror: ".socket_strerror($client->errCode);

                self::logs($msg);
//				error_log($msg, 3, self::$config['debug_file']);
			}

			$result = $client->recv();
			$result = str_replace(self::$config['package_eof'], '', $result);
			$result = unserialize($result);
			if (!is_null($client->callback))
				call_user_func_array($client->callback,[$client->response_to, $result]);
			$client->close();
		});
    }

    // ------------------------------------------------------------------------------

    /**
     * sync mode init
     *
     * @param \Swoole\Client $client
     */
    private static function syncInit(\Swoole\Client $client)
    {
        $cnnt = $client->connect(self::$config['server_host'], self::$config['server_port']);

        if (!$cnnt)
        {
            $msg = "Swoole client (sync connect fail) error code: {$client->errCode} socket_strerror: ".socket_strerror($client->errCode);

            self::logs($msg);
//            error_log($msg, 3, self::$config['debug_file']);
            return;
        }

        $post  = serialize($client->CiSwooleData);
        $post .= self::$config['package_eof'];
        $check = $client->send($post);

        if ($check === false)
        {
            $msg = "Swoole client (sync send false) error code: {$client->errCode} socket_strerror: ".socket_strerror($client->errCode);
            self::logs($msg);
//            error_log($msg, 3, self::$config['debug_file']);
        }

        $client->close();
    }

    // ------------------------------------------------------------------------------

    /**
     * log message to debug
     *
     * @param \Throwable $msg
     */
    private static function logs($msg)
    {
        $strings  = $msg;

//        $strings .= $msg->getTraceAsString();

        $time_nw  = date('Y-m-d H:i:s');
        $content  = "\n{$time_nw} [CiSwoole\\Core\\Client]: ";
        $content .= "{$strings}";
        $content .= "\n===================================\n";

        error_log($content, 3, self::$config['debug_file']);
    }

}