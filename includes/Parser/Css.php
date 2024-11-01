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
 * SpeedSize_Parser_Css.
 */
final class SpeedSize_Parser_Css extends SpeedSize_Parser_AbstractParser
{
    /**
     * Can Process
     * @var bool
     */
    protected static $can_process;

    protected const REL_STYLESHEET_ATTRIBUTE_SEARCH_PATTERN = '/\srel\s*\=\s*(\"|\')\s*stylesheet\s*(\1)/msi';

    protected const All_QUOTES_OR_PARENTHESES_WRAPPED_PATHS_PATTERN = '/((?:import|url)\s*\(?\s*)(\(|\"|\')([^\2,\{\}\;\)\<\>\?\=]+\.[a-zA-Z]\w+(?:\?[^\2,\{\}\)\<\>\?]*)?)(\2|\))/msi';

    /**
     * Can process?
     * @method can_process
     * @param  bool   $refresh    Skip self cache.
     * @return bool
     */
    public static function can_process($refresh = false)
    {
        if (self::$can_process === null || $refresh) {
            if (
                SpeedSize_Config::is_css_parsing_enabled() &&
                SpeedSize_Config::is_enabled($refresh)
            ) {
                //Make sure the cache dir exists (create if not)
                wp_mkdir_p(self::get_speedsize_css_cache_path());
                self::$can_process = true;
            } else {
                self::$can_process = false;
            }
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
        return preg_replace_callback(
            self::get_search_link_css_href_by_allowed_domains_pattern(),
            function ($link_matches) {
                //Skip if missing a rel="stylesheet" attribute
                if (!preg_match(self::REL_STYLESHEET_ATTRIBUTE_SEARCH_PATTERN, $link_matches[0])) {
                    return $link_matches[0];
                }

                //Skip if href ($link_matches[3]) contains excluded keyword
                if (preg_match(self::get_excluded_keywords_pattern(), $link_matches[3])) {
                    return $link_matches[0];
                }

                //Skip if processed href is empty or untouched
                if (
                    !($new_href = self::get_processed_css_url($link_matches[3])) ||
                    $new_href === $link_matches[3]
                ) {
                    return $link_matches[0];
                }

                $orig_href = $link_matches[3];
                $link_matches[3] = $new_href; //Replace href with processed URL
                $link_matches[4] .= " data-speedsize={$link_matches[4]}processed{$link_matches[4]} data-original-href={$link_matches[4]}{$orig_href}{$link_matches[4]}"; //Flag link attribute

                array_shift($link_matches);
                return implode("", $link_matches);
            },
            $content
        );
    }

    /**
     * @method get_excluded_keywords_pattern
     * @param  bool         $refresh    Skip self cache.
     * @return string
     */
    protected static function get_excluded_keywords_pattern($refresh = false)
    {
        $pattern = wp_cache_get('speedsize_css_parser_excluded_keywords_pattern', 'speedsize');
        if (!$pattern || $refresh) {
            $keywords = SpeedSize_Config::get_css_files_parsing_excluded_keywords();
            $keywords[] = '/speedsize/cache/css/'; //Always exclude self cache;
            $keywords = implode(
                '|',
                array_map(function ($keyword) {
                    return preg_quote($keyword, '/');
                }, $keywords)
            );
            $pattern = sprintf('/%s/msi', $keywords);
            wp_cache_set('speedsize_css_parser_excluded_keywords_pattern', $pattern, 'speedsize');
        }
        return $pattern;
    }

    /**
     * @method get_processed_css_url
     * @param  string $url
     * @return mixed
     */
    public static function get_processed_css_url($url)
    {
        try {
            preg_match('/^(.*?)(\?.*)?$/msi', $url, $url_parts);
            $no_query_url = $url_parts[1];
            $query_string = !empty($url_parts[2]) ? $url_parts[2] : '';

            $basename = basename($no_query_url);
            if (substr($basename, -4) !== '.css') {
                $basename .= '.css';
            }

            $cache_file_name = md5($url) . "_{$basename}";
            $cache_file_path = self::get_speedsize_css_cache_path($cache_file_name);

            //Check for a cached version:
            if (
                file_exists($cache_file_path) &&
                (time() - filemtime($cache_file_path) <= self::CACHE_EXPIRY)
            ) {
                if (self::has_untouched_flag_filepath($cache_file_path)) {
                    //If untouched on last process, return the original URL:
                    return $url;
                }

                //Return the cached processed file URL:
                return self::get_speedsize_css_cache_url($cache_file_name . $query_string);
            }

            //= Process

            //Add scheme if missing:
            $_url = $url;
            if (substr($_url, 0, 2) === '//') {
                $_url = (is_ssl() ? 'https:' : 'http:') . $_url;
            }
            //Convert to full URL if needed:
            if (substr($_url, 0, 1) === '/') {
                $_url = SpeedSize_Helper::home_url($_url);
            }

            $response = wp_remote_get(
                $_url,
                [
                    'method' => 'GET',
                    'timeout'     => 120,
                    'headers' => [
                        'Accept' => 'text/css',
                    ],
                ]
            );

            if (is_wp_error($response)) {
                throw new \Exception("[wp_error] " . $response->get_error_message());
            }

            if ((int) $response['response']['code'] > 399) {
                throw new \Exception("[wp_remote_get] (code:{$response['response']['code']}) Couldn't load CSS URL: {$url}");
            }

            if (empty($response['body'])) {
                throw new \Exception("[wp_remote_get] Empty body for CSS URL: {$url}");
            }

            $url_dirname = \dirname($_url);
            $content = self::inject_by_allowed_domains($response['body'], $url_dirname);
            if (md5($content) === md5($response['body'])) {
                self::flag_filepath_as_untouched($cache_file_path);
                //If untouched, return the original URL:
                return $url;
            } else {
                //Prevent remaining relative paths from breaking and add a timestamp:
                $content = self::convert_all_relative_urls_to_full_urls($content, $url_dirname);
                $content .= "\n/*Processed-by-SpeedSize:" . date('Y-m-d_H:i:s') . "*/";
                //Save to cache dir:
                if (!file_put_contents($cache_file_path, $content)) {
                    throw new \Exception("[SpeedSize CSS parser] file_put_contents() failed for '{$cache_file_path}'");
                }
                self::unflag_filepath_as_untouched($cache_file_path);

                //Return the new processed file:
                return self::get_speedsize_css_cache_url($cache_file_name . $query_string);
            }
        } catch (\Exception $e) {
            self::flag_filepath_as_untouched($cache_file_path);
            //Ignore errors and proceed to return the original URL.
        }

        return $url;
    }

    /**
     * @method convert_all_relative_urls_to_full_urls
     * Make sure that relative references are not breaking after changing the main URL to cache dir
     * @param  string $content
     * @param  string|null $url_dirname
     * @return string
     */
    protected static function convert_all_relative_urls_to_full_urls($content, $url_dirname = null)
    {
        return preg_replace_callback(
            self::All_QUOTES_OR_PARENTHESES_WRAPPED_PATHS_PATTERN,
            function ($url_matches) use ($url_dirname) {
                $url_matches[3] = trim($url_matches[3]);
                //Skip full URLs or relative root paths:
                if (preg_match('#^(?:https?\:)?\/(?:\/)?#msi', $url_matches[3])) {
                    return $url_matches[0];
                }
                //Convert to full URL:
                if (!($url_matches[3] = self::convert_relative_url_to_full_url($url_matches[3], $url_dirname))) {
                    return $url_matches[0];
                }
                //Return processed:
                array_shift($url_matches);
                return implode("", $url_matches);
            },
            $content
        );
    }

    /**
     * @method get_speedsize_css_cache_path
     * @param  string $path
     * @return string
     */
    public static function get_speedsize_css_cache_path($path = '')
    {
        return parent::get_speedsize_cache_path('css' . ($path ? '/' . $path : ''));
    }

    /**
     * @method get_speedsize_css_cache_url
     * @param  string $path
     * @return string
     */
    public static function get_speedsize_css_cache_url($path = '')
    {
        return parent::get_speedsize_cache_url('css' . ($path ? '/' . $path : ''));
    }

    /**
     * @method clear_cache
     * @param  bool|int  $clear_only_expired Expiry
     * @return true|null
     */
    public static function clear_cache($clear_only_expired = false)
    {
        $cacheDir = self::get_speedsize_css_cache_path();
        if (file_exists($cacheDir)) {
            foreach (new DirectoryIterator($cacheDir) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                if (
                    $fileInfo->isFile() &&
                    (!$clear_only_expired || (time() - $fileInfo->getCTime() >= (int) $clear_only_expired))
                ) {
                    unlink($fileInfo->getRealPath());
                }
            }
        }
        return true;
    }

    /**
     * @method clear_expired_cache
     * @return true|null
     */
    public static function clear_expired_cache($multiply = 1)
    {
        return self::clear_cache(self::CACHE_EXPIRY * $multiply);
    }
}
