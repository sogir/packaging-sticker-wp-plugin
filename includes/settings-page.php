<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'psp_register_settings_page');

function psp_register_settings_page() {
    add_submenu_page(
        'woocommerce',
        'PSP Settings',
        'Sticker Print Settings',
        'manage_options',
        'psp-settings',
        'psp_render_settings_page'
    );
}

function psp_register_settings() {
    // Basic Info
    register_setting('psp_options_group', 'psp_merchant_name');
    register_setting('psp_options_group', 'psp_company_logo');
    register_setting('psp_options_group', 'psp_merchant_id');
    register_setting('psp_options_group', 'psp_contact_info');
    
    // Layout & Visibility
    register_setting('psp_options_group', 'psp_show_product_image');
    register_setting('psp_options_group', 'psp_show_product_price');
    register_setting('psp_options_group', 'psp_show_order_date'); // New: Order Date
    register_setting('psp_options_group', 'psp_show_note_section');
    register_setting('psp_options_group', 'psp_design_density');

    // Note Logic
    register_setting('psp_options_group', 'psp_note_source');
    register_setting('psp_options_group', 'psp_custom_footer_note');
    
    // Paper Size
    register_setting('psp_options_group', 'psp_paper_preset');
    register_setting('psp_options_group', 'psp_orientation');
    register_setting('psp_options_group', 'psp_paper_width');
    register_setting('psp_options_group', 'psp_paper_height');
}
add_action('admin_init', 'psp_register_settings');

function psp_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Packaging Sticker Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('psp_options_group'); ?>
            <?php do_settings_sections('psp_options_group'); ?>
            
            <table class="form-table">
                <!-- Merchant Info -->
                <tr valign="top">
                    <th scope="row">Merchant Name</th>
                    <td><input type="text" name="psp_merchant_name" value="<?php echo esc_attr(get_option('psp_merchant_name', 'My Shop')); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Company Logo URL</th>
                    <td>
                        <input type="url" name="psp_company_logo" value="<?php echo esc_attr(get_option('psp_company_logo')); ?>" class="regular-text" placeholder="https://example.com/logo.png" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Merchant ID</th>
                    <td><input type="text" name="psp_merchant_id" value="<?php echo esc_attr(get_option('psp_merchant_id')); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Contact Info</th>
                    <td><input type="text" name="psp_contact_info" value="<?php echo esc_attr(get_option('psp_contact_info')); ?>" class="regular-text" /></td>
                </tr>

                <!-- Visual Settings -->
                <tr valign="top">
                    <th scope="row">Display Options</th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="psp_show_product_image" value="yes" <?php checked(get_option('psp_show_product_image'), 'yes'); ?> />
                                Show Product Image
                            </label><br>
                            <label>
                                <input type="checkbox" name="psp_show_product_price" value="yes" <?php checked(get_option('psp_show_product_price'), 'yes'); ?> />
                                Show Product Price
                            </label><br>
                            <label>
                                <input type="checkbox" name="psp_show_order_date" value="yes" <?php checked(get_option('psp_show_order_date'), 'yes'); ?> />
                                Show Order Date
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Design Density</th>
                    <td>
                        <select name="psp_design_density">
                            <option value="compact" <?php selected(get_option('psp_design_density'), 'compact'); ?>>Compact</option>
                            <option value="cozy" <?php selected(get_option('psp_design_density', 'cozy'), 'cozy'); ?>>Cozy (Default)</option>
                            <option value="flexible" <?php selected(get_option('psp_design_density'), 'flexible'); ?>>Flexible</option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Note Section</th>
                    <td>
                        <label>
                            <input type="checkbox" name="psp_show_note_section" value="yes" <?php checked(get_option('psp_show_note_section', 'yes'), 'yes'); ?> />
                            Show Note Section
                        </label>
                        <br><br>
                        <select name="psp_note_source">
                            <option value="customer" <?php selected(get_option('psp_note_source'), 'customer'); ?>>Customer Note</option>
                            <option value="admin" <?php selected(get_option('psp_note_source'), 'admin'); ?>>Admin Note</option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Footer Note</th>
                    <td><textarea name="psp_custom_footer_note" class="large-text" rows="2"><?php echo esc_textarea(get_option('psp_custom_footer_note', 'If returned, delivery charge applies.')); ?></textarea></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Paper Size</th>
                    <td>
                        <select name="psp_paper_preset">
                            <option value="75x100" <?php selected(get_option('psp_paper_preset', '75x100'), '75x100'); ?>>75mm x 100mm</option>
                            <option value="a4" <?php selected(get_option('psp_paper_preset'), 'a4'); ?>>A4</option>
                            <option value="a5" <?php selected(get_option('psp_paper_preset'), 'a5'); ?>>A5</option>
                            <option value="custom" <?php selected(get_option('psp_paper_preset'), 'custom'); ?>>Custom</option>
                        </select>
                        <select name="psp_orientation">
                            <option value="portrait" <?php selected(get_option('psp_orientation', 'portrait'), 'portrait'); ?>>Portrait</option>
                            <option value="landscape" <?php selected(get_option('psp_orientation'), 'landscape'); ?>>Landscape</option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Custom Size</th>
                    <td>
                        <input type="text" name="psp_paper_width" value="<?php echo esc_attr(get_option('psp_paper_width', '75mm')); ?>" placeholder="W" size="6" /> 
                        x 
                        <input type="text" name="psp_paper_height" value="<?php echo esc_attr(get_option('psp_paper_height', '100mm')); ?>" placeholder="H" size="6" />
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}