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
 * SpeedSize_Helper.
 */
class SpeedSize_Helper
{
    /**
     * @method is_valid_file_extension
     * @param  mixed              $url
     * @param  mixed              $attachment_id
     * @return mixed
     */
    public static function is_valid_url_extension($url)
    {
        return preg_match('/\.(?:' . SpeedSize_Config::get_allowed_image_file_extensions() . '|' . SpeedSize_Config::get_allowed_video_file_extensions() . ')(?:\?.*)?$/msi', $url);
    }

    /**
     * @method is_image_attachment
     * @param  int $attachment_id Attachment post ID.
     * @return boolean
     */
    public static function is_image_attachment($attachment_id)
    {
        return wp_attachment_is('image', $attachment_id);
    }

    /**
     * @method is_video_attachment
     * @param  int $attachment_id Attachment post ID.
     * @return boolean
     */
    public static function is_video_attachment($attachment_id)
    {
        return wp_attachment_is('video', $attachment_id);
    }

    /**
     * @method is_audio_attachment
     * @param  int $attachment_id Attachment post ID.
     * @return boolean
     */
    public static function is_audio_attachment($attachment_id)
    {
        return wp_attachment_is('audio', $attachment_id);
    }

    /**
     * @method get_attachment_type
     * @param  int $attachment_id Attachment post ID.
     * @return string
     */
    public static function get_attachment_type($attachment_id)
    {
        if (self::is_image_attachment($attachment_id)) {
            return 'image';
        }
        if (self::is_video_attachment($attachment_id)) {
            return 'video';
        }
        if (self::is_audio_attachment($attachment_id)) {
            return 'audio';
        }
        return 'unknown';
    }

    /**
     * @method is_image_or_video_attachment
     * @param  int $attachment_id Attachment post ID.
     * @return boolean
     */
    public static function is_image_or_video_attachment($attachment_id)
    {
        return wp_attachment_is('image', $attachment_id) || wp_attachment_is('video', $attachment_id);
    }

    /**
     * @method get_attachment_original_image_url
     * @param  int $attachment_id Attachment post ID.
     * @return string|false Attachment image URL, false on error or if the attachment is not an image.
     */
    public static function get_attachment_original_image_url($attachment_id)
    {
        return wp_get_original_image_url($attachment_id);
    }

    /**
     * @method is_speedsize_url
     * @param  mixed              $url
     * @return mixed
     */
    public static function is_speedsize_url($url)
    {
        return strpos($url, SpeedSize_Config::get_speedsize_service_base_url()) === 0 ||
            strpos($url, SpeedSize_Config::SPEEDSIZE_SERVICE_BASE_URL) === 0;
    }

    /**
     * @method contains_speedsize_url
     * @param  mixed              $url
     * @return mixed
     */
    public static function contains_speedsize_url($url)
    {
        return strpos($url, SpeedSize_Config::get_speedsize_service_base_url()) !== false ||
            strpos($url, SpeedSize_Config::SPEEDSIZE_SERVICE_BASE_URL) !== false;
    }

    /**
     * @method strip_sizes_from_url
     * @param  string              $url
     * @return string
     */
    public static function strip_sizes_from_image_url($url)
    {
        return preg_replace('/\-\d{1,4}x\d{1,4}(\.(?:' . SpeedSize_Config::get_allowed_image_file_extensions() . ')(?:\?.*)?$)/msi', '$1', $url);
    }

    /**
     * @method separate_sizes_from_image_url
     * @param  string              $url
     * @return array               $image
     */
    public static function separate_sizes_from_image_url($url)
    {
        $image = [
            'url' => $url,
            'width' => null,
            'height' => null,
            'crop' => false,
        ];

        preg_match('/(.*)\-(\d{1,4})x(\d{1,4})(\.(?:' . SpeedSize_Config::get_allowed_image_file_extensions() . ')(?:\?.*)?$)/ms', $url, $matches);
        if ($matches && count($matches) === 5) {
            $image['url'] = $matches[1] . $matches[4];
            $image['width'] = $matches[2];
            $image['height'] = $matches[3];
            $image['crop'] = SpeedSize_Config::is_size_crop_enabled([$image['width'], $image['height']]);
        }

        return $image;
    }

    /**
     * @method get_speedsize_prefix_url
     * @param  string $path
     * @param  array $params
     * @return string
     */
    public static function convert_wp_uploads_url_to_path($url)
    {
        preg_match('/' . preg_quote(SpeedSize_Config::get_upload_path(), '/') . '\/(.+?)(?:\?|$)/msi', $url, $matches);
        if ($matches && !empty($matches[1])) {
            return trailingslashit(wp_upload_dir()['basedir']) . $matches[1];
        }
        return false;
    }

