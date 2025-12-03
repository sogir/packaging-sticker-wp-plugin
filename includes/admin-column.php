<?php
add_filter('manage_edit-shop_order_columns', 'psp_add_sticker_column');
add_filter('manage_woocommerce_page_wc-orders_columns', 'psp_add_sticker_column');

function psp_add_sticker_column($columns) {
    $columns['psp_sticker'] = 'Sticker';
    return $columns;
}

add_action('manage_shop_order_posts_custom_column', 'psp_render_sticker_column_shop', 10, 2);
add_action('manage_woocommerce_page_wc-orders_custom_column', 'psp_render_sticker_column_wc_orders', 10, 2);

function psp_render_sticker_column_shop($column, $post_id) {
    psp_render_column_content($column, $post_id);
}

function psp_render_sticker_column_wc_orders($column, $order) {
    $order_id = is_object($order) ? $order->get_id() : intval($order);
    psp_render_column_content($column, $order_id);
}

function psp_render_column_content($column, $order_id) {
    if ($column !== 'psp_sticker') return;

    $printed = get_post_meta($order_id, 'psp_sticker_printed', true);
    $current_user = wp_get_current_user();
    $is_admin = in_array('administrator', (array) $current_user->roles);
    
    // Check Consignments
    $sf_id = get_post_meta($order_id, 'steadfast_consignment_id', true);
    
    // Pathao Check (Updated)
    $pt_id = get_post_meta($order_id, 'ptc_consignment_id', true); // New key
    if(empty($pt_id)) $pt_id = get_post_meta($order_id, 'pathao_consignment_id', true);
    if(empty($pt_id)) $pt_id = get_post_meta($order_id, 'pt_consignment_id', true);

    $has_courier = ($sf_id || $pt_id);
    $print_url = admin_url('admin-ajax.php?action=psp_print_sticker&order_id=' . intval($order_id));

    if ($printed === 'yes') {
        echo '<span class="psp-status-printed">✔ Printed</span>';
        if ($is_admin) {
            echo '<br><a href="' . $print_url . '" target="_blank" class="button button-small">Re-Print</a>';
        }
    } else {
        $btn_class = $has_courier ? 'button-primary' : '';
        echo '<a href="' . $print_url . '" target="_blank" class="button ' . $btn_class . ' button-small">Print</a>';
    }
}

// Bulk Actions
add_filter('bulk_actions-edit-shop_order', 'psp_add_bulk_action');
add_filter('bulk_actions-woocommerce_page_wc-orders', 'psp_add_bulk_action');

function psp_add_bulk_action($bulk_actions) {
    $bulk_actions['psp_bulk_sticker_print'] = 'Print Stickers';
    return $bulk_actions;
}

add_filter('handle_bulk_actions-edit-shop_order', 'psp_handle_bulk_action', 10, 3);
add_filter('handle_bulk_actions-woocommerce_page_wc-orders', 'psp_handle_bulk_action', 10, 3);

function psp_handle_bulk_action($redirect_to, $action, $post_ids) {
    if ($action !== 'psp_bulk_sticker_print') return $redirect_to;
    
    $order_ids = implode(',', array_map('intval', $post_ids));
    $print_url = admin_url('admin-ajax.php?action=psp_bulk_print_sticker&order_ids=' . $order_ids);
    
    wp_redirect($print_url);
    exit;
}