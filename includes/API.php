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
 * SpeedSize_API.
 */
class SpeedSize_API
{
    /**
     * @method get_speedsize_client_info
     * @param  mixed    $speedsizeClientId
     * @param  bool     $refresh    Skip object cache.
     * @return array
     */
    protected static function get_speedsize_client_info($speedsizeClientId, $refresh = false)
    {
        $speedsizeClientId = (string) $speedsizeClientId;

        $info = wp_cache_get('speedsize_client_info_' . $speedsizeClientId, 'speedsize');
        if ($info && !$refresh) {
            return $info;
        }

        try {
            if ($speedsizeClientId) {
                $response = wp_remote_get(
                    SpeedSize_Config::get_speedsize_api_url("clients/{$speedsizeClientId}"),
                    [
                        'method' => 'GET',
                        'timeout'     => 120,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                    ]
                );

                if (is_wp_error($response)) {
                    throw new \Exception("[wp_error] " . $response->get_error_message());
                } else {
                    if ($response['body'] && is_string($response['body'])) {
                        $response['body'] = json_decode($response['body'], true);
                    }
                    if (isset($response['body']["clientId"]) && $response['body']["clientId"] === $speedsizeClientId) {
                        $info = (array) $response['body'];
                    }
                }
            }
        } catch (\Exception $e) {
            //Ignore exceptions
            $info = [];
        }

        wp_cache_set('speedsize_client_info_' . $speedsizeClientId, $info, 'speedsize', 60);

        return $info;
    }

    /**
     * @method get_speedsize_client_status
     * @param  mixed                          $speedsizeClientId
     * @param  boolean                        $returnIsActive
     * @param  bool                           $refresh    Skip object cache.
     * @return mixed
     */
    public static function get_speedsize_client_status($speedsizeClientId, $returnIsActive = false, $refresh = false)
    {
        try {
            $speedsizeClientId = (string) $speedsizeClientId;

            if ($speedsizeClientId) {
                $info = self::get_speedsize_client_info($speedsizeClientId, $refresh);
                if ($info && isset($info["status"])) {
                    return $returnIsActive ? $info["status"] === 'Active' : $info["status"];
                }
            }
        } catch (\Exception $e) {
            //Ignore exceptions
        }

        return $returnIsActive ? false : null;
    }

    /**
     * @method get_speedsize_client_base_url
     * @param  mixed                          $speedsizeClientId
     * @param  bool                           $refresh    Skip object cache.
     * @return string|null
     */
    public static function get_speedsize_client_base_url($speedsizeClientId, $refresh = false)
    {
        try {
            $speedsizeClientId = (string) $speedsizeClientId;

            if ($speedsizeClientId) {
                $info = self::get_speedsize_client_info($speedsizeClientId, $refresh);
                if ($info && !empty($info["config"]["mLinkBaseUrl"])) {
                    return $info["config"]["mLinkBaseUrl"];
                }
            }
        } catch (\Exception $e) {
            //Ignore exceptions
        }

        return null;
    }

    /**
     * @method get_speedsize_client_allow_upscale
     * @param  mixed                          $speedsizeClientId
     * @param  bool                           $refresh    Skip object cache.
     * @return string|null
     */
    public static function get_speedsize_client_allow_upscale($speedsizeClientId, $refresh = false)
    {
        try {
            $speedsizeClientId = (string) $speedsizeClientId;

            if ($speedsizeClientId) {
                $info = self::get_speedsize_client_info($speedsizeClientId, $refresh);
                if ($info && !empty($info["allowUpscale"])) {
                    return $info["allowUpscale"];
                }
            }
        } catch (\Exception $e) {
            //Ignore exceptions
        }

        return null;
    }

    /**
     * @method get_speedsize_client_forbidden_paths
     * @param  mixed                          $speedsizeClientId
     * @param  bool                           $refresh    Skip object cache.
     * @return array|null
     */
    public static function get_speedsize_client_forbidden_paths($speedsizeClientId, $refresh = false)
    {
        try {
            $speedsizeClientId = (string) $speedsizeClientId;

            if ($speedsizeClientId) {
                $info = self::get_speedsize_client_info($speedsizeClientId, $refresh);
                if ($info && !empty($info["config"]["forbiddenPaths"])) {
                    return $info["config"]["forbiddenPaths"];
                }
            }
        } catch (\Exception $e) {
            //Ignore exceptions
        }

        return null;
    }

    /**
     * @method get_speedsize_client_whitelist_domains
     * @param  mixed                          $speedsizeClientId
     * @param  bool                           $refresh    Skip object cache.
     * @return array|null
     */
    public static function get_speedsize_client_whitelist_domains($speedsizeClientId, $refresh = false)
    {
        try {
            $speedsizeClientId = (string) $speedsizeClientId;

            if ($speedsizeClientId) {
                $info = self::get_speedsize_client_info($speedsizeClientId, $refresh);
                $whitelist_domains = ($info && !empty($info["config"]["whitelistDomains"])) ? SpeedSize_Helper::whitelist_domains_filter($info["config"]["whitelistDomains"]) : null;
                if ($info && !empty($whitelist_domains)) {
                    return $whitelist_domains;
                }
            }
        } catch (\Exception $e) {
            //Ignore exceptions
        }

        return null;
    }
}