    /**
     * @method is_url_file_exists
     * @param  string $url
     * @return bool
     */
    public static function is_url_file_exists($url)
    {
        $path = SpeedSize_Helper::convert_wp_uploads_url_to_path($url);
        return $path && file_exists($path);
    }

    /**
     * @method get_media_url_allowed_types_pattern
     * @return string
     */
    public function get_media_url_allowed_types_pattern()
    {
        return sprintf(
            '/\.(?:(%s)|(%s))(?:$|\?)/ims',
            SpeedSize_Config::get_allowed_image_file_extensions(),
            SpeedSize_Config::get_allowed_video_file_extensions()
        );
    }

    /**
     * @method get_media_url_allowed_type
     * @return string|null
     */
    public function get_media_url_allowed_type($url)
    {
        preg_match(self::get_media_url_allowed_types_pattern(), $url, $type);
        if (!empty($type[1])) {
            return 'image';
        }
        if (!empty($type[2])) {
            return 'video';
        }
        return null;
    }

    /**
     * @method get_speedsize_prefix_url
     * @param  string $path
     * @param  array $params
     * @return string
     */
    public static function get_speedsize_prefix_url($path = "", $params = [])
    {
        $url = SpeedSize_Config::is_enabled() ?
            SpeedSize_Config::get_speedsize_service_url(SpeedSize_Config::get_speedsize_client_id()) . '/' . (string) $path :
            "";

        if (SpeedSize_Config::is_using_speedsize_ecdn()) {
            $url = SpeedSize_Helper::add_speedsize_params_as_query_string($url, $params);
        } else {
            $url .= $params ? '/' . implode(',', $params) : '';
        }

        return $url;
    }

    /**
     * @method prefix_url
     * @param  string               $url
     * @param  array                $params
     * @param  string|null          $file_type  'image' / 'video'
     * @return string
     */
    public static function prefix_url($url = "", $params = [], $file_type = null)
    {
        if (SpeedSize_Helper::contains_speedsize_url($url)) {
            return $url;
        }
        if (!SpeedSize_Config::is_enabled()) {
            return $url;
        }
        if (substr($url, 0, 1) === '/' && substr($url, 1, 1) !== '/') {
            $url = SpeedSize_Helper::home_url($url);
        }

        if (SpeedSize_Config::should_keep_http_scheme_on_wrapped_media_urls() || !is_ssl()) {
            // Add http(s) if missing
            if (substr($url, 0, 2) === '//') {
                $url = (is_ssl() ? 'https:' : 'http:') . $url;
            }
        } else {
            // Remove https:// if exists (start wrapped URL with the domain after SpeedSize client ID)
            $url = preg_replace('#^\s*(?:https\:)?\\\\?\/\\\\?\/#msi', '', $url);
        }

        // Get file type
        $file_type = $file_type ? $file_type : self::get_media_url_allowed_type($url);

        // Add image upscale params if needed ("upscl" if size params are present or "enh" if not)
        if ($file_type === 'image' && SpeedSize_Config::is_allowed_upscale()) {
            $upscaleParams = preg_grep("/^(?:w|h)_\d/ims", $params) ? ['upscl'] : ['enh'];
            $params = array_filter(array_unique(array_merge(
                $upscaleParams,
                $params
            )));
        }

        // Add video v_muted params if speedsize_mute_all_videos is enabled
        if ($file_type === 'video' && SpeedSize_Config::should_mute_all_videos()) {
            $params[] = "v_muted";
        }

        return SpeedSize_Helper::get_speedsize_prefix_url($url, $params);
    }

    /**
     * @method add_speedsize_url_params
     * @param string $url
     * @param array $params
     * @return string
     */
    public static function add_speedsize_url_params($url = "", $params = [])
    {
        if (SpeedSize_Config::is_using_speedsize_ecdn()) {
            $url = preg_replace('/((?:\?|&)speedsize=[^&#]*?)((?:enh|upscl),?)/ms', '$1', $url);
            $url = SpeedSize_Helper::add_speedsize_params_as_query_string($url, $params);
        } else {
            if (SpeedSize_Config::is_allowed_upscale()) {
                $url = preg_replace('/\/(?:enh|upscl)\/*$/ms', '', $url);
            }
            $url .= ($params ? '/' . implode(",", $params) : "");
        }

        return $url;
    }

