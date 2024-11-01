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
 * SpeedSize_Buffer.
 */
final class SpeedSize_Buffer
{
    /**
     * Initialize buffer
     * @method init
     * @param  mixed              $url
     * @param  mixed              $attachment_id
     * @return mixed
     */
    public static function init()
    {
        if (
            (SpeedSize_Config::is_realtime_parsing_enabled() || SpeedSize_Config::is_css_parsing_enabled()) &&
            SpeedSize_Config::is_enabled()
        ) {
            add_action((defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ? 'xmlrpc_call' : 'template_redirect'), 'SpeedSize_Buffer::start', -9999);
        }
    }

    /**
     * @method start
     */
    public static function start()
    {
        ob_start('SpeedSize_Buffer::process');
    }

    /**
     * @method process
     */
    public static function process($content)
    {
        if ($content) {
            $content = SpeedSize_Parser_Css::maybe_process($content);
            $content = SpeedSize_Parser_Html::maybe_process($content);
        }
        return $content;
    }
}
