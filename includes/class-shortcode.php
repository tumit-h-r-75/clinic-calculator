<?php
defined('ABSPATH') || exit;

class Derma_ROI_Shortcode {
    public static function register() {
        add_shortcode('derma_calculator', array(__CLASS__, 'render'));
        add_shortcode('derma_calculator_price', array(__CLASS__, 'render_price'));
    }

    public static function render($atts) {
        $atts = shortcode_atts(array('id' => ''), $atts, 'derma_calculator');
        $shortcode_id = sanitize_key($atts['id']);

        if (!$shortcode_id) {
            return '<p class="derma-roi-error">' . esc_html__('Error: Calculator ID not provided.', 'derma-roi-calculator') . '</p>';
        }

        $post_id = self::get_calculator_id($shortcode_id);

        if (!$post_id) {
            return '<p class="derma-roi-error">' . esc_html__('Error: Calculator not found.', 'derma-roi-calculator') . '</p>';
        }

        $settings = self::get_settings($post_id);

        wp_enqueue_script('derma-roi-calculator');
        wp_enqueue_style('derma-roi-calculator');

        $default_days = min(max(20, $settings['min_days']), $settings['max_days']);
        $financing_url = self::get_financing_url($post_id);

        ob_start();
        ?>
        <div class="derma-roi-calculator-wrapper" data-calculator-id="<?php echo esc_attr($post_id); ?>">
            <div class="derma-roi-header">
                <div class="derma-roi-heading">
                    <span class="derma-roi-kicker"><?php esc_html_e('Investerings Afkast (ROI)', 'derma-roi-calculator'); ?></span>
                    <h3><?php echo esc_html($settings['clinic_name'] ?: __('Beregn Dit Klinik-Afkast', 'derma-roi-calculator')); ?></h3>
                    <p><?php esc_html_e('Se hvor meget din klinik kan tjene om måneden med Hifu UltraLift Pro.', 'derma-roi-calculator'); ?></p>
                </div>
                <div class="derma-roi-score">
                    <span class="derma-roi-score-icon" aria-hidden="true"></span>
                    <span>
                        <small><?php esc_html_e('Gennemsnitlig dækningsgrad', 'derma-roi-calculator'); ?></small>
                        <strong data-result="coverage">0.0% dækningsgrad</strong>
                    </span>
                </div>
            </div>

            <div class="derma-roi-shell">
                <div class="derma-roi-panel derma-roi-inputs">
                    <h4 class="derma-roi-panel-title"><span aria-hidden="true"></span><?php esc_html_e('Aktivitetsniveau og Priser', 'derma-roi-calculator'); ?></h4>
                    <?php self::render_range('treatments', __('Behandlinger pr. dag:', 'derma-roi-calculator'), 1, 20, 1, $settings['default_treatments'], array(__('1/dag (Hobby)', 'derma-roi-calculator'), __('5/dag (Standard)', 'derma-roi-calculator'), __('20/dag (Høj-aktivitet)', 'derma-roi-calculator')), __('behandlinger', 'derma-roi-calculator')); ?>
                    <?php self::render_range('price', __('Gennemsnitlig pris pr. session:', 'derma-roi-calculator'), $settings['min_price'], $settings['max_price'], 50, $settings['default_price'], array(number_format_i18n($settings['min_price']) . ' kr. (Hage)', number_format_i18n($settings['default_price']) . ' kr. (Anbefalet)', number_format_i18n($settings['max_price']) . ' kr. (Luksus Kombi)'), 'DKK'); ?>
                    <?php self::render_range('material', __('Materialeudgift pr. session:', 'derma-roi-calculator'), $settings['min_material'], $settings['max_material'], 10, $settings['default_material'], array(number_format_i18n($settings['min_material']) . ' kr. (Ingen forbrug)', number_format_i18n($settings['default_material']) . ' kr. (Anbefalet)', number_format_i18n($settings['max_material']) . ' kr. (Høj udgift)'), 'DKK'); ?>

                    <div class="derma-roi-selects">
                        <div class="derma-roi-select-group">
                            <label><?php esc_html_e('Behandlingsdage pr. måned:', 'derma-roi-calculator'); ?></label>
                            <select class="derma-roi-select" data-input="days">
                                <?php foreach (self::get_day_options($settings['min_days'], $settings['max_days'], $default_days) as $days => $label) : ?>
                                    <option value="<?php echo esc_attr($days); ?>" <?php selected($days, $default_days); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="derma-roi-select-group">
                            <label><?php esc_html_e('Finansiel aftaleform:', 'derma-roi-calculator'); ?></label>
                            <select class="derma-roi-select" data-input="lease">
                                <option value="<?php echo esc_attr($settings['lease_1_cost']); ?>" data-label="<?php echo esc_attr(self::format_lease_label($settings['lease_1_name'], $settings['lease_1_cost'])); ?>"><?php echo esc_html(self::format_lease_label($settings['lease_1_name'], $settings['lease_1_cost'])); ?></option>
                                <option value="<?php echo esc_attr($settings['lease_2_cost']); ?>" data-label="<?php echo esc_attr(self::format_lease_label($settings['lease_2_name'], $settings['lease_2_cost'])); ?>"><?php echo esc_html(self::format_lease_label($settings['lease_2_name'], $settings['lease_2_cost'])); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="derma-roi-side">
                    <div class="derma-roi-panel derma-roi-estimate" aria-live="polite">
                        <h4><?php esc_html_e('Udregnet månedligt estimat', 'derma-roi-calculator'); ?></h4>
                        <p class="derma-roi-subline"><?php esc_html_e('Estimeret brutto omsætning:', 'derma-roi-calculator'); ?> <span data-result="gross_revenue_inline">0 DKK</span> <span data-result="total_treatments_inline">(0 behandlinger)</span></p>
                        <div class="derma-roi-profit-label"><?php esc_html_e('Dit månedlige Netto-Overskud:', 'derma-roi-calculator'); ?></div>
                        <div class="derma-roi-profit" data-result="net_profit">0 kr.</div>
                        <p class="derma-roi-subline"><?php esc_html_e('Efter faste omkostninger og forbrugsmaterialer.', 'derma-roi-calculator'); ?></p>

                        <dl class="derma-roi-metrics">
                            <div>
                                <dt><?php esc_html_e('Vælg finansiering:', 'derma-roi-calculator'); ?></dt>
                                <dd data-result="lease_label"><?php echo esc_html(self::format_lease_label($settings['lease_1_name'], $settings['lease_1_cost'])); ?></dd>
                            </div>
                            <div>
                                <dt><?php esc_html_e('Gennemsnitlig variabel forbrug:', 'derma-roi-calculator'); ?></dt>
                                <dd class="derma-roi-negative" data-result="material_total">-0 DKK</dd>
                            </div>
                            <div class="derma-roi-break-row">
                                <dt><?php esc_html_e('Break-even pr. måned:', 'derma-roi-calculator'); ?></dt>
                                <dd data-result="break_even">0 behandlinger</dd>
                            </div>
                            <div>
                                <dt><?php esc_html_e('Investering Return-on-Investment:', 'derma-roi-calculator'); ?></dt>
                                <dd data-result="roi">0.0x dækkende dækning</dd>
                            </div>
                        </dl>
                    </div>
                    <a class="derma-roi-cta" href="<?php echo esc_url($financing_url); ?>"><?php esc_html_e('Søg uforpligtende Finansierings-Godkendelse', 'derma-roi-calculator'); ?><span aria-hidden="true"></span></a>
                </div>
            </div>

            <div class="derma-roi-risk">
                <span class="derma-roi-risk-icon" aria-hidden="true"></span>
                <div>
                    <strong><?php esc_html_e('Minimal forretningsrisiko:', 'derma-roi-calculator'); ?></strong>
                    <p><?php esc_html_e('DermaScope AI tilbyder op til 100.000+ hudanalyser gennem sit AI-diagnosemodul. Løsningen kræver ingen dyre engangskassetter eller specialudstyr til hver konsultation. Din eneste variable omkostning er standard dermatoskop-tilbehør og evt. licensfornyelse, typisk for en brøkdel af prisen pr. session sammenlignet med traditionelt udstyr.', 'derma-roi-calculator'); ?></p>
                </div>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    public static function render_price($atts) {
        $atts = shortcode_atts(array(
            'id'   => '',
            'type' => 'leasing',
        ), $atts, 'derma_calculator_price');

        $shortcode_id = sanitize_key($atts['id']);
        $type = sanitize_key($atts['type']);

        if (!$shortcode_id) {
            return '';
        }

        $post_id = self::get_calculator_id($shortcode_id);
        if (!$post_id) {
            return '';
        }

        wp_enqueue_style('derma-roi-calculator');

        $settings = self::get_settings($post_id);
        $cost = 'rental' === $type ? $settings['lease_2_cost'] : $settings['lease_1_cost'];
        $class = 'rental' === $type ? 'derma-roi-price-rental' : 'derma-roi-price-leasing';

        return '<span class="derma-roi-price ' . esc_attr($class) . '">' . esc_html(number_format_i18n($cost) . ' kr./md') . '</span>';
    }

    private static function render_range($key, $label, $min, $max, $step, $value, $range_labels, $suffix = '') {
        ?>
        <div class="derma-roi-control">
            <div class="derma-roi-label">
                <span><?php echo esc_html($label); ?></span>
                <strong class="derma-roi-value"><span data-value="<?php echo esc_attr($key); ?>"><?php echo esc_html(number_format_i18n($value)); ?></span><?php echo $suffix ? ' ' . esc_html($suffix) : ''; ?></strong>
            </div>
            <input type="range" class="derma-roi-input" data-input="<?php echo esc_attr($key); ?>" min="<?php echo esc_attr($min); ?>" max="<?php echo esc_attr($max); ?>" step="<?php echo esc_attr($step); ?>" value="<?php echo esc_attr($value); ?>">
            <div class="derma-roi-labels">
                <?php foreach ($range_labels as $range_label) : ?>
                    <small><?php echo esc_html($range_label); ?></small>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private static function get_settings($post_id) {
        $settings = array(
            'clinic_name'        => get_post_meta($post_id, 'clinic_name', true),
            'default_treatments' => max(1, (int) get_post_meta($post_id, 'default_treatments_per_day', true) ?: 5),
            'min_price'          => max(0, (int) get_post_meta($post_id, 'min_price', true) ?: 450),
            'max_price'          => max(50, (int) get_post_meta($post_id, 'max_price', true) ?: 4000),
            'default_price'      => max(0, (int) get_post_meta($post_id, 'default_price', true) ?: 1050),
            'min_material'       => max(0, (int) get_post_meta($post_id, 'min_material_cost', true)),
            'max_material'       => max(10, (int) get_post_meta($post_id, 'max_material_cost', true) ?: 1000),
            'default_material'   => max(0, (int) get_post_meta($post_id, 'default_material', true) ?: 150),
            'min_days'           => max(1, (int) get_post_meta($post_id, 'min_days_per_month', true) ?: 8),
            'max_days'           => min(31, max(1, (int) get_post_meta($post_id, 'max_days_per_month', true) ?: 30)),
            'lease_1_name'       => get_post_meta($post_id, 'lease_option_1_name', true) ?: 'Standard Leasing',
            'lease_1_cost'       => max(0, (int) get_post_meta($post_id, 'lease_option_1_cost', true) ?: 3195),
            'lease_2_name'       => get_post_meta($post_id, 'lease_option_2_name', true) ?: 'Rental',
            'lease_2_cost'       => max(0, (int) get_post_meta($post_id, 'lease_option_2_cost', true) ?: 2995),
        );

        $settings['default_treatments'] = min($settings['default_treatments'], 20);
        $settings['max_price'] = max($settings['max_price'], $settings['min_price']);
        $settings['max_material'] = max($settings['max_material'], $settings['min_material']);
        $settings['max_days'] = max($settings['max_days'], $settings['min_days']);
        $settings['default_price'] = min(max($settings['default_price'], $settings['min_price']), $settings['max_price']);
        $settings['default_material'] = min(max($settings['default_material'], $settings['min_material']), $settings['max_material']);

        return $settings;
    }

    private static function get_calculator_id($shortcode_id) {
        $query = new WP_Query(array(
            'post_type'      => 'calculator',
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => 'shortcode_code',
                    'value'   => $shortcode_id,
                    'compare' => '=',
                ),
            ),
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        ));

        return $query->posts ? (int) $query->posts[0] : 0;
    }