    /**
     * @method add_speedsize_params_as_query_string
     * @param  string                          $url
     * @param  array                           $params
     * @return string
     */
    public static function add_speedsize_params_as_query_string($url, $params = [])
    {
        // If no params - do nothing and return the original URL.
        if (!$params) {
            return $url;
        }

        $paramName = 'speedsize';

        // If URL has no speedsize params and no fragment, append params as query.
        $hasQuery = strpos($url, '?') !== false;
        if (
            strpos($url, '#') === false &&
            (!$hasQuery || strpos($url, $paramName . '=') === false)
        ) {
            $separator = $hasQuery ? '&' : '?';
            return $url . $separator . urlencode($paramName) . '=' . implode(',', $params);
        }

        // Else - parse URL and add to the current speedsize params
        $urlParts = explode('#', $url);
        $_fragment = isset($urlParts[1]) ? $urlParts[1] : '';
        $urlParts = explode('?', $urlParts[0]);
        $_url = isset($urlParts[0]) ? $urlParts[0] : '';
        $_query = isset($urlParts[1]) ? $urlParts[1] : '';
        $_query = (array) explode('&', $_query);

        foreach ($_query as &$_queryParam) {
            if (strpos($_queryParam, $paramName . '=') === 0) {
                $_queryParam = explode('=', $_queryParam);
                $_queryParam[1] = isset($_queryParam[1]) ? explode(',', $_queryParam[1]) : [];
                $_queryParam[1] = array_filter(array_unique(array_merge($_queryParam[1], $params)));
                $_queryParam[1] = $_queryParam[1] ? implode(',', $_queryParam[1]) : '';
                $_queryParam = implode('=', $_queryParam);
                if ($_queryParam) {
                    $_query = implode('&', $_query);
                    $url = $_url;
                    $url .= $_query ? '?' . $_query : '';
                    $url .= $_fragment ? '#' . $_fragment : '';
                }
                break;
            }
        }

        return $url;
    }

    /**
     * @method refresh_speedsize_client_id_status
     * @return bool $speedsize_client_id_status
     */
    public static function refresh_speedsize_client_id_status()
    {
        $speedsize_client_id_status = SpeedSize_API::get_speedsize_client_status(SpeedSize_Config::get_speedsize_client_id(), true);
        if ($speedsize_client_id_status) {
            SpeedSize_Config::set_speedsize_client_id_active(true);
        } else {
            SpeedSize_Config::set_speedsize_client_id_active(false);
        }
        return $speedsize_client_id_status;
    }

    /**
     * @method refresh_speedsize_client_base_url
     * @return string|null $speedsize_client_base_url
     */
    public static function refresh_speedsize_client_base_url()
    {
        $speedsize_client_base_url = SpeedSize_API::get_speedsize_client_base_url(SpeedSize_Config::get_speedsize_client_id());
        SpeedSize_Config::set_speedsize_service_base_url($speedsize_client_base_url ?: null);
        return $speedsize_client_base_url;
    }

    /**
     * @method refresh_speedsize_client_allow_upscale
     * @return string|null $speedsize_client_allow_upscale
     */
    public static function refresh_speedsize_client_allow_upscale()
    {
        $speedsize_client_allow_upscale = SpeedSize_API::get_speedsize_client_allow_upscale(SpeedSize_Config::get_speedsize_client_id());
        SpeedSize_Config::set_speedsize_client_allow_upscale($speedsize_client_allow_upscale ?: null);
        return $speedsize_client_allow_upscale;
    }

    /**
     * @method refresh_speedsize_client_forbidden_paths
     * @return array|null $speedsize_client_forbidden_paths
     */
    public static function refresh_speedsize_client_forbidden_paths()
    {
        $speedsize_client_forbidden_paths = SpeedSize_API::get_speedsize_client_forbidden_paths(SpeedSize_Config::get_speedsize_client_id());
        SpeedSize_Config::set_speedsize_client_forbidden_paths(SpeedSize_Helper::speedsize_client_forbidden_paths_filter($speedsize_client_forbidden_paths, false) ?: null);
        return $speedsize_client_forbidden_paths;
    }

    /**
     * @method refresh_speedsize_client_whitelist_domains
     * @return array|null $speedsize_client_whitelist_domains
     */
    public static function refresh_speedsize_client_whitelist_domains()
    {
        $speedsize_client_whitelist_domains = SpeedSize_API::get_speedsize_client_whitelist_domains(SpeedSize_Config::get_speedsize_client_id());
        SpeedSize_Config::set_speedsize_client_whitelist_domains(SpeedSize_Helper::whitelist_domains_filter($speedsize_client_whitelist_domains, false) ?: null);
        return $speedsize_client_whitelist_domains;
    }

    /**
     * @method refresh_speedsize_client_forbidden_paths
     * @return array|null $speedsize_client_forbidden_paths
     */
    public static function refresh_speedsize_client_settings()
    {
        self::refresh_speedsize_client_id_status();
        self::refresh_speedsize_client_base_url();
        self::refresh_speedsize_client_allow_upscale();
        self::refresh_speedsize_client_forbidden_paths();
        self::refresh_speedsize_client_whitelist_domains();
    }

    /**
     * Wraps the original WP site_url()
     * @param string      $path   Optional. Path relative to the site URL. Default empty.
     * @param string|null $scheme Optional. Scheme to give the site URL context. See set_url_scheme().
     * @return string Site URL link with optional path appended.
     */
    public static function site_url($path = '', $scheme = null)
    {
        if (substr($path, 0, 1) === '/') {
            return self::remove_path_from_url(site_url()) . $path;
        }
        return site_url($path, $scheme);
    }

