<?php

/**
 * Plugin Name: SpeedSize Image & Video AI-Optimizer
 * Description: ~90-99% smaller media, 100% of the visual quality. Get Sharper & Faster.
 * Author: SpeedSize
 * Author URI: https://speedsize.com/
 * Version: 1.5.0
 * Requires at least: 5.0
 * Text Domain: speedsize
 * Domain Path: /languages
 */

/**
 * SpeedSize Image & Video AI-Optimizer - Worpress plugin
 *
 * @category Image Optimization / CDN / Page Speed & Performance
 * @package  speedsize
 * @author   SpeedSize (https://speedsize.com/)
 * @author   Developed by Pniel (Pini) Cohen | Trus (https://www.trus.co.il/)
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SPEEDSIZE_VERSION', '1.5.0');
define('SPEEDSIZE_PATH', plugin_dir_path(__FILE__));
define('SPEEDSIZE_ASSETS_PATH', SPEEDSIZE_PATH . 'assets/');
define('SPEEDSIZE_TEMPLATES_PATH', SPEEDSIZE_PATH . 'templates/');
define('SPEEDSIZE_ASSETS_URL', plugins_url('assets/', __FILE__));

require_once(SPEEDSIZE_PATH . 'includes/autoload.php');

add_action('plugins_loaded', 'speedsize_init');

function speedsize_init()
{
    if (class_exists('SpeedSize')) {
        return;
    }

    load_plugin_textdomain('speedsize', false, plugin_basename(dirname(__FILE__)) . '/languages');

    class SpeedSize
    {
        /**
         * @var Singleton instance of this class.
         */
        private static $instance;

        /**
         * @var Singleton instance of this class.
         */
        private static $speedsize_client_forbidden_paths_patterns = [];

        private function __construct()
        {
            $this->init();
        }

        /**
         * Prevent cloning of the instance of the Singleton instance.
         *
         * @return void
         */
        private function __clone() {}

        /**
         * Prevent unserializing of the Singleton instance.
         *
         * @return void
         */
        public function __wakeup()
        {
            throw new \Exception("Cannot unserialize singleton");
        }

        /**
         * Returns the *Singleton* instance of this class.
         *
         * @return Singleton instance of this class.
         */
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Init the plugin after plugins_loaded.
         */
        public function init()
        {
            // Filters
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_action_links']);
            add_filter('speedsize_prefix_url_excluded', [$this, 'default_speedsize_prefix_url_excluded'], 0, 2);

            // Actions
            add_action('admin_init', 'SpeedSize_Settings::init');
            add_action('admin_menu', [$this, 'add_admin_menus']);

            add_action('speedsize_cron_clear_expired_css_cache', [$this, 'speedsize_clear_expired_css_cache']);
            $this->maybe_schedule_css_cache_clear_expired();

            //add_action('speedsize_cron_refresh_client_settings', [$this, 'speedsize_refresh_client_settings']);
            //$this->maybe_schedule_speedsize_refresh_client_settings();

            if (SpeedSize_Config::is_enabled()) {
                // add_action('send_headers', array($this, 'send_headers'), 999);
                add_action('wp_headers', [$this, 'add_headers'], 999); // Replaced with send_headers on template_redirect
                add_action('wp_head', [$this, 'add_preconnect_link'], 10);
                self::$speedsize_client_forbidden_paths_patterns = SpeedSize_Config::get_speedsize_client_forbidden_paths_patterns();

                if (SpeedSize_Config::is_speedsize_js_snippet_enabled()) {
                    add_action('wp_footer', [$this, 'load_js_snippet'], 10);
                }
            }

            SpeedSize_Processor::init();
            SpeedSize_Buffer::init();
        }

        /**
         * Adds plugin action links.
         */
        public function plugin_action_links($links)
        {
            $plugin_links = [
                '<a href="admin.php?page=speedsize-settings">' . esc_html__('SpeedSize Settings', 'speedsize') . '</a>',
            ];
            return array_merge($plugin_links, $links);
        }

        /**
         * Adds plugin headers.
         */
        public function add_headers($headers)
        {
            $speedsize_headers = SpeedSize_Helper::get_speedsize_headers();

            if (!isset($headers['Accept-CH'])) {
                $headers['Accept-CH'] = $speedsize_headers['Accept-CH'];
            }

            if (!isset($headers['Feature-Policy'])) {
                $headers['Feature-Policy'] = $speedsize_headers['Accept-CH'];
            }

            return $headers;
        }

        /**
         * [Currently not in use]
         * Send plugin headers using php header() instead of the wp_headers.
         */
        public function send_headers()
        {
            if (!headers_sent()) {
                $current_headers = headers_list();
                $speedsize_headers = SpeedSize_Helper::get_speedsize_headers();

                $accept_ch_set = false;
                $feature_policy_set = false;

                foreach ($current_headers as $header) {
                    if (stripos(strtolower($header), 'Accept-CH:') === 0) {
                        $accept_ch_set = true;
                    }
                    if (stripos(strtolower($header), 'Feature-Policy:') === 0) {
                        $feature_policy_set = true;
                    }
                }

                if (!$accept_ch_set) {
                    header('Accept-CH: ' . $speedsize_headers['Accept-CH']);
                }

                if (!$feature_policy_set) {
                    header('Feature-Policy: ' . $speedsize_headers['Feature-Policy']);
                }
            }
        }

        /**
         * Adds preconnect link to HTML head.
         */
        public function add_preconnect_link()
        {
?>
            <link rel="preconnect" href="<?php esc_attr_e(SpeedSize_Config::get_speedsize_service_base_url()); ?>" data-speedsize-plugin="<?= esc_attr_e(SPEEDSIZE_VERSION) ?>">
<?php
        }

        /**
         * Load SpeedSize JS snippet.
         */
        public function load_js_snippet()
        {

            include SPEEDSIZE_TEMPLATES_PATH . 'speedsize-snippet.php';
        }

        /**
         * Add admin menus
         */
        public function add_admin_menus()
        {
            add_menu_page(
                'SpeedSize Settings',
                'SpeedSize',
                'manage_options',
                'speedsize-settings',
                'SpeedSize_Settings::render_admin_settings_page',
                'data:image/svg+xml;base64,' . base64_encode(file_get_contents(SPEEDSIZE_ASSETS_PATH . 'images/speedsize-logo-icon.svg')),
                90
            );
        }

        /**
         * Schedule CSS expired cache clear if needed
         */
        public function maybe_schedule_css_cache_clear_expired()
        {
            if (
                !SpeedSize_Config::is_css_parsing_enabled() ||
                SpeedSize_Config::get_speedsize_enabled() !== 'yes'
            ) {
                return;
            }

            if (!wp_next_scheduled('speedsize_cron_clear_expired_css_cache')) {
                global $wp_version;
                wp_schedule_event(time(), (version_compare($wp_version, '5.4', '<') ? 'daily' : 'weekly'), 'speedsize_cron_clear_expired_css_cache');
            }
        }

        /**
         * @method speedsize_clear_expired_css_cache
         */
        public function speedsize_clear_expired_css_cache()
        {
            SpeedSize_Parser_Css::clear_expired_cache(12);
        }

        /**
         * Schedule SpeedSize refresh client settings (Client ID status, Base-URL, AllowedUpscale, ...)
         */
        public function maybe_schedule_speedsize_refresh_client_settings()
        {
            if (SpeedSize_Config::get_speedsize_enabled() !== 'yes') {
                return;
            }

            if (!wp_next_scheduled('speedsize_cron_refresh_client_settings')) {
                global $wp_version;
                wp_schedule_event(time(), (version_compare($wp_version, '5.4', '<') ? 'daily' : 'weekly'), 'speedsize_cron_refresh_client_settings');
            }
        }

        /**
         * @method speedsize_refresh_client_settings
         */
        public function speedsize_refresh_client_settings()
        {
            SpeedSize_Helper::refresh_speedsize_client_id_status();
            SpeedSize_Helper::refresh_speedsize_client_base_url();
            SpeedSize_Helper::refresh_speedsize_client_allow_upscale();
        }

        /**
         * Alias for SpeedSize_Helper::prefix_url()
         * @method prefix_url
         * @param  string               $url
         * @param  array                $params
         * @param  string|null          $file_type  'image' / 'video'
         * @return string
         */
        public static function prefix_url($url = "", $params = [], $file_type = null)
        {
            return SpeedSize_Helper::prefix_url($url, $params, $file_type);
        }

        /**
         * Default excluded URLs.
         */
        public function default_speedsize_prefix_url_excluded($excluded, $url)
        {
            if (preg_match('#captcha|/api/khub/maps/#msi', $url)) {
                return true;
            }

            if (self::$speedsize_client_forbidden_paths_patterns) {
                $path = preg_replace('#^\s*(?:https?\:)?\/\/[^\/]+\/#msi', '/', $url);
                foreach (self::$speedsize_client_forbidden_paths_patterns as $pattern) {
                    if (preg_match($pattern, $path)) {
                        return true;
                    }
                }
            }

            return $excluded;
        }
    }

    SpeedSize::get_instance();
}
