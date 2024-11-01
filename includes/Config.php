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
 * SpeedSize_Config.
 */
class SpeedSize_Config
{
    public const SPEEDSIZE_SERVICE_BASE_URL = 'https://cdn.speedsize.com';
    public const SPEEDSIZE_API_BASE_URL = 'https://api.speedsize-aws.com/api';
    public const SPEEDSIZE_BYPASS_QUERY_PARAM = 'nospeedsize';
    public const IMAGE_FILE_EXTENSIONS = 'gif|jpe?g|png|tiff?|webp|jpe|bmp';
    public const VIDEO_FILE_EXTENSIONS = 'mp4|avi|mpg|rm|mov|asf|3gp|mkv|rmvb|m4v|webm|ogv';

    public const SPEEDSIZE_CONFIG_DEFAULTS = [
        'speedsize_enabled' => 'no',
        'speedsize_client_id' => '',
        'speedsize_client_id_active' => false,
        'speedsize_service_base_url' => self::SPEEDSIZE_SERVICE_BASE_URL,
        'speedsize_client_allow_upscale' => 'off',
        'speedsize_client_forbidden_paths' => '',
        'speedsize_client_whitelist_domains' => '',
        'speedsize_js_snippet_enabled' => 'yes',
        'speedsize_size_params_enabled' => 'yes',
        'speedsize_realtime_parsing_enabled' => 'yes',
        'speedsize_parser_image_size_params_enabled' => 'yes',
        'speedsize_css_files_parsing_enabled' => 'yes',
        'speedsize_css_files_parsing_excluded_keywords' => 'jquery,bootstrap,icons,fonts',
        'speedsize_disable_processor_filters' => 'no',
        'speedsize_mute_all_videos' => 'no',
        'speedsize_allowed_html_attributes' => 'src,srcset,href,poster,bg,bg-image',
        'speedsize_keep_https_scheme_on_wrapped_media_urls' => 'no',
    ];

    private static $cache = [];

    /**
     * Get option from DB.
     * @param  string $key Option key.
     * @param  mixed $default
     * @return string
     */
    public static function get_option($key, $default = null)
    {
        return get_option($key, $default);
    }

