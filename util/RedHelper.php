<?php declare(strict_types=1);

/**
 * Create by Red.jiang in 10/14/21 at 10:36 AM
 *
 * Email redmadfinger@gmail.com
 */
class RedHelper
{
    /*
     * Load current file with functions
     */
    public static function load()
    {
    }
}

/**
 * Load config file
 */
if (!function_exists("red_load_config")) {
    function red_load_config(string $dir) {
        RedConfig::load($dir);
    }
}

/**
 * Log info
 *
 * @param $text
 * @param mixed $extra
 */
function red_log($text, $extra = null)
{
    RedLog::info($text, $extra);
}

/**
 * Log info
 *
 * @param $text
 * @param mixed $extra
 */
function red_log_info($text, $extra = null)
{
    RedLog::info($text, $extra);
}

/**
 * Log debug
 *
 * @param $text
 * @param mixed $extra
 */
function red_log_debug($text, $extra = null)
{
    RedLog::debug($text, $extra);
}

/**
 * Log error
 *
 * @param $text
 * @param mixed $extra
 */
function red_log_error($text, $extra = null)
{
    RedLog::error($text, $extra);
}

/**
 * Log warn
 *
 * @param $text
 * @param mixed $extra
 */
function red_log_warn($text, $extra = null)
{
    RedLog::warn($text, $extra);
}

/**
 * Log warn
 *
 * @param string $tag
 * @param string $text
 * @param mixed $extra
 */
function red_log_start($tag, $text, $extra = null)
{
    RedLog::start($tag, $text, $extra);
}

/**
 * Log warn
 *
 * @param string $tag
 * @param string $text
 * @param mixed $extra
 */
function red_log_end($tag, $text, $extra = null)
{
    RedLog::end($tag, $text, $extra);
}

