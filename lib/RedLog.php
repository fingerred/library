<?php declare(strict_types=1);

/**
 * Create by Red.jiang in 10/14/21 at 10:45 AM
 *
 * Email redmadfinger@gmail.com
 */
class RedLog
{
    protected $lastLogTime;
    protected $timer = [];

    protected static $instance = null;
    protected static $flushEnabled = false;

    protected $messages = [];

    protected $config = [
        "filename" => "app.log",
        "label" => "DEFAULT"
    ];

    protected $env;

    /**
     * RedLog constructor.
     *
     * @param array $config
     */
    function __construct($config = [])
    {
        $this->env = RedConfig::env();
        $this->logDebugEnv = self::logDebugEnv();
        $this->logStdoutEnv = self::logStdoutEnv();
        $this->logDisabled = self::logDisabled();
        $this->config = array_merge($this->config, self::defaultConfig(), $config);

        if (!self::$flushEnabled) {
            register_shutdown_function(array('RedLog', 'flush'));
            self::$flushEnabled = true;
        }
    }

    /**
     * Get instance
     *
     * @return RedLog|null
     */
    public function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get deBugEnv
     *
     * @return bool
     */
    protected static function logDebugEnv()
    {
        return !!getenv("RED_LOG_DEBUG");
    }

    /**
     * Get deBugEnv
     *
     * @return bool
     */
    protected static function logStdoutEnv()
    {
        return !!getenv("RED_LOG_STDOUT");
    }

    /**
     * Get deBugEnv
     *
     * @return bool
     */
    protected static function logDisabled()
    {
        return !!getenv("RED_LOG_DISABLED");
    }

    /**
     * Get default config
     *
     * @return array
     */
    protected static function defaultConfig()
    {
        return [
            "dir" => RedConfig::get('redlog.dir')
        ];
    }

    /**
     * Config
     *
     * @param array $config
     *
     * @return mixed
     */
    static function config(array $config = null)
    {
        if (!$config) return self::instance()->getConfig();

        return self::instance()->setConfig($config);
    }

    /**
     * Save info log
     *
     * @param $text
     * @param mixed $extra
     */
    static function info($text, $extra = null)
    {
        self::instance()->add('I', $text, $extra);
    }

    /**
     * Save debug log
     *
     * @param $text
     * @param mixed $extra
     */
    static function debug($text, $extra = null)
    {
        self::instance()->add('D', $text, $extra);
    }

    /**
     * Save warn log
     *
     * @param $text
     * @param mixed $extra
     */
    static function warn($text, $extra = null)
    {
        self::instance()->add('W', $text, $extra);
    }

    /**
     * Save error log
     *
     * @param $text
     * @param mixed $extra
     */
    static function error($text, $extra = null)
    {
        self::instance()->add('E', $text, $extra);
    }


    /**
     * 计时结束
     *
     * @param $tag
     * @param $text
     * @param $extra
     */
    static function end($tag, $text = null, $extra = null)
    {
        self::instance()->endLog($tag, $text, $extra);
    }

    /**
     * 计时开始
     *
     * @param $tag
     * @param $text
     * @param $extra
     */
    static function start($tag, $text = null, $extra = null)
    {
        self::instance()->startLog($tag, $text, $extra);
    }

    /**
     * 返回日志路径
     *
     * @return string
     */
    static function path()
    {
        return self::instance()->getFilePath();
    }

    /**
     * Set label
     *
     * @param $label
     */
    static function label($label)
    {
        self::instance()->setLabel($label);
    }

    /**
     * Flush all logs
     */
    static function flush()
    {
        self::instance()->flushLogs();
    }

