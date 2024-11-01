<?php

/**
 * SpeedSize Image & Video AI-Optimizer - Worpress plugin
 *
 * @category Image Optimization / CDN / Page Speed & Performance
 * @package  speedsize
 * @author   SpeedSize (https://speedsize.com/)
 * @author   Developed by Pniel (Pini) Cohen | Trus (https://www.trus.co.il/)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * SpeedSize_Parser_AbstractParser.
 */
abstract class SpeedSize_Parser_AbstractParser
{
    /**
     * Cache expiration (seconds)
     * @var int
     */
    public const CACHE_EXPIRY = 604800; //1 Week

    /**
     * @var string
     */
    protected const UNTOUCHED_FLAG_NAME = 'untouched';

    protected const CSS_BACKGROUND_IMAGE_URLS_SEARCH_PATTERN = '/(background(?:\-image)?\s*:[^;\{\}]*(?:url\s*\(\s*))(?:(\"|\')?((?:(?!\2|\)).)+?)(\2)?)(\s*\))/msi';
    protected const HREF_ATTRIBUTE_SEARCH_PATTERN = '/(\shref\s*\=\s*)(?:(\"|\')((?:(?!\2).)+?)(\2))/msi';

    protected const INJECT_BY_ALLOWED_DOMAINS_PATTERN = '#(?<=^|[,\"\'\(\s\<\>\;\=])(?:(?:(?:https?\:)?\\\?\/\\\?\/)(?:www\.)?(?:%s)\\\?\/|\\\?\/[\w\-]|\.\.?\\\?\/)[^,\"\'\)\<\>\?\=]+\.(?:(%s)|(%s))(?:\?[^,\"\'\)\s\<\>\?]*)?(?=$|[\,\"\'\(\)\s\<\>\;\=\&])#msi';

    protected const SUPPORTED_MEDIA_EXTENSION_URL_OR_PATH_PATTERN = '#^((?:(?:https?\:)?\\\?\/\\\?\/)|\\\?\/)?[^\"\'\(\)\<\>\{\}\=\?]+\.(?:%s)(?:\?[^\"\'\)\s\<\>\{\}]*)?$#msi';
    protected const LINK_CSS_HREF_BY_ALLOWED_DOMAINS_SEARCH_PATTERN = '/(<\s*link\s[^>]*?href\s*\=\s*)(\"|\')((?:(?:(?:https?\:)?\/\/)(?:www\.)?(?:%s)\/|\/[\w\-])[^\>\?]+?\.css(?:\?[^,\"\'\)\s\<\>]*)?)(\2)([^>]*?>)/msi';

    /**
     * @method get_all_allowed_domains_pattern
     * @param  bool         $refresh    Skip object cache.
     * @return string
     */
    protected static function get_all_allowed_domains_pattern($refresh = false)
    {
        $pattern = wp_cache_get('speedsize_all_allowed_domains_pattern', 'speedsize');
        if (!$pattern || $refresh) {
            $pattern = implode(
                '|',
                array_map(function ($domain) {
                    return preg_quote($domain, '/');
                }, SpeedSize_Config::get_all_allowed_domains())
            );
            wp_cache_set('speedsize_all_allowed_domains_pattern', $pattern, 'speedsize');
        }
        return $pattern;
    }

    /**
     * @method get_inject_by_allowed_domains_pattern
     * @param  bool    $refresh    Skip object cache.
     * @return string
     */
    protected static function get_inject_by_allowed_domains_pattern($refresh = false)
    {
        return sprintf(
            self::INJECT_BY_ALLOWED_DOMAINS_PATTERN,
            self::get_all_allowed_domains_pattern($refresh),
            SpeedSize_Config::get_allowed_image_file_extensions(),
            SpeedSize_Config::get_allowed_video_file_extensions()
        );
    }

    /**
     * @method get_supported_media_extension_url_or_path_pattern
     * @return string
     */
    protected static function get_supported_media_extension_url_or_path_pattern()
    {
        return sprintf(
            self::SUPPORTED_MEDIA_EXTENSION_URL_OR_PATH_PATTERN,
            SpeedSize_Config::get_allowed_image_file_extensions() . '|' . SpeedSize_Config::get_allowed_video_file_extensions()
        );
    }

