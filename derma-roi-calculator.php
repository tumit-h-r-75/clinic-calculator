<?php
/*
Plugin Name: DermaScope ROI Calculator
Plugin URI: https://github.com/tumit-h-r-75/clinic-calculator
Description: Interactive ROI calculator for dermatology clinics with custom post type and shortcode.
Version: 1.0.0
Author: Tumit
Author URI: https://my-protfolio-tumit.web.app
License: GPL v2 or later
License URI: https://github.com/tumit-h-r-75/clinic-calculator
Text Domain: derma-roi-calculator
Domain Path: /languages
Requires at least: 5.0
Requires PHP: 7.4
*/

defined('ABSPATH') || exit;

define('DERMA_ROI_VERSION', '1.0.0');
define('DERMA_ROI_PLUGIN_FILE', __FILE__);
define('DERMA_ROI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DERMA_ROI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DERMA_ROI_ASSETS_URL', DERMA_ROI_PLUGIN_URL . 'public/assets');

require_once DERMA_ROI_PLUGIN_DIR . 'includes/class-post-type.php';
require_once DERMA_ROI_PLUGIN_DIR . 'includes/class-meta-boxes.php';
require_once DERMA_ROI_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once DERMA_ROI_PLUGIN_DIR . 'includes/class-database.php';

final class Derma_ROI_Calculator {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(DERMA_ROI_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(DERMA_ROI_PLUGIN_FILE, array($this, 'deactivate'));

        Derma_ROI_Post_Type::register();
        Derma_ROI_Meta_Boxes::init();
        Derma_ROI_Shortcode::register();

        add_action('init', array($this, 'load_textdomain'));
        add_action('save_post_calculator', array($this, 'save_calculator'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'derma-roi-calculator',
            false,
            dirname(plugin_basename(DERMA_ROI_PLUGIN_FILE)) . '/languages'
        );
    }

    public function admin_scripts($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || 'calculator' !== $screen->post_type) {
            return;
        }

        wp_enqueue_style(
            'derma-roi-admin',
            DERMA_ROI_ASSETS_URL . '/css/admin.css',
            array(),
            DERMA_ROI_VERSION
        );

        wp_enqueue_script(
            'derma-roi-admin',
            DERMA_ROI_ASSETS_URL . '/js/admin.js',
            array(),
            DERMA_ROI_VERSION,
            true
        );
    }

    public function frontend_scripts() {
        wp_register_style(
            'derma-roi-calculator',
            DERMA_ROI_ASSETS_URL . '/css/calculator.css',
            array(),
            DERMA_ROI_VERSION
        );

        wp_register_script(
            'derma-roi-calculator',
            DERMA_ROI_ASSETS_URL . '/js/calculator.js',
            array(),
            DERMA_ROI_VERSION,
            true
        );
    }

    public function save_calculator($post_id, $post) {
        if (!isset($_POST['derma_roi_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['derma_roi_nonce'])), 'derma_roi_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (!$post || 'calculator' !== $post->post_type) {
            return;
        }

        $text_fields = array(
            'clinic_name',
            'lease_option_1_name',
            'lease_option_2_name',
        );

        foreach ($text_fields as $field) {
            $value = isset($_POST[$field]) ? sanitize_text_field(wp_unslash($_POST[$field])) : '';
            update_post_meta($post_id, $field, $value);
        }

        $number_fields = array(
            'default_treatments_per_day',
            'min_price',
            'max_price',
            'default_price',
            'min_material_cost',
            'max_material_cost',
            'default_material',
            'min_days_per_month',
            'max_days_per_month',
            'lease_option_1_cost',
            'lease_option_2_cost',
        );

        foreach ($number_fields as $field) {
            $value = isset($_POST[$field]) ? absint(wp_unslash($_POST[$field])) : 0;
            update_post_meta($post_id, $field, $value);
        }

        if (!get_post_meta($post_id, 'shortcode_code', true)) {
            update_post_meta($post_id, 'shortcode_code', self::generate_shortcode_code($post_id));
        }
    }

    private static function generate_shortcode_code($post_id) {
        return 'derma_calculator_' . absint($post_id) . '_' . substr(wp_hash($post_id . microtime()), 0, 6);
    }

    public function activate() {
        Derma_ROI_Post_Type::register_post_type();
        Derma_ROI_Database::create_tables();
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }
}

function derma_roi_calculator() {
    return Derma_ROI_Calculator::get_instance();
}

derma_roi_calculator();
