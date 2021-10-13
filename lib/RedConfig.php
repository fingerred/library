<?php declare(strict_types=1);

/**
 * 环境配置加载
 *
 * Create by Red.jiang in 10/13/21 at 6:11 PM
 *
 * Email redmadfinger@gmail.com
 */
class RedConfig implements ArrayAccess
{
    // 配置
    protected $config = [];

    // 环境变量
    protected $env = 'production';

    // 实例
    protected static $instance;

    /**
     * RedConfig constructor.
     *
     * @param string|null $env
     */
    public function __construct(string $env = null)
    {
        if (!$env) {
            $env = getenv("ENV");
            $this->env = $env ? $env : 'production';
        }
    }

    /**
     * Load dir
     *
     * @param $dir
     *
     * @return static
     */
    public static function load($dir)
    {
        return self::instance()->loadConfig($dir);
    }

    /**
     * Get instance
     *
     * @return RedConfig
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::instance()->loadConfig(dirname(__DIR__) . '/config');
        }

        return self::$instance;
    }

    /**'
     * @param $dir
     *
     * @return $this
     */
    public function loadConfig($dir)
    {
        $configPath = realpath($dir);

        // Path not found
        if (!$configPath) {
            return $this;
        }

        $defaultFile = $configPath . '/default.php';
        $envFile = $configPath . '/' . self::env() . '.php';
        $localFile = $configPath . '/local.php';

        $config = [];

        if (file_exists($defaultFile)) {
            $config = include($defaultFile);
        }

        if (file_exists($envFile)) {
            $config = array_merge($config, include($envFile));
        }

        if (file_exists($localFile)) {
            $config = array_merge($config, include($localFile));
        }

        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * Get config item
     *
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::instance()->getItem($key, $default);
    }

    /**
     * Has config item
     *
     * @param $key
     *
     * @return bool
     */
    public static function has($key)
    {
        return self::instance()->hasItem($key);
    }

    /**
     * Get all item
     *
     * @return array
     */
    public static function all()
    {
        return self::instance()->getConfig();
    }

    /**
     * Get config group
     *
     * @param $prefix
     *
     * @return array
     */
    public static function group($prefix)
    {
        return self::instance()->getGroup($prefix);
    }

    /**
     * Set config item
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public static function set($key, $value)
    {
        return self::instance()->setItem($key, $value);
    }

    /**
     * Has config item
     *
     * @param $key
     */
    public static function del($key)
    {
        return self::instance()->delItem($key);
    }

    /**
     * Get config group
     *
     * @param $prefix
     *
     * @return array
     */
    public function getGroup($prefix)
    {
        $config = [];
        $len = strlen($prefix) + 1;

        foreach ($this->config as $k => $v) {
            if (strpos($k, $prefix . '.') === 0) {
                $config[substr($k, $len)] = $v;
            }
        }

        return $config;
    }

    /**
     * Get env
     *
     * @return array|false|string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @return array|false|string
     */
    public static function env()
    {
        return self::instance()->getEnv();
    }

    /**
     * Has config item
     *
     * @param $key
     *
     * @return bool
     */
    public function hasItem($key)
    {
        return isset($this->config[$key]);
    }

    /**
     * Get config item
     *
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function getItem($key, $default = null)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Set config item
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setItem($key, $value)
    {
        $this->config[$key] = $value;

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
     * Delete item
     *
     * @param $key
     */
    public function delItem($key)
    {
        unset($this->config[$key]);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->hasItem($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->getItem($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->setItem($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->delItem($offset);
    }
}