    /**
     * Safe text
     *
     * @param $text
     *
     * @return mixed|string
     */
    protected static function safeText($text)
    {
        return !$text || is_string($text) ? (string) $text : json_encode($text, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get micro time
     *
     * @return float
     */
    protected static function getMicrotime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Set config
     *
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set label
     *
     * @param $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $label = strtoupper(substr($label, 0, 10));
        $this->config['label'] = $label;

        return $this;
    }

    /**
     * Start log
     *
     * @param $tag
     * @param $text
     * @param mixed $extra
     *
     * @return $this
     */
    public function startLog($tag, $text, $extra = null)
    {
        $this->timer[$tag] = self::getMicrotime();
        $tagText = "[$tag/0ms]";
        $text = $text ? self::safeText($text) . " $tagText" : $tagText;
        $this->add('I', $text, $extra);

        return $this;
    }

    /**
     * End log
     *
     * @param $tag
     * @param $text
     * @param mixed $extra
     *
     * @return $this
     */
    public function endLog($tag, $text = null, $extra = null)
    {
        // 根据 timerKey 获取开始时间，并计算本段程序执行时间
        if (!empty($this->timer[$tag])) {
            $runTime = round((self::getMicrotime() - $this->timer[$tag]) * 1000);
            unset($this->timer[$tag]);
        } else {
            $runTime = 0;
        }
        $tagText = "[$tag/{$runTime}ms]";
        $text = $text ? self::safeText($text) . " $tagText" : $tagText;
        $this->add('I', $text, $extra);

        return $this;
    }

    /**
     * Add log
     *
     * @param $level
     * @param $message
     * @param mixed $extra
     *
     * @return $this
     */
    public function add($level, $message, $extra = null)
    {
        if ($this->logDisabled) return $this;

        $distanceTime = $this->lastLogTime ? round((self::getMicrotime() - $this->lastLogTime) * 1000) : 0;
        $this->lastLogTime = self::getMicrotime();
        if ($this->env === "production" && !$this->logDebugEnv && $level === 'D') {
            return $this;
        }

        $label = $this->config['label'];
        if (is_array($extra) && !empty($extra['label'])) {
            $label = $extra['label'];
        }

        //组装消息
        $message = [
            'label' => $label,
            'time' => date("Y-m-d H:i:s", time()),
            'level' => $level,
            'body' => self::safeText($message),
            'spend' => $distanceTime,
            'extra' => self::safeText($extra)
        ];

        if ($this->logStdoutEnv) {
            // 格式化单条日志
            $logMsg = self::formatMsg($message);
            // 直接输出
            fwrite(STDOUT, $logMsg);
        } else {
            $this->messages[] = $message;
        }

        return $this;
    }

    /**
     * 格式化消息
     *
     * @param $message
     *
     * @return string
     */
    protected static function formatMsg($message)
    {
        return str_pad("(" . strtoupper($message['label']) . ")", 10, " ") . " " . $message['time'] . ' <' . $message['level'] . '> ' . preg_replace("/[\r\n]+/", ' ', $message['body'], -1) . " " . (!empty($message['extra']) ? json_encode(preg_replace("/[\r\n]+/", ' ', $message['extra'], -1)) . ' ' : '') . "+" . $message['spend'] . "ms";
    }

    /**
     * 校验日志目录
     *
     * @return string
     *
     * @throws Error
     */
    public function getFilePath()
    {
        $filePath = $this->config['dir'] . DIRECTORY_SEPARATOR . $this->config['filename'];
        $dirName = dirname($filePath);
        if (!is_dir($dirName)) {
            @mkdir($dirName, 0777, true);
        }

        return $filePath;
    }

    /**
     * Write logs to file
     */
    public function flushLogs()
    {
        if (empty($this->messages)) return $this;

        // 1. 校验路径
        $filePath = $this->getFilePath();

        $logMsg = "";
        foreach ($this->messages as $message) {
            // 整理日志
            $logMsg .= self::formatMsg($message) . PHP_EOL;
        }

        // 2. 写入日志
        @file_put_contents($filePath, $logMsg, FILE_APPEND);

        $this->messages = [];

        return $this;
    }

    /**
     * Register error handler
     */
    public static function recordErrors()
    {
        set_exception_handler(array('RedLog', "exceptionHandler"));
        set_error_handler(array('RedLog', "errorHandler"));
        register_shutdown_function(array('RedLog', "shutdownHandler"));
    }

    /**
     *
     * Exception handler
     *
     * @desc  异常处理函数
     * @param $e
     */
    public static function exceptionHandler($e)
    {
        self::instance()->add('E', '[' . $e->getCode() . ']:' . $e->getMessage(), array('file' => $e->getFile(), 'line' => $e->getLine()));
    }

    /**
     * @desc  错误处理函数
     *
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        self::exceptionHandler(new ErrorException($errstr, $errno, 0, $errfile, $errline));
    }

    /**
     * Shutdown handler
     */
    public static function shutdownHandler()
    {
        $error = error_get_last();
        if ($error) {
            self::exceptionHandler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
        }
    }
}