    /**
     * Wraps the original WP home_url()
     * @param string      $path   Optional. Path relative to the home URL. Default empty.
     * @param string|null $scheme Optional. Scheme to give the home URL context. Accepts
     *                            'http', 'https', 'relative', 'rest', or null. Default null.
     * @return string Home URL link with optional path appended.
     */
    public static function home_url($path = '', $scheme = null)
    {
        if (substr($path, 0, 1) === '/') {
            return self::remove_path_from_url(home_url()) . $path;
        }
        return home_url($path, $scheme);
    }

    /**
     * @method remove_path_from_url
     * @param  string $url
     * @return string
     */
    public static function remove_path_from_url($url)
    {
        return preg_replace('/((?:^(?:(?:https?\:)?\/\/)?)[^\/]+)(.*)/mi', '$1', $url);
    }

    /**
     * @method extract_domain_from_url
     * @param  string $url
     * @return string
     */
    public static function extract_domain_from_url($url)
    {
        return preg_replace('/(?:^(?:(?:https?\:)?\/\/)?(?:www\.)?)|(?:(?:\/|\s).*$)/mi', '', $url);
    }

    /**
     * @method whitelist_domains_filter
     * @param  string                            $whitelist_domains_filter
     * @param  boolean                           $return_array
     * @return string|array
     */
    public static function whitelist_domains_filter($whitelist_domains_filter = '', $return_array = false)
    {
        if ($whitelist_domains_filter) {
            if (!is_array($whitelist_domains_filter)) {
                $whitelist_domains_filter = (array) \explode(',', (string) $whitelist_domains_filter);
            }

            $whitelist_domains_filter = array_map(function ($domain) {
                return trim(self::extract_domain_from_url(trim($domain)));
            }, $whitelist_domains_filter);
            $whitelist_domains_filter = array_unique(array_filter($whitelist_domains_filter));
        } else {
            $whitelist_domains_filter = [];
        }
        return $return_array ? $whitelist_domains_filter : implode(',', $whitelist_domains_filter);
    }

    /**
     * @method allowed_html_attributes_filter
     * @param  string                         $additional_allowed_attrs
     * @param  boolean                        $return_array
     * @return array
     */
    public static function allowed_html_attributes_filter($additional_allowed_attrs = '', $return_array = false)
    {
        if ($additional_allowed_attrs) {
            if (!is_array($additional_allowed_attrs)) {
                $additional_allowed_attrs = (array) \explode(',', (string) $additional_allowed_attrs);
            }

            $additional_allowed_attrs = array_map(function ($attr) {
                return trim(preg_replace('/^data\-/mi', '', trim($attr)));
            }, $additional_allowed_attrs);
            $additional_allowed_attrs = array_unique(array_filter($additional_allowed_attrs));
        } else {
            $additional_allowed_attrs = [];
        }
        return $return_array ? $additional_allowed_attrs : implode(',', $additional_allowed_attrs);
    }

    /**
     * @method css_files_parsing_excluded_keywords_filter
     * @param  string                                     $keywords
     * @param  boolean                                    $return_array
     * @return array
     */
    public static function css_files_parsing_excluded_keywords_filter($keywords = '', $return_array = false)
    {
        if ($keywords) {
            if (!is_array($keywords)) {
                $keywords = (array) \explode(',', (string) $keywords);
            }
            $keywords = array_unique(array_filter(array_map('trim', $keywords)));
        } else {
            $keywords = [];
        }
        return $return_array ? $keywords : implode(',', $keywords);
    }

    /**
     * @method speedsize_client_forbidden_paths_filter
     * @param  string                                  $paths
     * @param  boolean                                 $return_array
     * @return array
     */
    public static function speedsize_client_forbidden_paths_filter($paths = '', $return_array = false)
    {
        if ($paths) {
            if (!is_array($paths)) {
                $paths = (array) \explode(',', (string) $paths);
            }
            $paths = array_unique(array_filter(array_map('trim', $paths)));
        } else {
            $paths = [];
        }
        return $return_array ? $paths : implode(',', $paths);
    }

    /**
     * @return array Array of headers.
     */
    public static function get_speedsize_headers()
    {
        $headers = [];
        $headers['Accept-CH'] = 'Viewport-Width, Width, DPR';
        $headers['Feature-Policy'] = sprintf(
            'ch-viewport-width %s, ch-dpr %s, ch-width %s',
            SpeedSize_Config::get_speedsize_service_base_url(),
            SpeedSize_Config::get_speedsize_service_base_url(),
            SpeedSize_Config::get_speedsize_service_base_url()
        );
        return $headers;
    }
}
