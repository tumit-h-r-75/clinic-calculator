<?php
defined('ABSPATH') || exit;

class Derma_ROI_Post_Type {
    public static function register() {
        add_action('init', array(__CLASS__, 'register_post_type'));
        add_filter('post_updated_messages', array(__CLASS__, 'updated_messages'));
    }

    public static function register_post_type() {
        $labels = array(
            'name'               => _x('Calculators', 'Post Type General Name', 'derma-roi-calculator'),
            'singular_name'      => _x('Calculator', 'Post Type Singular Name', 'derma-roi-calculator'),
            'menu_name'          => __('Calculators', 'derma-roi-calculator'),
            'name_admin_bar'     => __('Calculator', 'derma-roi-calculator'),
            'archives'           => __('Calculator Archives', 'derma-roi-calculator'),
            'attributes'         => __('Calculator Attributes', 'derma-roi-calculator'),
            'parent_item_colon'  => __('Parent Calculator:', 'derma-roi-calculator'),
            'all_items'          => __('All Calculators', 'derma-roi-calculator'),
            'add_new_item'       => __('Add New Calculator', 'derma-roi-calculator'),
            'add_new'            => __('Add New', 'derma-roi-calculator'),
            'new_item'           => __('New Calculator', 'derma-roi-calculator'),
            'edit_item'          => __('Edit Calculator', 'derma-roi-calculator'),
            'update_item'        => __('Update Calculator', 'derma-roi-calculator'),
            'view_item'          => __('View Calculator', 'derma-roi-calculator'),
            'view_items'         => __('View Calculators', 'derma-roi-calculator'),
            'search_items'       => __('Search Calculator', 'derma-roi-calculator'),
            'not_found'          => __('Not found', 'derma-roi-calculator'),
            'not_found_in_trash' => __('Not found in Trash', 'derma-roi-calculator'),
        );

        $args = array(
            'label'               => __('Calculator', 'derma-roi-calculator'),
            'description'         => __('ROI Calculator for dermatology clinics', 'derma-roi-calculator'),
            'labels'              => $labels,
            'supports'            => array('title', 'editor', 'custom-fields'),
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-chart-bar',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
        );

        register_post_type('calculator', $args);
    }

    public static function updated_messages($messages) {
        $post = get_post();

        $messages['calculator'] = array(
            0  => '',
            1  => __('Calculator updated.', 'derma-roi-calculator'),
            2  => __('Custom field updated.', 'derma-roi-calculator'),
            3  => __('Custom field deleted.', 'derma-roi-calculator'),
            4  => __('Calculator updated.', 'derma-roi-calculator'),
            5  => isset($_GET['revision']) ? sprintf(
                __('Calculator restored to revision from %s', 'derma-roi-calculator'),
                wp_post_revision_title(absint($_GET['revision']), false)
            ) : false,
            6  => __('Calculator published.', 'derma-roi-calculator'),
            7  => __('Calculator saved.', 'derma-roi-calculator'),
            8  => __('Calculator submitted.', 'derma-roi-calculator'),
            9  => $post ? sprintf(
                __('Calculator scheduled for: <strong>%1$s</strong>.', 'derma-roi-calculator'),
                date_i18n(__('M j, Y @ G:i', 'derma-roi-calculator'), strtotime($post->post_date))
            ) : __('Calculator scheduled.', 'derma-roi-calculator'),
            10 => __('Calculator draft updated.', 'derma-roi-calculator'),
        );

        return $messages;
    }
}