    /**
     * Return 'yes'/'no', the actual value of the `speedsize_enabled` config
     * @method get_speedsize_enabled
     * @return string
     */
    public static function get_speedsize_enabled()
    {
        return self::get_option('speedsize_enabled', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_enabled']);
    }

    /**
     * @method get_speedsize_client_id
     * @return string
     */
    public static function get_speedsize_client_id()
    {
        return self::get_option('speedsize_client_id', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_client_id']);
    }

    /**
     * Return 'yes'/'no', the actual value of the `speedsize_js_snippet_enabled` config
     * @method get_speedsize_js_snippet_enabled
     * @return string
     */
    public static function get_speedsize_js_snippet_enabled()
    {
        return self::get_option('speedsize_js_snippet_enabled', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_js_snippet_enabled']);
    }

    /**
     * Return 'yes'/'no', the actual value of the `speedsize_size_params_enabled` config
     * @method get_speedsize_size_params_enabled
     * @return string
     */
    public static function get_speedsize_size_params_enabled()
    {
        return self::get_option('speedsize_size_params_enabled', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_size_params_enabled']);
    }

    /**
     * Return 'yes'/'no', the actual value of the `speedsize_realtime_parsing_enabled` config
     * @method get_speedsize_realtime_parsing_enabled
     * @return string
     */
    public static function get_speedsize_realtime_parsing_enabled()
    {
        return self::get_option('speedsize_realtime_parsing_enabled', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_realtime_parsing_enabled']);
    }

    /**
     * Return 'yes'/'no', the actual value of the `speedsize_parser_image_size_params_enabled` config
     * @method get_speedsize_parser_image_size_params_enabled
     * @return string
     */
    public static function get_speedsize_parser_image_size_params_enabled()
    {
        return self::get_option('speedsize_parser_image_size_params_enabled', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_parser_image_size_params_enabled']);
    }

    /**
     * Return 'yes'/'no', the actual value of the `speedsize_css_files_parsing_enabled` config
     * @method get_speedsize_css_files_parsing_enabled
     * @return string
     */
    public static function get_speedsize_css_files_parsing_enabled()
    {
        return self::get_option('speedsize_css_files_parsing_enabled', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_css_files_parsing_enabled']);
    }

    /**
     * Return 'yes'/'no', the actual value of the `speedsize_disable_processor_filters` config
     * @method get_speedsize_disable_processor_filters
     * @return string
     */
    public static function get_speedsize_disable_processor_filters()
    {
        return self::get_option('speedsize_disable_processor_filters', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_disable_processor_filters']);
    }

    /**
     * Return 'yes'/'no', the actual value of the `speedsize_mute_all_videos` config
     * @method get_speedsize_disable_processor_filters
     * @return string
     */
    public static function get_speedsize_mute_all_videos()
    {
        return self::get_option('speedsize_mute_all_videos', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_mute_all_videos']);
    }

    /**
     * Return the actual value of the `speedsize_allowed_html_attributes` config
     * @method get_speedsize_allowed_html_attributes
     * @param  bool   $returnArray
     * @return string
     */
    public static function get_speedsize_allowed_html_attributes($returnArray = false)
    {
        $additionalAllowedAttrs = self::get_option('speedsize_allowed_html_attributes', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_allowed_html_attributes']);
        return $returnArray ? SpeedSize_Helper::allowed_html_attributes_filter($additionalAllowedAttrs, true) : $additionalAllowedAttrs;
    }

    /**
     * Return the actual value of the `speedsize_css_files_parsing_excluded_keywords` config
     * @method get_speedsize_css_files_parsing_excluded_keywords
     * @param  bool   $returnArray
     * @return string|array
     */
    public static function get_speedsize_css_files_parsing_excluded_keywords($returnArray = false)
    {
        $keywords = self::get_option('speedsize_css_files_parsing_excluded_keywords', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_css_files_parsing_excluded_keywords']);
        return $returnArray ? SpeedSize_Helper::css_files_parsing_excluded_keywords_filter($keywords, true) : $keywords;
    }

    /**
     * @method get_speedsize_client_id_active
     * @return bool
     */
    public static function get_speedsize_client_id_active()
    {
        return self::get_option('speedsize_client_id_active', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_client_id_active']);
    }

    /**
     * @method get_speedsize_service_base_url
     * @return string
     */
    public static function get_speedsize_service_base_url()
    {
        return defined('SPEEDSIZE_CUSTOM_SERVICE_BASE_URL') ?
            SPEEDSIZE_CUSTOM_SERVICE_BASE_URL :
            self::get_option('speedsize_service_base_url', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_service_base_url']);
    }

    /**
     * @method get_speedsize_client_allow_upscale
     * @return string
     */
    public static function get_speedsize_client_allow_upscale()
    {
        return self::get_option('speedsize_client_allow_upscale', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_client_allow_upscale']);
    }

    /**
     * Return the actual value of the `speedsize_client_forbidden_paths` config (or as an array)
     * @method get_speedsize_client_forbidden_paths
     * @param  bool   $returnArray
     * @return string|array
     */
    public static function get_speedsize_client_forbidden_paths($returnArray = false)
    {
        $paths = self::get_option('speedsize_client_forbidden_paths', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_client_forbidden_paths']);
        return $returnArray ? SpeedSize_Helper::speedsize_client_forbidden_paths_filter($paths, true) : $paths;
    }

    /**
     * Return the actual value of the `speedsize_client_whitelist_domains` config (or as an array)
     * @method get_speedsize_client_whitelist_domains
     * @param  bool   $returnArray
     * @return string|array
     */
    public static function get_speedsize_client_whitelist_domains($returnArray = false)
    {
        $domains = self::get_option('speedsize_client_whitelist_domains', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_client_whitelist_domains']);
        return $returnArray ? SpeedSize_Helper::whitelist_domains_filter($domains, true) : $domains;
    }

    /**
     * @method get_should_keep_http_scheme_on_wrapped_media_urls
     * @return string
     */
    public static function get_should_keep_http_scheme_on_wrapped_media_urls()
    {
        return self::get_option('speedsize_keep_https_scheme_on_wrapped_media_urls', self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_keep_https_scheme_on_wrapped_media_urls']);
    }

    /**
     * @method set_speedsize_enabled
     * @param  bool  $value
     * @return mixed
     */
    public static function set_speedsize_enabled($value)
    {
        return update_option('speedsize_enabled', in_array($value, ['yes', true, 1, '1']) ? 'yes' : 'no');
    }

    /**
     * @method set_speedsize_client_id
     * @param  bool  $value
     * @return mixed
     */
    public static function set_speedsize_client_id($value)
    {
        return update_option('speedsize_client_id', (string) $value);
    }

    /**
     * @method set_speedsize_client_id_active
     * @param  bool  $value
     * @return mixed
     */
    public static function set_speedsize_client_id_active($value)
    {
        return update_option('speedsize_client_id_active', (bool) $value);
    }

    /**
     * @method set_speedsize_service_base_url
     * @param  mixed  $value
     * @return mixed
     */
    public static function set_speedsize_service_base_url($value)
    {
        return update_option('speedsize_service_base_url', rtrim((string) $value, '/') ?: self::SPEEDSIZE_SERVICE_BASE_URL);
    }

    /**
     * @method set_speedsize_client_allow_upscale
     * @param  mixed  $value
     * @return mixed
     */
    public static function set_speedsize_client_allow_upscale($value)
    {
        return update_option('speedsize_client_allow_upscale', $value ?: self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_client_allow_upscale']);
    }

    /**
     * @method set_speedsize_client_forbidden_paths
     * @param  mixed  $value
     * @return mixed
     */
    public static function set_speedsize_client_forbidden_paths($value)
    {
        return update_option('speedsize_client_forbidden_paths', $value ?: self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_client_forbidden_paths']);
    }

    /**
     * @method set_speedsize_client_whitelist_domains
     * @param  mixed  $value
     * @return mixed
     */
    public static function set_speedsize_client_whitelist_domains($value)
    {
        return update_option('speedsize_client_whitelist_domains', $value ?: self::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_client_whitelist_domains']);
    }

    // Config Helpers

    /**
     * Check if SpeedSize is enabled with credentials and not bypassed
     * @param  bool    $refresh         Skip self cache.
     * @return bool
     */
    public static function is_enabled($refresh = false)
    {
        if (!isset(self::$cache['is_enabled']) || $refresh) {
            self::$cache['is_enabled'] = self::get_speedsize_enabled() === 'yes' &&
                (self::has_valid_credentials()) && !is_admin() &&
                (!isset($_GET[SpeedSize_Config::SPEEDSIZE_BYPASS_QUERY_PARAM]));
        }
        return self::$cache['is_enabled'];
    }

    /**
     * Is SpeedSize JS Snippet enabled.
     * @return bool
     */
    public static function is_speedsize_js_snippet_enabled()
    {
        return self::get_speedsize_js_snippet_enabled() === 'yes';
    }

    /**
     * Is SpeedSize size params enabled.
     * @return bool
     */
    public static function is_speedsize_size_params_enabled()
    {
        return self::get_speedsize_size_params_enabled() === 'yes';
    }

    /**
     * Is real-time parsing enabled.
     * @return bool
     */
    public static function is_realtime_parsing_enabled()
    {
        return self::get_speedsize_realtime_parsing_enabled() === 'yes';
    }

    /**
     * Is parser using image size params.
     * @return bool
     */
    public static function is_parser_image_size_params_enabled()
    {
        return self::is_speedsize_size_params_enabled() && self::get_speedsize_parser_image_size_params_enabled() === 'yes';
    }

    /**
     * Is css parser enabled.
     * @return bool
     */
    public static function is_css_parsing_enabled()
    {
        return self::get_speedsize_css_files_parsing_enabled() === 'yes';
    }

    /**
     * Is processor filters enabled.
     * @return bool
     */
    public static function is_processor_filters_enabled()
    {
        return self::get_speedsize_disable_processor_filters() === 'no';
    }

    /**
     * @method should_keep_http_scheme_on_wrapped_media_urls
     * @return bool
     */
    public static function should_keep_http_scheme_on_wrapped_media_urls()
    {
        return self::get_should_keep_http_scheme_on_wrapped_media_urls() === 'yes';
    }

    /**
     * @method should_mute_all_videos
     * @return boolean
     */
    public static function should_mute_all_videos()
    {
        return self::get_speedsize_mute_all_videos() === 'yes';
    }

    /**
     * @return string
     */
    public static function has_valid_credentials()
    {
        if (
            self::get_speedsize_client_id() &&
            self::get_speedsize_client_id_active()
        ) {
            return true;
        }
        return false;
    }

    /**
     * @method get_speedsize_service_url
     * @param string $path
     * @return string
     */
    public static function get_speedsize_service_url($path = "")
    {
        return self::get_speedsize_service_base_url() . '/' . (string) $path;
    }

    /**
     * @method get_speedsize_api_url
     * @param string $path
     * @return string
     */
    public static function get_speedsize_api_url($path = "")
    {
        return self::SPEEDSIZE_API_BASE_URL . '/' . (string) $path;
    }

    /**
     * @method get_allowed_image_file_extensions
     * @return string
     */
    public static function get_allowed_image_file_extensions()
    {
        return self::IMAGE_FILE_EXTENSIONS;
    }

    /**
     * @method get_allowed_video_file_extensions
     * @return string
     */
    public static function get_allowed_video_file_extensions()
    {
        return self::VIDEO_FILE_EXTENSIONS;
    }

    /**
     * @method get_site_domain
     * @param  bool    $refresh         Skip self cache.
     * @return string
     */
    public static function get_site_domain($refresh = false)
    {
        if (!isset(self::$cache['site_domain']) || $refresh) {
            self::$cache['site_domain'] = preg_replace('/^(?:https?\:)?\/\//', '', rtrim((string) site_url(), '/'));
        }
        return self::$cache['site_domain'];
    }

    /**
     * @method get_home_domain
     * @param  bool    $refresh         Skip self cache.
     * @return string
     */
    public static function get_home_domain($refresh = false)
    {
        if (!isset(self::$cache['home_domain']) || $refresh) {
            self::$cache['home_domain'] = preg_replace('/^(?:https?\:)?\/\//', '', rtrim((string) home_url(), '/'));
        }
        return self::$cache['home_domain'];
    }

    /**
     * @method get_upload_path
     * @param  bool    $refresh         Skip self cache.
     * @return string
     */
    public static function get_upload_path($refresh = false)
    {
        if (!isset(self::$cache['upload_path']) || $refresh) {
            self::$cache['upload_path'] = trim(get_option('upload_path')) ?: 'wp-content/uploads';
        }
        return self::$cache['upload_path'];
    }

    /**
     * @method get_registered_image_subsizes
     * @param  bool    $refresh         Skip self cache.
     * @return array
     */
    public static function get_registered_image_subsizes($refresh = false)
    {
        if (!isset(self::$cache['registered_image_subsizes']) || $refresh) {
            self::$cache['registered_image_subsizes'] = wp_get_registered_image_subsizes();
        }
        return self::$cache['registered_image_subsizes'];
    }

    /**
     * @method is_size_crop_enabled
     * @param  string|int[] $size       Image size
     * @param  bool         $refresh    Skip self cache.
     * @return bool
     */
    public static function is_size_crop_enabled($size, $refresh = false)
    {
        $size_key = is_array($size) ? implode('x', $size) : (string) $size;
        if (!isset(self::$cache[$size_key . '_crop_enabled']) || $refresh) {
            self::$cache[$size_key . '_crop_enabled'] = false;
            $subsizes = self::get_registered_image_subsizes($refresh);
            if (is_array($size) && count($size) >= 2) {
                $size = array_values($size);
                foreach ($subsizes as $subsize) {
                    if (absint($size[0]) === $subsize['width'] && absint($size[1]) === $subsize['height']) {
                        self::$cache[$size_key . '_crop_enabled'] = !empty($subsize['crop']);
                        break;
                    }
                }
            } else {
                self::$cache[$size_key . '_crop_enabled'] = isset($subsizes[$size_key]) && !empty($subsizes[$size_key]['crop']);
            }
        }
        return self::$cache[$size_key . '_crop_enabled'];
    }

    /**
     * @method get_basic_site_allowed_domains (without configured additional allowed domains)
     * site and home domains.
     * @return array
     */
    public static function get_basic_site_allowed_domains()
    {
        return [
            self::get_site_domain(),
            self::get_home_domain()
        ];
    }

    /**
     * @method get_all_allowed_domains (including configured additional allowed domains)
     * @param  bool         $refresh    Skip self cache.
     * @return array
     */
    public static function get_all_allowed_domains($refresh = false)
    {
        if (!isset(self::$cache['all_allowed_domains']) || $refresh) {
            self::$cache['all_allowed_domains'] = array_unique(array_filter(array_merge(
                SpeedSize_Helper::whitelist_domains_filter(self::get_basic_site_allowed_domains(), true),
                self::get_speedsize_client_whitelist_domains(true)
            )));
        }
        return self::$cache['all_allowed_domains'];
    }

    /**
     * @method get_allowed_html_attributes
     * @param  bool         $refresh    Skip self cache.
     * @return array
     */
    public static function get_allowed_html_attributes($refresh = false)
    {
        if (!isset(self::$cache['allowed_html_attributes']) || $refresh) {
            self::$cache['allowed_html_attributes'] = self::get_speedsize_allowed_html_attributes(true);
        }
        return self::$cache['allowed_html_attributes'];
    }

    /**
     * @method get_css_files_parsing_excluded_keywords
     * @param  bool         $refresh    Skip self cache.
     * @return array
     */
    public static function get_css_files_parsing_excluded_keywords($refresh = false)
    {
        if (!isset(self::$cache['css_files_parsing_excluded_keywords']) || $refresh) {
            self::$cache['css_files_parsing_excluded_keywords'] = self::get_speedsize_css_files_parsing_excluded_keywords(true);
        }
        return self::$cache['css_files_parsing_excluded_keywords'];
    }

    /**
     * @method get_speedsize_client_forbidden_paths_patterns
     * @param  bool         $refresh    Skip self cache.
     * @return array
     */
    public static function get_speedsize_client_forbidden_paths_patterns($refresh = false)
    {
        if (!isset(self::$cache['speedsize_client_forbidden_paths_patterns']) || $refresh) {
            self::$cache['speedsize_client_forbidden_paths_patterns'] = [];
            foreach (self::get_speedsize_client_forbidden_paths(true) as &$path) {
                self::$cache['speedsize_client_forbidden_paths_patterns'][] = sprintf('/%s/ms', str_replace('\*', '.*', preg_quote($path, '/')));
            }
        }
        return self::$cache['speedsize_client_forbidden_paths_patterns'];
    }

    /**
     * @method is_allowed_upscale
     * @param  bool         $refresh    Skip self cache.
     * @return bool
     */
    public static function is_allowed_upscale($refresh = false)
    {
        if (!isset(self::$cache['is_allowed_upscale']) || $refresh) {
            $value = trim((string)self::get_speedsize_client_allow_upscale());
            if (
                $value === 'all' ||
                ($value === 'hp' && is_home())
            ) {
                self::$cache['is_allowed_upscale'] = true;
            } else {
                self::$cache['is_allowed_upscale'] = false;
            }
        }
        return self::$cache['is_allowed_upscale'];
    }

    /**
     * @method is_using_speedsize_ecdn
     * @return boolean
     */
    public static function is_using_speedsize_ecdn()
    {
        return \strpos(self::get_speedsize_service_url(''), 'ecdn.speedsize.com') !== false ? true : false;
    }
}
