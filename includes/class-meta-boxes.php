<?php
defined('ABSPATH') || exit;

class Derma_ROI_Meta_Boxes {
    public static function init() {
        add_action('add_meta_boxes_calculator', array(__CLASS__, 'register_meta_boxes'));
    }

    public static function register_meta_boxes() {
        add_meta_box('derma_roi_calculator_settings', __('Calculator Settings', 'derma-roi-calculator'), array(__CLASS__, 'render_calculator_settings'), 'calculator', 'normal', 'high');
        add_meta_box('derma_roi_shortcode_box', __('Shortcode', 'derma-roi-calculator'), array(__CLASS__, 'render_shortcode_box'), 'calculator', 'side', 'high');
        add_meta_box('derma_roi_instructions', __('Instructions', 'derma-roi-calculator'), array(__CLASS__, 'render_instructions'), 'calculator', 'side', 'low');
    }

    public static function render_calculator_settings($post) {
        wp_nonce_field('derma_roi_save', 'derma_roi_nonce');

        $values = array(
            'clinic_name'                => get_post_meta($post->ID, 'clinic_name', true),
            'default_treatments_per_day' => get_post_meta($post->ID, 'default_treatments_per_day', true) ?: 5,
            'min_price'                  => get_post_meta($post->ID, 'min_price', true) ?: 450,
            'max_price'                  => get_post_meta($post->ID, 'max_price', true) ?: 4000,
            'default_price'              => get_post_meta($post->ID, 'default_price', true) ?: 1050,
            'min_material_cost'          => get_post_meta($post->ID, 'min_material_cost', true) ?: 0,
            'max_material_cost'          => get_post_meta($post->ID, 'max_material_cost', true) ?: 1000,
            'default_material'           => get_post_meta($post->ID, 'default_material', true) ?: 150,
            'min_days_per_month'         => get_post_meta($post->ID, 'min_days_per_month', true) ?: 8,
            'max_days_per_month'         => get_post_meta($post->ID, 'max_days_per_month', true) ?: 30,
            'lease_option_1_name'        => get_post_meta($post->ID, 'lease_option_1_name', true) ?: 'Standard Leasing',
            'lease_option_1_cost'        => get_post_meta($post->ID, 'lease_option_1_cost', true) ?: 3195,
            'lease_option_2_name'        => get_post_meta($post->ID, 'lease_option_2_name', true) ?: 'Rental',
            'lease_option_2_cost'        => get_post_meta($post->ID, 'lease_option_2_cost', true) ?: 2995,
        );
        ?>
        <div class="derma-roi-settings">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="clinic_name"><?php esc_html_e('Clinic Name', 'derma-roi-calculator'); ?></label></th>
                        <td>
                            <input type="text" id="clinic_name" name="clinic_name" value="<?php echo esc_attr($values['clinic_name']); ?>" class="regular-text" placeholder="<?php esc_attr_e('Enter clinic name or leave empty for generic', 'derma-roi-calculator'); ?>">
                            <p class="description"><?php esc_html_e('Optional. Shows in calculator header.', 'derma-roi-calculator'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Treatment Settings', 'derma-roi-calculator'); ?></th>
                        <td>
                            <label>
                                <?php esc_html_e('Default treatments per day:', 'derma-roi-calculator'); ?>
                                <input type="number" name="default_treatments_per_day" value="<?php echo esc_attr($values['default_treatments_per_day']); ?>" min="1" max="50" class="small-text">
                            </label>
                        </td>
                    </tr>
                    <?php self::render_range_row(__('Price Range (DKK)', 'derma-roi-calculator'), 'min_price', 'max_price', $values, 0, 50); ?>
                    <?php self::render_number_row(__('Default Price (DKK)', 'derma-roi-calculator'), 'default_price', $values, 0, 50); ?>
                    <?php self::render_range_row(__('Material Cost Range (DKK)', 'derma-roi-calculator'), 'min_material_cost', 'max_material_cost', $values, 0, 10); ?>
                    <?php self::render_number_row(__('Default Material Cost (DKK)', 'derma-roi-calculator'), 'default_material', $values, 0, 10); ?>
                    <?php self::render_range_row(__('Working Days per Month Range', 'derma-roi-calculator'), 'min_days_per_month', 'max_days_per_month', $values, 1, 1, 30); ?>
                    <tr>
                        <th scope="row"><?php esc_html_e('Lease Options', 'derma-roi-calculator'); ?></th>
                        <td class="derma-roi-lease-grid">
                            <?php self::render_lease_option(1, $values); ?>
                            <?php self::render_lease_option(2, $values); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    private static function render_range_row($label, $min_key, $max_key, $values, $min, $step, $max = null) {
        ?>
        <tr>
            <th scope="row"><?php echo esc_html($label); ?></th>
            <td class="derma-roi-grid-2">
                <label>
                    <?php esc_html_e('Minimum:', 'derma-roi-calculator'); ?>
                    <input type="number" name="<?php echo esc_attr($min_key); ?>" value="<?php echo esc_attr($values[$min_key]); ?>" min="<?php echo esc_attr($min); ?>" <?php echo null !== $max ? 'max="' . esc_attr($max) . '"' : ''; ?> step="<?php echo esc_attr($step); ?>" class="regular-text">
                </label>
                <label>
                    <?php esc_html_e('Maximum:', 'derma-roi-calculator'); ?>
                    <input type="number" name="<?php echo esc_attr($max_key); ?>" value="<?php echo esc_attr($values[$max_key]); ?>" min="<?php echo esc_attr($min); ?>" <?php echo null !== $max ? 'max="' . esc_attr($max) . '"' : ''; ?> step="<?php echo esc_attr($step); ?>" class="regular-text">
                </label>
            </td>
        </tr>
        <?php
    }

    private static function render_number_row($label, $key, $values, $min, $step, $max = null) {
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
            <td>
                <input type="number" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($values[$key]); ?>" min="<?php echo esc_attr($min); ?>" <?php echo null !== $max ? 'max="' . esc_attr($max) . '"' : ''; ?> step="<?php echo esc_attr($step); ?>" class="regular-text">
            </td>
        </tr>
        <?php
    }

    private static function render_lease_option($number, $values) {
        $name_key = 'lease_option_' . $number . '_name';
        $cost_key = 'lease_option_' . $number . '_cost';
        ?>
        <div class="derma-roi-grid-2">
            <label>
                <?php printf(esc_html__('Option %d Name:', 'derma-roi-calculator'), $number); ?>
                <input type="text" name="<?php echo esc_attr($name_key); ?>" value="<?php echo esc_attr($values[$name_key]); ?>" class="regular-text">
            </label>
            <label>
                <?php esc_html_e('Cost (DKK/month):', 'derma-roi-calculator'); ?>
                <input type="number" name="<?php echo esc_attr($cost_key); ?>" value="<?php echo esc_attr($values[$cost_key]); ?>" min="0" step="100" class="regular-text">
            </label>
        </div>
        <?php
    }

    public static function render_shortcode_box($post) {
        $shortcode = get_post_meta($post->ID, 'shortcode_code', true);
        ?>
        <div class="derma-roi-shortcode-box">
            <?php if ($shortcode) : ?>
                <p><?php esc_html_e('Copy this shortcode and paste it on any page or post:', 'derma-roi-calculator'); ?></p>
                <code id="derma-roi-shortcode-code">[derma_calculator id="<?php echo esc_attr($shortcode); ?>"]</code>
                <button type="button" class="button button-small" data-derma-roi-copy-shortcode><?php esc_html_e('Copy to Clipboard', 'derma-roi-calculator'); ?></button>
                <p><?php esc_html_e('Standalone product prices:', 'derma-roi-calculator'); ?></p>
                <code>[derma_calculator_price id="<?php echo esc_attr($shortcode); ?>" type="leasing"]</code>
                <code>[derma_calculator_price id="<?php echo esc_attr($shortcode); ?>" type="rental"]</code>
            <?php else : ?>
                <p><?php esc_html_e('Shortcode will be generated when you publish or update this calculator.', 'derma-roi-calculator'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function render_instructions() {
        ?>
        <ol class="derma-roi-instructions">
            <li><?php esc_html_e('Fill in the calculator settings.', 'derma-roi-calculator'); ?></li>
            <li><?php esc_html_e('Publish or update the calculator.', 'derma-roi-calculator'); ?></li>
            <li><?php esc_html_e('Copy the generated shortcode.', 'derma-roi-calculator'); ?></li>
            <li><?php esc_html_e('Paste it on any page or post.', 'derma-roi-calculator'); ?></li>
        </ol>
        <?php
    }
}
