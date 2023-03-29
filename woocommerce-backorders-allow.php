<?php
/**
 * Plugin Name: WooCommerce Allow Backorders
 * Plugin URI: https://boon.band/
 * Description: This plugin sets the 'Allow backorders' option for all WooCommerce products and their variations.
 * Version: 1.1
 * Author: Boon.Band
 * Author URI: https://boon.band/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wc-allow-backorders
 */

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_menu', 'wc_allow_backorders_menu');
    add_action('plugins_loaded', 'wc_allow_backorders_load_textdomain');

    function wc_allow_backorders_menu() {
        add_submenu_page('woocommerce', __('Allow Backorders', 'wc-allow-backorders'), __('Allow Backorders', 'wc-allow-backorders'), 'manage_options', 'wc-allow-backorders', 'wc_allow_backorders_page');
    }

    function wc_allow_backorders_load_textdomain() {
        load_plugin_textdomain('wc-allow-backorders', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    function wc_allow_backorders_page() {
        if (isset($_POST['allow_backorders'])) {
            check_admin_referer('wc_allow_backorders_nonce_action', 'wc_allow_backorders_nonce');
            wc_allow_backorders_update($_POST['backorders_option']);
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Allow Backorders for All Products', 'wc-allow-backorders'); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('wc_allow_backorders_nonce_action', 'wc_allow_backorders_nonce'); ?>
                <p><?php _e('Select a backorders option and click the button below to set the \'Allow backorders\' option for all WooCommerce products and their variations.', 'wc-allow-backorders'); ?></p>
                <select name="backorders_option">
                    <option value="yes"><?php _e('Allow', 'wc-allow-backorders'); ?></option>
                    <option value="notify"><?php _e('Allow, but notify customer', 'wc-allow-backorders'); ?></option>
                    <option value="no"><?php _e('Do not allow', 'wc-allow-backorders'); ?></option>
                </select>
                <input type="submit" name="allow_backorders" class="button button-primary" value="<?php _e('Allow Backorders', 'wc-allow-backorders'); ?>" />
            </form>
        </div>
        <?php
    }

    function wc_allow_backorders_update($backorders_option) {
        global $wpdb;

        // Update all simple products
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = '_backorders'", $backorders_option));

        // Update all product variations
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = '_backorders' AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product_variation')", $backorders_option));

        // Display success message
        echo '<div class="notice notice-success is-dismissible"><p>' . __('All products and their variations have been updated to allow backorders.', 'wc-allow-backorders') . '</p></div>';
    }

}