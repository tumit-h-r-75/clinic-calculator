<?php
defined('ABSPATH') || exit;

class Derma_ROI_Database {
    public static function create_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'derma_roi_calculations';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            calculator_id bigint(20) unsigned NOT NULL DEFAULT 0,
            clinic_name varchar(255) NOT NULL DEFAULT '',
            treatments_per_day int unsigned NOT NULL DEFAULT 0,
            price_per_session decimal(10,2) NOT NULL DEFAULT 0.00,
            material_cost_per_session decimal(10,2) NOT NULL DEFAULT 0.00,
            days_per_month int unsigned NOT NULL DEFAULT 0,
            lease_cost decimal(10,2) NOT NULL DEFAULT 0.00,
            gross_revenue decimal(12,2) NOT NULL DEFAULT 0.00,
            net_profit decimal(12,2) NOT NULL DEFAULT 0.00,
            roi_value decimal(10,2) NOT NULL DEFAULT 0.00,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY calculator_id (calculator_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
