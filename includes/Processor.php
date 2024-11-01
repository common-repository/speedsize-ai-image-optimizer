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
 * SpeedSize_Processor.
 */
final class SpeedSize_Processor
{
    /**
     * Wrap attachment url
     * @method wrap_attachment_url
     * @param  mixed              $url
     * @param  mixed              $attachment_id
     * @return mixed
     */
    public static function init()
    {
        if (SpeedSize_Config::is_enabled() && SpeedSize_Config::is_processor_filters_enabled()) {
            add_filter('wp_get_attachment_url', 'SpeedSize_Processor::wrap_attachment_url', 9999, 2);
            add_filter('wp_get_attachment_image_src', 'SpeedSize_Processor::wrap_attachment_image_src', 9999, 4);
            add_filter('wp_calculate_image_srcset', 'SpeedSize_Processor::wrap_image_srcset', 9999, 5);
        }
    }

    /**
     * Wrap attachment url
     * @method wrap_attachment_url
     * @param  mixed              $url
     * @param  mixed              $attachment_id
     * @return mixed
     */
    public static function wrap_attachment_url($url, $attachment_id)
    {
        $cacheKey = 'speedsize_wrap_attachment_url_' . md5($url . $attachment_id);
        if (($cached = wp_cache_get($cacheKey, 'speedsize'))) {
            return $cached;
        }

        $file_type = SpeedSize_Helper::get_attachment_type($attachment_id);
        if (
            in_array($file_type, ['image', 'video']) &&
            !apply_filters('speedsize_prefix_url_excluded', false, $url)
        ) {
            $url = SpeedSize_Helper::prefix_url($url, [], $file_type);
        }

        wp_cache_set($cacheKey, $url, 'speedsize');

        return $url;
    }

    /**
     * Wrap attachment image src
     * @method wrap_attachment_image_src
     * @param  array                    $image
     *   $image[0] => Image URL, $image[1] => Image width, $image[2] => Image height, $image[3] => Image has been resized
     * @param  int                      $attachment_id
     * @param  mixed                    $size
     * @param  bool                     $icon
     * @return array
     */
    public static function wrap_attachment_image_src($image, $attachment_id, $size, $icon)
    {
        if (!empty($image[0])) {
            $cacheKey = 'speedsize_wrap_attachment_image_src_' . md5(json_encode(func_get_args()));
            if (($cached = wp_cache_get($cacheKey, 'speedsize'))) {
                return $cached;
            }

            $file_type = SpeedSize_Helper::get_attachment_type($attachment_id);
            if (
                in_array($file_type, ['image', 'video']) &&
                !apply_filters('speedsize_prefix_url_excluded', false, $image[0])
            ) {
                if (
                    SpeedSize_Config::is_speedsize_size_params_enabled() &&
                    !empty($image[1]) && // Has width
                    (
                        !empty($image[3]) || // Resized by WP
                        ($isBfiThumb = !empty($size['bfi_thumb'])) // Resized by BFI thumb (Elementor)
                    )
                ) {
                    $origURL = SpeedSize_Helper::get_attachment_original_image_url($attachment_id);
                    if ($image[0] !== $origURL || !empty($isBfiThumb)) {
                        $params = SpeedSize_Config::is_allowed_upscale() ? ['upscl'] : [];
                        $params[] = "w_{$image[1]}";
                        if (
                            !empty($image[2]) && // Has height
                            (SpeedSize_Config::is_size_crop_enabled($size) || !empty($size['crop']))
                        ) {
                            $params[] = "h_{$image[2]}";
                        }
                        $image[0] = SpeedSize_Helper::add_speedsize_url_params($origURL, $params);
                    } else {
                        $image[0] = SpeedSize_Helper::prefix_url($image[0], [], $file_type);
                    }
                } else {
                    $image[0] = SpeedSize_Helper::prefix_url($image[0], [], $file_type);
                }
            }

            wp_cache_set($cacheKey, $image, 'speedsize');
        }

        return $image;
    }

    /**
     * Wrap attachment image src
     * @method wrap_attachment_url
     * @param  mixed              $url
     * @param  mixed              $attachment_id
     * @return mixed
     */
    public static function wrap_image_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id)
    {
        $cacheKey = 'speedsize_wrap_image_srcset_' . md5(json_encode(func_get_args()));
        if (($cached = wp_cache_get($cacheKey, 'speedsize'))) {
            return $cached;
        }

        $file_type = SpeedSize_Helper::get_attachment_type($attachment_id);
        if (
            in_array($file_type, ['image', 'video']) &&
            !apply_filters('speedsize_prefix_url_excluded', false, $image_src)
        ) {
            $origURL = SpeedSize_Helper::get_attachment_original_image_url($attachment_id);
            $image_meta_sizes = [];
            if (!empty($image_meta['sizes'])) {
                foreach ($image_meta['sizes'] as $k => $size) {
                    $size['size_name'] = $k;
                    $meta_sizes[$size['file']] = $size;
                }
            }
            foreach ($sources as $k => $source) {
                if (SpeedSize_Config::is_speedsize_size_params_enabled() && $source['descriptor'] === 'w' && $source['value'] && $origURL) {
                    $params = SpeedSize_Config::is_allowed_upscale() ? ['upscl'] : [];
                    $params[] = "w_{$source['value']}";
                    $file = \basename($source['url']);
                    if (!empty($meta_sizes[$file]) && !empty($meta_sizes[$file]['height']) && SpeedSize_Config::is_size_crop_enabled($meta_sizes[$file]['size_name'])) {
                        $params[] = "h_{$meta_sizes[$file]['height']}";
                    }
                    $sources[$k]['url'] = SpeedSize_Helper::add_speedsize_url_params($origURL, $params);
                } else {
                    $sources[$k]['url'] = SpeedSize_Helper::prefix_url($source['url'], [], $file_type);
                }
            }
        }

        wp_cache_set($cacheKey, $sources, 'speedsize');

        return $sources;
    }
}
