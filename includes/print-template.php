<?php
add_action('wp_ajax_psp_print_sticker', 'psp_render_print_template');
add_action('wp_ajax_psp_bulk_print_sticker', 'psp_render_print_template');

function psp_render_print_template() {

    // --- 1. Fetch Settings ---
    $merchant_name = get_option('psp_merchant_name', 'My Shop');
    $merchant_logo = get_option('psp_company_logo', '');
    $merchant_id   = get_option('psp_merchant_id', '');
    $contact_info  = get_option('psp_contact_info', '');
    $note_source   = get_option('psp_note_source', 'customer');
    $footer_note   = get_option('psp_custom_footer_note', 'If returned, delivery charge applies.');
    
    // Visibility
    $show_image = get_option('psp_show_product_image') === 'yes';
    $show_price = get_option('psp_show_product_price') === 'yes';
    $show_date  = get_option('psp_show_order_date') === 'yes';
    $show_note  = get_option('psp_show_note_section', 'yes') === 'yes';

    // Design & Size
    $density = get_option('psp_design_density', 'cozy'); 
    $preset  = get_option('psp_paper_preset', '75x100');
    $orientation = get_option('psp_orientation', 'portrait');

    // Determine Dimensions
    $css_size = ''; $width_screen = ''; 

    if ($preset === 'custom') {
        $w = get_option('psp_paper_width', '75mm');
        $h = get_option('psp_paper_height', '100mm');
        $css_size = "{$w} {$h}"; 
        $width_screen = $w; 
    } elseif ($preset === '75x100') {
        if ($orientation === 'landscape') { 
            $css_size = "100mm 75mm"; $width_screen = "100mm"; 
        } else { 
            $css_size = "75mm 100mm"; $width_screen = "75mm"; 
        }
    } else {
        $css_size = strtoupper($preset) . " " . $orientation;
        if ($preset === 'a4') { $w = ($orientation==='portrait')?'210mm':'297mm'; }
        elseif ($preset === 'a5') { $w = ($orientation==='portrait')?'148mm':'210mm'; }
        $width_screen = $w; 
    }
    
    $order_ids_param = isset($_GET['order_ids']) ? $_GET['order_ids'] : null;
    $single_order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

    if ($order_ids_param) {
        $order_ids = array_filter(array_map('absint', array_map('trim', explode(',', $order_ids_param))));
    } elseif ($single_order_id) {
        $order_ids = [ absint($single_order_id) ];
    } else {
        wp_die('No order selected');
    }
    ?>
    <!DOCTYPE html>
    <html lang="bn">
    <head>
        <meta charset="utf-8">
        <title>Sticker Print</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap');
            
            :root {
                --font-base: 13px;
                --font-title: 16px;
                --gap: 3px;
                --padding: 4px;
                --border-color: #000;
                --section-radius: 4px;
            }

            /* Density Adjustments */
            <?php if ($density === 'compact'): ?>
            :root { --gap: 1px; --padding: 2px; --font-base: 12px; --font-title: 14px; --section-radius: 2px; }
            <?php elseif ($density === 'flexible'): ?>
            :root { --gap: 8px; --padding: 10px; --font-base: 15px; --font-title: 20px; --section-radius: 6px; }
            <?php endif; ?>

            html, body { 
                margin: 0; padding: 0; 
                width: 100%; height: 100%;
                font-family: "Poppins", "Hind Siliguri", sans-serif;
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
                background: #fff; 
            }
            
            @media print {
                @page { 
                    size: <?php echo esc_html($css_size); ?>; 
                    margin: 0; 
                }
                
                body { background: #fff; }

                .psp-wrapper {
                    width: 100%;
                    height: 100%; 
                    page-break-after: always;
                    box-sizing: border-box;
                    padding: var(--padding);
                    display: flex;
                    flex-direction: column;
                    gap: var(--gap);
                    border: none !important;
                    margin: 0;
                    overflow: hidden; 
                }
            }
            
            /* Screen Preview */
            .psp-wrapper {
                width: <?php echo esc_html($width_screen); ?>;
                <?php if($preset !== 'custom'): ?>height: 100mm;<?php endif; ?> 
                border: 1px dashed #ddd; 
                margin: 20px auto;
                padding: var(--padding);
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
                gap: var(--gap);
                background: #fff;
                position: relative;
                overflow: hidden;
            }

            /* --- Sections --- */
            .psp-section {
                border: 1px solid var(--border-color);
                border-radius: var(--section-radius);
                padding: var(--padding);
                width: 100%;
                box-sizing: border-box;
            }

            /* Header */
            .psp-header {
                text-align: center;
                background: #fff;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                border: none !important; 
                border-bottom: 2px solid #000 !important; 
                padding-bottom: 3px;
                border-radius: 0;
            }
            .psp-logo { max-width: 90%; max-height: 50px; object-fit: contain; margin-bottom: 2px; display: block; }
            .psp-title { font-size: var(--font-title); font-weight: 800; text-transform: uppercase; line-height: 1.1; }
            .psp-sub { font-size: calc(var(--font-base) - 2px); margin-top: 2px; font-weight: 500;}

            /* Products */
            .psp-products { 
                font-size: var(--font-base); 
                flex-shrink: 1; 
                overflow: hidden;
            }
            .psp-item { 
                display: flex; 
                align-items: flex-start; 
                gap: 6px; 
                margin-bottom: 3px; 
                border-bottom: 1px dotted #ccc; 
                padding-bottom: 3px; 
            }
            .psp-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
            
            .psp-img { width: 35px; height: 35px; object-fit: cover; border-radius: 3px; border: 1px solid #ccc; flex-shrink: 0; }
            
            .psp-item-details { flex-grow: 1; min-width: 0; }
            
            /* Title & Qty */
            .psp-item-header { line-height: 1.2; }
            .psp-item-name { font-weight: 600; margin: 0; display: inline; }
            .psp-item-qty { font-weight: 800; font-size: 1.1em; margin-left: 4px; display: inline; }

            /* Variations Block */
            .psp-item-meta { 
                font-size: 0.85em; 
                color: #333; 
                margin: 1px 0 0 0; 
                line-height: 1.25;
                display: block; 
            }
            .psp-item-meta span { display: block; }

            .psp-item-price { font-weight: 700; font-size: 0.9em; white-space: nowrap; margin-left: 5px; }

            /* Courier */
            .psp-courier { 
                text-align: center; font-weight: 900; 
                font-size: calc(var(--font-title) + 3px); 
                background: #000 !important; color: #fff !important; 
                padding: calc(var(--padding) + 2px);
                border: 2px solid #000;
                flex-shrink: 0;
            }

            /* Note */
            .psp-note { 
                background: #f5f5f5 !important; 
                font-size: calc(var(--font-base) - 1px); 
                font-style: italic; 
                border: 1px dashed #666;
                flex-shrink: 0;
            }

            /* Customer */
            .psp-customer { 
                font-size: var(--font-base); 
                line-height: 1.35; 
                border: none !important; 
                padding-left: 2px;
                flex-shrink: 0;
            }
            .psp-row { 
                display: flex; 
                justify-content: flex-start;
                align-items: baseline;
                gap: 5px;
            }
            .psp-label { font-weight: 700; min-width: 50px; color: #333; }
            .psp-val { text-align: left; }

            /* Footer - Flow with content */
            .psp-footer-wrapper {
                margin-top: 5px; /* Separate slightly from previous section */
                display: flex;
                flex-direction: column;
                gap: 2px;
                padding-bottom: 0;
                flex-shrink: 0;
                /* No margin-top: auto; allows it to flow naturally */
            }
            .psp-footer-total { 
                text-align: center; 
                border: 2px solid #000; 
                font-weight: 800;
                font-size: var(--font-title);
                background: #fff;
                padding: var(--padding);
                border-radius: var(--section-radius);
            }
            .psp-terms { 
                text-align: center; 
                font-size: var(--font-base); 
                font-weight: 600;
                color: #000; 
                margin-top: 1px;
                line-height: 1.1;
            }
            .psp-timestamp {
                font-size: 9px;
                color: #777;
                text-align: center;
                margin-top: 0;
                white-space: nowrap;
            }

        </style>
        <script>
            window.onload = function(){ window.print(); };
        </script>
    </head>
    <body>
    <?php
    foreach ($order_ids as $order_id):
        $order = wc_get_order($order_id);
        if (!$order) continue;

        // Permissions Check
        $already_printed = get_post_meta($order_id, 'psp_sticker_printed', true);
        if ($already_printed === 'yes') {
            if (!current_user_can('edit_shop_orders')) {
                echo "<div style='padding:50px; text-align:center; color:red; page-break-after:always;'>Order #{$order->get_order_number()} already printed.</div>";
                continue;
            }
        }
        update_post_meta($order_id, 'psp_sticker_printed', 'yes');

        // Customer Data
        $display_number = $order->get_order_number();
        $billing_name   = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $billing_phone  = $order->get_billing_phone();
        $address        = str_replace('<br/>', ', ', $order->get_formatted_billing_address());
        $address        = strip_tags($address);
        
        $order_date = '';
        if ($show_date) {
            $order_date = $order->get_date_created()->date_i18n(get_option('date_format'));
        }

        // Courier Data
        $courier_display = '';
        $sf_id = get_post_meta($order_id, 'steadfast_consignment_id', true);
        $pt_id = get_post_meta($order_id, 'ptc_consignment_id', true);
        if (!$pt_id) $pt_id = get_post_meta($order_id, 'pathao_consignment_id', true);
        if (!$pt_id) $pt_id = get_post_meta($order_id, 'pt_consignment_id', true);

        if ($sf_id) { $courier_display = 'SFC: ' . $sf_id; }
        elseif ($pt_id) { $courier_display = 'Pathao: ' . $pt_id; }

        // Note Data
        $note_content = '';
        if ($show_note) {
            if ($note_source === 'customer') {
                $note_content = $order->get_customer_note();
            } else {
                $notes = wc_get_order_notes(['order_id' => $order_id, 'type' => 'internal', 'orderby' => 'date_created', 'order' => 'DESC']);
                foreach ($notes as $n) { if ($n->added_by !== 'system') { $note_content = $n->content; break; } }
            }
        }

        // --- PRE-FETCH GLOBAL BILLING SIZE ---
        $global_billing_size = $order->get_meta('billing_size', true);
        if (empty($global_billing_size)) {
            $global_billing_size = $order->get_meta('_billing_size', true);
        }
        // -------------------------------------
        ?>

        <div class="psp-wrapper">
            
            <!-- SECTION 1: Header -->
            <div class="psp-section psp-header">
                <?php if($merchant_logo): ?>
                    <img src="<?php echo esc_url($merchant_logo); ?>" class="psp-logo" alt="Logo">
                <?php endif; ?>
                <?php if(!$merchant_logo || $merchant_name): ?>
                    <div class="psp-title"><?php echo esc_html($merchant_name); ?></div>
                <?php endif; ?>
                <div class="psp-sub">
                    <?php 
                        $parts = [];
                        if($merchant_id) $parts[] = 'MID: ' . $merchant_id;
                        if($contact_info) $parts[] = $contact_info;
                        echo implode(' | ', array_map('esc_html', $parts));
                    ?>
                </div>
            </div>

            <!-- SECTION 2: Products -->
            <div class="psp-section psp-products">
                <?php foreach ($order->get_items() as $item): 
                    $product = $item->get_product();
                    $qty = $item->get_quantity();
                    
                    // Image
                    $img_url = '';
                    if ($show_image && $product) {
                        $img_id = $product->get_image_id();
                        if (!$img_id && $product->is_type('variation')) {
                             $parent = wc_get_product($product->get_parent_id());
                             if($parent) $img_id = $parent->get_image_id();
                        }
                        if ($img_id) { $img_src = wp_get_attachment_image_src($img_id, 'thumbnail'); if ($img_src) $img_url = $img_src[0]; }
                    }

                    // --- VARIATION LOGIC ---
                    $meta_to_display = [];

                    // 1. Custom Billing Size
                    $billing_size = $item->get_meta('billing_size');
                    if(!$billing_size) $billing_size = $item->get_meta('_billing_size');
                    if(empty($billing_size) && !empty($global_billing_size)) $billing_size = $global_billing_size;

                    if($billing_size) {
                        $meta_to_display[] = 'Size: <b>' . esc_html($billing_size) . '</b>';
                    }

                    // 2. Standard Variations
                    foreach ( $item->get_formatted_meta_data() as $meta ) {
                        if ( stripos($meta->key, 'billing_size') !== false ) continue;
                        $meta_to_display[] = wp_kses_post( $meta->display_key . ': ' . $meta->display_value );
                    }

                    // 3. Product Title (Clean)
                    $product_name = $product ? $product->get_name() : $item->get_name();
                    ?>
                    <div class="psp-item">
                        <?php if($img_url): ?><img src="<?php echo esc_url($img_url); ?>" class="psp-img" alt=""><?php endif; ?>
                        
                        <div class="psp-item-details">
                            <div class="psp-item-header">
                                <span class="psp-item-name"><?php echo esc_html($product_name); ?></span>
                                <span class="psp-item-qty">× <?php echo $qty; ?></span>
                            </div>
                            
                            <?php if(!empty($meta_to_display)): ?>
                                <div class="psp-item-meta">
                                    <?php 
                                        foreach($meta_to_display as $m) {
                                            echo '<span>' . $m . '</span>';
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if($show_price): ?>
                            <div class="psp-item-price"><?php echo wc_price($item->get_total() + $item->get_total_tax()); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- SECTION 3: Courier -->
            <?php if ($courier_display): ?>
                <div class="psp-section psp-courier"><?php echo esc_html($courier_display); ?></div>
            <?php endif; ?>

            <!-- SECTION 4: Note -->
            <?php if ($show_note && !empty($note_content)): ?>
                <div class="psp-section psp-note"><b>Note:</b> <?php echo esc_html($note_content); ?></div>
            <?php endif; ?>

            <!-- SECTION 5: Customer -->
            <div class="psp-section psp-customer">
                <div class="psp-row">
                    <span class="psp-label">Order:</span> 
                    <span class="psp-val" style="font-size:1.1em; font-weight:700;">#<?php echo $display_number; ?></span>
                </div>
                
                <?php if($show_date): ?>
                <div class="psp-row">
                    <span class="psp-label">Date:</span> 
                    <span class="psp-val"><?php echo esc_html($order_date); ?></span>
                </div>
                <?php endif; ?>

                <div class="psp-row">
                    <span class="psp-label">Name:</span> 
                    <span class="psp-val"><?php echo esc_html($billing_name); ?></span>
                </div>
                <div class="psp-row">
                    <span class="psp-label">Phone:</span> 
                    <span class="psp-val"><b style="font-size:1.1em;"><?php echo esc_html($billing_phone); ?></b></span>
                </div>
                <div class="psp-row" style="align-items: flex-start;">
                    <span class="psp-label">Addr:</span> 
                    <span class="psp-val"><?php echo esc_html($address); ?></span>
                </div>
            </div>

            <!-- SECTION 6: Footer -->
            <div class="psp-footer-wrapper">
                <div class="psp-footer-total">
                    TOTAL: <?php echo wc_price($order->get_total(), ['currency' => $order->get_currency()]); ?>
                </div>

                <div class="psp-terms">
                    <?php if($footer_note) echo nl2br(esc_html($footer_note)); ?>
                </div>
                
                <div class="psp-timestamp">
                    <?php date_default_timezone_set('Asia/Dhaka'); echo date("d-M-Y h:i A"); ?>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
    </body>
    </html>
    <?php
    exit;
}