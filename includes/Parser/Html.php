<?php
/**
 * SpeedSize Image & Video AI-Optimizer - Worpress plugin
 *
 * @category Image Optimization / CDN / Page Speed & Performance
 * @package  speedsize
 * @author   SpeedSize (https://speedsize.com/)
 * @author   Developed by Pniel (Pini) Cohen | Trus (https://www.trus.co.il/)
 */

 if (! defined('ABSPATH')) {
     exit; // Exit if accessed directly.
 }

/**
 * SpeedSize_Parser_Html.
 */
final class SpeedSize_Parser_Html extends SpeedSize_Parser_AbstractParser
{
    /**
     * Can Process
     * @var bool
     */
    protected static $can_process;

    /**
     * Can process?
     * @method can_process
     * @param  bool   $refresh    Skip self cache.
     * @return bool
     */
    public static function can_process($refresh = false)
    {
        if (self::$can_process === null || $refresh) {
            self::$can_process = SpeedSize_Config::is_realtime_parsing_enabled() && SpeedSize_Config::is_enabled($refresh);
        }
        return self::$can_process;
    }

    /**
     * @method maybe_process
     * @param  string $content
     * @return string
     */
    public static function maybe_process($content)
    {
        return !self::can_process() ? $content : self::process($content);
    }

    /**
     * @method process
     * @param  string $content
     * @return string
     */
    public static function process($content)
    {
        return self::inject_by_allowed_domains($content);
    }
}