    /**
     * @method get_search_link_css_href_by_allowed_domains_pattern
     * @param  bool    $refresh    Skip object cache.
     * @return string
     */
    protected static function get_search_link_css_href_by_allowed_domains_pattern($refresh = false)
    {
        return sprintf(self::LINK_CSS_HREF_BY_ALLOWED_DOMAINS_SEARCH_PATTERN, self::get_all_allowed_domains_pattern($refresh));
    }

    /**
     * @method convert_css_relative_urls_to_full_urls
     * @param  string $url
     * @param  string $url_dirname
     * @return string|false
     */
    protected static function convert_relative_url_to_full_url($url, $url_dirname = null)
    {
        /*$json_decoded_url = json_decode("\"{$url}\"");
        if ($json_decoded_url !== $url) {
            $url = $json_decoded_url;
        } else {
            $json_decoded_url = false;
        }*/

        //Treat up levels dir paths
        preg_match('#^((?:\.\.\/)+)[^\.\/]+.*#msi', $url, $relativeLevels);
        if (!empty($relativeLevels[1])) {
            if ($url_dirname) {
                $full_url = rtrim(dirname($url_dirname, substr_count($relativeLevels[1], '/')), '/') . '/' . substr($url, strlen($relativeLevels[1]));
            } else {
                return false;
            }
            if (substr($full_url, 0, 1) !== '/' && filter_var($full_url, FILTER_VALIDATE_URL) === false) {
                return false;
            }
            $url = $full_url;
        }
        //Treat ./ as dir level
        $url = preg_replace('#^\.\/#msi', '', $url);
        //Treat dir level path
        if ($url_dirname && !preg_match('#^(?:https?\:)?\/(\/)?[\w\-\_]#msi', $url)) {
            $url = rtrim($url_dirname, '/') . '/' . $url;
        }
        //If relative root path
        if (preg_match('#^\\\?\/[\w\-\_]#ms', $url)) {
            //If known WP dir or existing file
            preg_match('#^(.*?)(\\\?\/wp\-(?:admin|content|includes)\\\?\/.*)#ms', $url, $is_known_path);
            if (
                (!empty($is_known_path[2]) && empty($is_known_path[1])) ||
                file_exists(ABSPATH . ltrim($url)) ||
                (!empty($is_known_path[1]) && file_exists(ABSPATH . ltrim($is_known_path[2])))
            ) {
                $url = SpeedSize_Helper::home_url($url);
            } else {
                return false;
            }
        }

        //Json encode if decoded
        /*if ($json_decoded_url) {
            $url = trim(json_encode($url), '"');
        }*/

        //Return converted
        return $url;
    }

    /**
     * @method convert_css_relative_urls_to_full_urls
     * @param  string $content
     * @param  string $url_dirname
     * @return string
     */
    protected static function convert_css_relative_urls_to_full_urls($content, $url_dirname = null)
    {
        return preg_replace_callback(
            self::CSS_BACKGROUND_IMAGE_URLS_SEARCH_PATTERN,
            function ($url_matches) use ($url_dirname) {
                if (!preg_match(self::get_supported_media_extension_url_or_path_pattern(), $url_matches[3])) {
                    return $url_matches[0];
                }
                //If json encoded:
                $json_decoded_url = json_decode("\"{$url_matches[3]}\"");
                if ($json_decoded_url !== $url_matches[3]) {
                    $url_matches[3] = $json_decoded_url;
                } else {
                    $json_decoded_url = false;
                }
                if (!($url_matches[3] = self::convert_relative_url_to_full_url($url_matches[3], $url_dirname))) {
                    return $url_matches[0];
                }
                //Json encode if decoded:
                if ($json_decoded_url) {
                    $url_matches[3] = trim(json_encode($url_matches[3]), '"');
                }
                //Return processed:
                array_shift($url_matches);
                return implode("", $url_matches);
            },
            $content
        );
    }

