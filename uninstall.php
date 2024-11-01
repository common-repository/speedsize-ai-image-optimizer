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

// if uninstall not called from WordPress exit
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (defined('WC_REMOVE_ALL_DATA') && true === WC_REMOVE_ALL_DATA) {
    // Delete options.
    $pluginOptions = [
        'speedsize_enabled',
        'speedsize_client_id',
        'speedsize_client_id_active',
        'speedsize_disabled_on_admin',
        'speedsize_enabled_on_admin',
        'speedsize_service_base_url',
        'speedsize_client_allow_upscale',
        'speedsize_client_whitelist_domains',
        'speedsize_client_forbidden_paths',
        'speedsize_js_snippet_enabled',
        'speedsize_size_params_enabled',
        'speedsize_realtime_parsing_enabled',
        'speedsize_parser_image_size_params_enabled',
        'speedsize_css_files_parsing_enabled',
        'speedsize_css_files_parsing_excluded_keywords',
        'speedsize_disable_processor_filters',
        'speedsize_additional_allowed_domains',
        'speedsize_allowed_html_attributes',
        'speedsize_keep_https_scheme_on_wrapped_media_urls',
        'speedsize_mute_all_videos',
    ];
    foreach ($pluginOptions as $optionName) {
        delete_option($optionName);
    }
}
