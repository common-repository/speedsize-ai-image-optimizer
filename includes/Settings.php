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
 * SpeedSize_Settings class.
 */
class SpeedSize_Settings
{
    public static function init()
    {
        register_setting('speedsize', 'speedsize_enabled', 'SpeedSize_Settings::speedsize_settings_validate_yesno');
        register_setting('speedsize', 'speedsize_client_id', 'SpeedSize_Settings::speedsize_settings_validate_client_id');
        register_setting('speedsize', 'speedsize_js_snippet_enabled', 'SpeedSize_Settings::speedsize_settings_validate_yesno');
        register_setting('speedsize', 'speedsize_mute_all_videos', 'SpeedSize_Settings::speedsize_settings_validate_yesno');
        register_setting('speedsize', 'speedsize_size_params_enabled', 'SpeedSize_Settings::speedsize_settings_validate_yesno');
        register_setting('speedsize', 'speedsize_realtime_parsing_enabled', 'SpeedSize_Settings::speedsize_settings_validate_yesno');
        register_setting('speedsize', 'speedsize_parser_image_size_params_enabled', 'SpeedSize_Settings::speedsize_settings_validate_yesno');
        register_setting('speedsize', 'speedsize_css_files_parsing_enabled', 'SpeedSize_Settings::speedsize_settings_validate_yesno');
        register_setting('speedsize', 'speedsize_css_files_parsing_excluded_keywords', 'SpeedSize_Settings::speedsize_settings_validate_css_files_parsing_excluded_keywords');
        register_setting('speedsize', 'speedsize_keep_https_scheme_on_wrapped_media_urls', 'SpeedSize_Settings::speedsize_settings_validate_yesno');
        register_setting('speedsize', 'speedsize_disable_processor_filters', 'SpeedSize_Settings::speedsize_settings_validate_yesno');

        add_settings_section(
            'speedsize_settings_section',
            __('Basic Settings', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_section_callback',
            'speedsize'
        );

        add_settings_field(
            'speedsize_enabled',
            __('Enable Neuroscience Media Optimization', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_settings_section',
            [
                'label_for'         => 'speedsize_enabled',
                'class'             => 'speedsize_row',
            ]
        );

        add_settings_field(
            'speedsize_client_id',
            __('SpeedSize Client ID', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_settings_section',
            [
                'label_for'         => 'speedsize_client_id',
                'class'             => 'speedsize_row',
                //'description'       => sprintf(__('Don’t have a SpeedSize ID yet? Contact us: %s', 'speedsize'), '<a href="mailto:support@speedsize.com?subject=Request for ClientID from WordPress">support@speedsize.com</a>'),
            ]
        );

        add_settings_field(
            'speedsize_js_snippet_enabled', // New field
            __('Enable SpeedSize Additional JS Solution', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_settings_section',
            [
                'label_for'         => 'speedsize_js_snippet_enabled',
                'class'             => 'speedsize_row',
                'description'       => __('When enabled, SpeedSize\'s JS snippet will be loaded on the front-end, allowing SpeedSize to optimize dynamically loaded media URLs as well.', 'speedsize'),
            ]
        );

        add_settings_field(
            'speedsize_mute_all_videos',
            __('Mute All Videos', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_settings_section',
            [
                'label_for'         => 'speedsize_mute_all_videos',
                'class'             => 'speedsize_row',
                'description'       => __('Add a "v_muted" SpeedSize param to all wrapped video URLs', 'speedsize'),
            ]
        );

        add_settings_section(
            'speedsize_advanced_settings_section',
            __('Advanced Settings', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_section_callback',
            'speedsize'
        );

        add_settings_field(
            'speedsize_size_params_enabled',
            __('Use SpeedSize Size Params (Resize on SpeedSize)', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_advanced_settings_section',
            [
                'label_for'         => 'speedsize_size_params_enabled',
                'class'             => 'speedsize_row',
                'description'       => __('For optimal results, it\'s highly recommended setting this to "Yes" and let SpeedSize deal with the image resizing process, but you may still disable it if needed.', 'speedsize'),
            ]
        );

        add_settings_field(
            'speedsize_realtime_parsing_enabled',
            __('Enable Real-Time HTML Parsing', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_advanced_settings_section',
            [
                'label_for'         => 'speedsize_realtime_parsing_enabled',
                'class'             => 'speedsize_row',
                'description'       => __('This option is enabled by default in order to increase our support for a variety of themes and plugins, yet, you may disable it if everything seems to works the same without it on your website.', 'speedsize'),
            ]
        );

        add_settings_field(
            'speedsize_parser_image_size_params_enabled',
            __('Use SpeedSize Size Params on HTML Parser', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_advanced_settings_section',
            [
                'label_for'         => 'speedsize_parser_image_size_params_enabled',
                'class'             => 'speedsize_row',
                'description'       => __('When enabled (alongside "Enable Real-Time HTML Parsing"), our HTML parser will try to extract the image size from the image URL (this may be disabled only when using SpeedSize alongside other plugins/scripts that are changing the normal image file names on WP).', 'speedsize'),
            ]
        );

        add_settings_field(
            'speedsize_css_files_parsing_enabled',
            __('Enable SpeedSize CSS files Parsing', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_advanced_settings_section',
            [
                'label_for'         => 'speedsize_css_files_parsing_enabled',
                'class'             => 'speedsize_row',
                'description'       => __('When enabled, SpeedSize will try to wrap media URLs on loaded CSS files as well. You may enable this option if your website loads images from CSS files (e.g., background images).<br>
                    [!] Note: After enabling this option, a new button will appear on this page, "Clear CSS Parser Cache". This button will allow you to clear the cached version of the processed CSS files, after deploying changes (Don\'t forget to clear other caches after that).<br>
                    *By default it\'ll expire after a week or when the css file URL changes (e.g., when changing the URL version param).', 'speedsize'),
            ]
        );

        add_settings_field(
            'speedsize_css_files_parsing_excluded_keywords',
            __('Excluded CSS files', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_advanced_settings_section',
            [
                'label_for'         => 'speedsize_css_files_parsing_excluded_keywords',
                'class'             => 'speedsize_row',
                'description'       => __('A list of comma separated CSS filenames (or URL keywords) that will be excluded from CSS parsing (e.g., "jquery,bootstrap,icons,fonts")', 'speedsize'),
                'placeholder'       => sprintf(__('e.g., %s', 'speedsize'), SpeedSize_Config::SPEEDSIZE_CONFIG_DEFAULTS['speedsize_css_files_parsing_excluded_keywords']),
            ]
        );

        add_settings_field(
            'speedsize_keep_https_scheme_on_wrapped_media_urls',
            __('Keep HTTPS Scheme on Wrapped Media URLs', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_advanced_settings_section',
            [
                'label_for'         => 'speedsize_keep_https_scheme_on_wrapped_media_urls',
                'class'             => 'speedsize_row',
                'description'       => __('When enabled, wrapped media URLs will have an HTTPS scheme, leading to URLs with double occurrences of "https://" (e.g., "https://{SPEEDSIZE-CDN-PREFIX}/https://..."), otherwise, it\'ll be removed and the wrapped URLs would start from the domain (recommended).', 'speedsize'),
            ]
        );

        add_settings_field(
            'speedsize_disable_processor_filters',
            __('Rely *ONLY* on Real-Time HTML Parsing', 'speedsize'),
            'SpeedSize_Settings::speedsize_settings_fields',
            'speedsize',
            'speedsize_advanced_settings_section',
            [
                'label_for'         => 'speedsize_disable_processor_filters',
                'class'             => 'speedsize_row',
                'description'       => __('When set to "Yes", only real-time HTML parsing will be used for applying SpeedSize\'s CDN URL (while disabling other internal processor filters).<br>
                    [!] Note: Normally, this should be kept as "No", but in some cases it may resolve some types local conflicts (e.g., when using multiple plugins for images processing, etc).', 'speedsize'),
            ]
        );

        // Clear cache action:
        add_action('admin_post_speedsize_clear_css_cache', 'SpeedSize_Settings::speedsize_clear_css_cache_action');
    }

    public static function speedsize_clear_css_cache_action()
    {
        $redirect_url = admin_url('admin.php?page=speedsize-settings&speedsize-clear-css-cache-success');
        try {
            if (!current_user_can('manage_options')) {
                throw new \Exception("You don't have permission to access this page!");
            }
            SpeedSize_Parser_Css::clear_cache();
        } catch (\Exception $e) {
            $redirect_url = admin_url('admin.php?page=speedsize-settings&speedsize-clear-css-cache-error=' . base64_encode($e->getMessage()));
        }
        wp_redirect($redirect_url);
        exit;
    }

    public static function speedsize_settings_section_callback($args)
    {
        echo '<hr>';
    }

    /**
     * @param array $args
     */
    public static function speedsize_settings_fields($args)
    {
        switch ($args['label_for']) {
            case 'speedsize_enabled':
            case 'speedsize_size_params_enabled':
            case 'speedsize_realtime_parsing_enabled':
            case 'speedsize_parser_image_size_params_enabled':
            case 'speedsize_css_files_parsing_enabled':
            case 'speedsize_keep_https_scheme_on_wrapped_media_urls':
            case 'speedsize_disable_processor_filters':
            case 'speedsize_mute_all_videos':
            case 'speedsize_js_snippet_enabled':
                $currVal = SpeedSize_Config::get_option($args['label_for'], SpeedSize_Config::SPEEDSIZE_CONFIG_DEFAULTS[$args['label_for']]);
?>
                <select id="<?php esc_attr_e($args['label_for']); ?>" name="<?php esc_attr_e($args['label_for']); ?>">
                    <option value="yes" <?php selected($currVal, 'yes', true); ?>>
                        <?php esc_html_e('Yes', 'speedsize'); ?>
                    </option>
                    <option value="no" <?php selected($currVal, 'no', true); ?>>
                        <?php esc_html_e('No', 'speedsize'); ?>
                    </option>
                </select>
            <?php
                break;
            case 'speedsize_client_id':
                $speedsizeClientId = SpeedSize_Config::get_speedsize_client_id();
                $speedsizeClientIdActive = SpeedSize_Config::get_speedsize_client_id_active();
                $speedsizeClientAllowUpscale = strtoupper(trim((string)SpeedSize_Config::get_speedsize_client_allow_upscale()));
                $speedsizeClientForbiddenPaths = SpeedSize_Config::get_speedsize_client_forbidden_paths(true);
                $speedsizeClientWhitelistDomains = SpeedSize_Config::get_all_allowed_domains();
            ?>
                <input type="text" id="<?php esc_attr_e($args['label_for']); ?>" name="<?php esc_attr_e($args['label_for']); ?>" value="<?php esc_attr_e($speedsizeClientId) ?>" class="regular-text ltr" />
                <?php if (!empty($speedsizeClientId)): ?>
                    <span style="display:inline-block;padding:0 0.6em;color:<?php esc_attr_e($speedsizeClientIdActive ? '#14c514' : '#ff2828'); ?>;">
                        <?php esc_html_e(($speedsizeClientIdActive ? 'Active' : 'Inactive'), 'speedsize') ?>
                    </span>
                    <?php if ($speedsizeClientIdActive): ?>
                        <p class="description">
                            <?php esc_html_e(sprintf('- CDN Base URL: %s', SpeedSize_Config::get_speedsize_service_base_url()), 'speedsize'); ?>
                            <?php if ($speedsizeClientAllowUpscale && $speedsizeClientAllowUpscale !== 'OFF'): ?>
                                <br>
                                <?php esc_html_e(sprintf('- SpeedSize Allow Upscale: %s', $speedsizeClientAllowUpscale), 'speedsize'); ?>
                            <?php endif; ?>
                            <?php if ($speedsizeClientWhitelistDomains): ?>
                                <br>
                                <?php esc_html_e(sprintf('- SpeedSize Whitelist Domains: %s', implode(', ', $speedsizeClientWhitelistDomains)), 'speedsize'); ?>
                            <?php endif; ?>
                            <?php if ($speedsizeClientForbiddenPaths): ?>
                                <br>
                                <?php esc_html_e(sprintf('- SpeedSize Forbidden Paths: %s', implode(', ', $speedsizeClientForbiddenPaths)), 'speedsize'); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <p class="description">
                        <?php if (!$speedsizeClientId): ?>
                            <?php echo sprintf(__('Don’t have a SpeedSize ID yet? Contact us: %s', 'speedsize'), '<a href="mailto:support@speedsize.com?subject=Request for ClientID from WordPress">support@speedsize.com</a>'); ?>
                        <?php else: ?>
                            <?php echo sprintf(__('Need support? Contact us: %s', 'speedsize'), '<a href="mailto:support@speedsize.com?subject=Request for support from WordPress">support@speedsize.com</a>'); ?>
                        <?php endif; ?>
                    </p>
                <?php endif;
                break;
            case 'speedsize_css_files_parsing_excluded_keywords':
                $currVal = SpeedSize_Config::get_option($args['label_for'], SpeedSize_Config::SPEEDSIZE_CONFIG_DEFAULTS[$args['label_for']]);
                $placeholder = !empty($args['placeholder']) ? $args['placeholder'] : '';
                ?>
                <input type="text" id="<?php esc_attr_e($args['label_for']); ?>" name="<?php esc_attr_e($args['label_for']); ?>" value="<?php esc_attr_e($currVal) ?>" placeholder="<?php esc_attr_e($placeholder) ?>" class="regular-text ltr" />
            <?php
                break;
        }
        // Field Description:
        if (!empty($args['description'])): ?>
            <p class="description">
                <?php echo $args['description']; ?>
            </p>
        <?php endif;
    }

    /**
     * Renders the Admin UI
     */
    public static function render_admin_settings_page()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated']) && !get_settings_errors('speedsize_settings_errors')) {
            add_settings_error('speedsize_settings_success', 'speedsize_settings_saved', __('Settings Saved', 'speedsize'), 'success');
        }

        if (isset($_GET['speedsize-clear-css-cache-success'])) {
            add_settings_error('speedsize_settings_success', 'speedsize_cache_cleared', __('SpeedSize CSS cache successfully cleared', 'speedsize'), 'success');
        }

        if (isset($_GET['speedsize-clear-css-cache-error'])) {
            add_settings_error('speedsize_settings_errors', 'speedsize_cache_clear_error', sprintf(__('Error while trying to clear SpeedSize cache: %s', 'speedsize'), base64_decode(sanitize_text_field($_GET['speedsize-clear-css-cache-error']))), 'error');
        }

        settings_errors('speedsize_settings_success');
        settings_errors('speedsize_settings_errors');

        wp_cache_flush_group('speedsize');
        SpeedSize_Helper::refresh_speedsize_client_settings(); ?>

        <div class="wrap">
            <h1>
                <img src="<?php esc_attr_e(SPEEDSIZE_ASSETS_URL . 'images/speedsize-logo-black.svg'); ?>" style="height:20px;vertical-align:middle;">
                <span style="display:inline-block;padding:0 5px;"><?php esc_html_e(get_admin_page_title()); ?><span>
            </h1>
            <p>
                <?php esc_html_e('~90-99% smaller media, 100% of the visual quality.', 'speedsize'); ?>
                <i> <?php esc_html_e('Get Sharper & Faster', 'speedsize'); ?></i>
            </p>
            <hr>

            <?php if (SpeedSize_Config::is_css_parsing_enabled()): ?>
                <a class="button" href="admin-post.php?action=speedsize_clear_css_cache">Clear CSS Parser Cache</a>
                <hr>
            <?php endif; ?>

            <form name="speedsize_settings_form" action="options.php" method="post" onsubmit="return SpeedSizeValidateForm()">
                <?php settings_fields('speedsize'); ?>
                <?php do_settings_sections('speedsize'); ?>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
<?php
    }

    public static function speedsize_settings_validate_yesno($input)
    {
        if (!in_array($input, ['yes', 'no'])) {
            $input = 'no';
        }
        return $input;
    }

    public static function speedsize_settings_validate_client_id($input)
    {
        $input = !empty($input) ? trim((string)$input) : '';
        if ($input) {
            $speedsizeClientIdStatus = SpeedSize_API::get_speedsize_client_status($input, true);
            if ($input && !$speedsizeClientIdStatus) {
                add_settings_error('speedsize_settings_errors', 'speedsize_client_id', __("SpeedSize ID is invalid, inactive or doesn't exist. We can't save your current settings.", 'speedsize'), 'error');
                return SpeedSize_Config::get_speedsize_client_id();
            }
            SpeedSize_Helper::refresh_speedsize_client_settings();
        } else {
            SpeedSize_Config::set_speedsize_enabled(false);
            SpeedSize_Config::set_speedsize_client_id_active(false);
            SpeedSize_Config::set_speedsize_service_base_url(null);
            SpeedSize_Config::set_speedsize_client_allow_upscale(null);
            SpeedSize_Config::set_speedsize_client_forbidden_paths(null);
            SpeedSize_Config::set_speedsize_client_whitelist_domains(null);
        }

        return $input;
    }

    public static function speedsize_settings_validate_css_files_parsing_excluded_keywords($input)
    {
        if (empty($input)) {
            return '';
        }
        return SpeedSize_Helper::css_files_parsing_excluded_keywords_filter(trim((string)$input), false);
    }
}