    /**
     * @method inject_by_allowed_domains
     * @param  string $content
     * @param  string $url_dirname
     * @return string
     */
    protected static function inject_by_allowed_domains($content, $url_dirname = null)
    {
        if ($url_dirname && !in_array(SpeedSize_Helper::extract_domain_from_url($url_dirname), SpeedSize_Config::get_basic_site_allowed_domains())) {
            $url_dirname = null;
        } elseif (!$url_dirname && !empty($_SERVER['REQUEST_URI'])) {
            $url_dirname = dirname($_SERVER['REQUEST_URI']);
        }

        $content = self::convert_css_relative_urls_to_full_urls($content, $url_dirname);

        return preg_replace_callback(
            self::get_inject_by_allowed_domains_pattern(),
            function ($url_matches) use ($url_dirname) {
                //Take a copy of the initial URL value:
                $url = $url_matches[0];
                //If json encoded:
                $json_decoded_url = json_decode("\"{$url}\"");
                if ($json_decoded_url !== $url) {
                    $url = $json_decoded_url;
                } else {
                    $json_decoded_url = false;
                }
                //Check if filter exdluded:
                if (apply_filters('speedsize_prefix_url_excluded', false, $url)) {
                    //Return untouched
                    return $url_matches[0];
                }
                //If relative path (and under the WP dirs):
                if (!($url = self::convert_relative_url_to_full_url($url, $url_dirname))) {
                    //If false - return untouched
                    return $url_matches[0];
                }
                //Add CDN prefix and params if needed:
                $params = [];
                $file_type = !empty($url_matches[1]) ? 'image' : 'video';
                if (
                    SpeedSize_Config::is_parser_image_size_params_enabled() &&
                    ($image = SpeedSize_Helper::separate_sizes_from_image_url($url)) &&
                    !empty($image['width'])
                ) {
                    $params[] = "w_{$image['width']}";
                    if (!empty($image['height']) && !empty($image['crop'])) {
                        $params[] = "h_{$image['height']}";
                    }
                    $url = SpeedSize_Helper::prefix_url($image['url'], $params, $file_type);
                } else {
                    $url = SpeedSize_Helper::prefix_url($url, $params, $file_type);
                }
                //Json encode if decoded:
                if ($json_decoded_url) {
                    $url = trim(json_encode($url), '"');
                }
                //Finally - return the processed URL:
                return $url;
            },
            $content
        );
    }

    /**
     * @method process
     * @param  string $content
     * @return string
     */
    abstract public static function process($content);

    /**
     * @method get_speedsize_cache_path
     * @param  string $path
     * @return string
     */
    public static function get_speedsize_cache_path($path = '')
    {
        return trailingslashit(wp_upload_dir()['basedir']) . 'speedsize/cache' . ($path ? '/' . $path : '');
    }

    /**
     * @method get_speedsize_cache_url
     * @param  string $path
     * @return string
     */
    public static function get_speedsize_cache_url($path = '')
    {
        return trailingslashit(wp_upload_dir()['baseurl']) . 'speedsize/cache' . ($path ? '/' . $path : '');
    }

    /**
     * @method has_untouched_flag_filepath
     * @param  string $cache_file_path
     */
    protected static function has_untouched_flag_filepath($cache_file_path)
    {
        return file_exists($cache_file_path . '.' . self::UNTOUCHED_FLAG_NAME);
    }

    /**
     * @method flag_filepath_as_untouched
     * @param  string $cache_file_path
     */
    protected static function flag_filepath_as_untouched($cache_file_path)
    {
        file_put_contents($cache_file_path, '');
        file_put_contents($cache_file_path . '.' . self::UNTOUCHED_FLAG_NAME, '');
    }

    /**
     * @method unflag_filepath_as_untouched
     * @param  string $cache_file_path
     */
    protected static function unflag_filepath_as_untouched($cache_file_path)
    {
        if (file_exists($cache_file_path . '.' . self::UNTOUCHED_FLAG_NAME)) {
            unlink($cache_file_path . '.' . self::UNTOUCHED_FLAG_NAME);
        }
    }
}
