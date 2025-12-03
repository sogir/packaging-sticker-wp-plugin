<?php
/*
Plugin Name: Packaging Sticker Print
Plugin URI: https://ridwa.com/
Description: Print product packaging stickers with Courier Id (Steadfast & Pathao) and customizable settings.
Version: 2.1.0
Author: Ridwa.com
Author URI: https://ridwa.com/
*/

if (!defined('ABSPATH')) exit;

define('PSP_PATH', plugin_dir_path(__FILE__));
define('PSP_URL', plugin_dir_url(__FILE__));

// Check WooCommerce Activation
register_activation_hook(__FILE__, 'psp_check_woocommerce');
function psp_check_woocommerce() {
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        wp_die('Packaging Sticker Print plugin requires WooCommerce to be active.');
    }
}

// Include Files
require_once PSP_PATH . 'includes/admin-column.php';
require_once PSP_PATH . 'includes/print-template.php';
require_once PSP_PATH . 'includes/settings-page.php';

// Enqueue Admin Styles
add_action('admin_enqueue_scripts', 'psp_admin_scripts');
function psp_admin_scripts() {
    wp_enqueue_style('psp-admin-style', PSP_URL . 'assets/css/style.css');
}