    private static function format_lease_label($name, $cost) {
        return $name . ' (' . number_format_i18n($cost) . ' kr./md)';
    }

    private static function get_financing_url($post_id) {
        $url = add_query_arg('page_id', 3742, home_url('/'));

        return apply_filters('derma_roi_financing_url', $url, $post_id);
    }

    private static function get_day_options($min_days, $max_days, $default_days) {
        $presets = array(
            8  => __('8 dage (Kun weekender)', 'derma-roi-calculator'),
            12 => __('12 dage (Deltid)', 'derma-roi-calculator'),
            16 => __('16 dage (Udvidet deltid)', 'derma-roi-calculator'),
            20 => __('20 dage (Normal fuldtid)', 'derma-roi-calculator'),
            24 => __('24 dage (Høj aktivitet)', 'derma-roi-calculator'),
            30 => __('30 dage (Maks kapacitet)', 'derma-roi-calculator'),
        );

        $options = array();
        foreach ($presets as $days => $label) {
            if ($days >= $min_days && $days <= $max_days) {
                $options[$days] = $label;
            }
        }

        if (!isset($options[$default_days])) {
            $options[$default_days] = sprintf(_n('%d dag', '%d dage', $default_days, 'derma-roi-calculator'), $default_days);
        }

        ksort($options);

        return $options;
    }
}
