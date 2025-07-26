<?php
/**
 * Provide a admin area view for the plugin settings
 */

if (isset($_POST['wootale_save_settings'])) {
    if (wp_verify_nonce($_POST['wootale_settings_nonce'], 'wootale_settings')) {
        $only_template_pages = isset($_POST['wootale_only_template_pages']) ? 'yes' : 'no';
        update_option('wootale_only_template_pages', $only_template_pages);

        $replace_tabs = isset($_POST['wootale_replace_tabs']) ? 'yes' : 'no';
        update_option('wootale_replace_tabs', $replace_tabs);
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'wootale') . '</p></div>';
    }
}

$only_template_pages = get_option('wootale_only_template_pages', 'no');
$replace_tabs = get_option('wootale_replace_tabs', 'no');
?>

<div class="wrap">
    <h1><?php _e('WooTale Settings', 'wootale'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wootale_settings', 'wootale_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Template Pages Filter', 'wootale'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wootale_only_template_pages" value="yes" <?php checked($only_template_pages, 'yes'); ?>>
                        <?php _e('Only show pages marked as templates in the dropdown', 'wootale'); ?>
                    </label>
                    <p class="description"><?php _e('When enabled, only pages with "Mark as Product Template" checked will appear in the template selection dropdown.', 'wootale'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Template Display Mode', 'wootale'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wootale_replace_tabs" value="yes" <?php checked($replace_tabs, 'yes'); ?>>
                        <?php _e('Replace entire Description and Reviews tabs with custom template', 'wootale'); ?>
                    </label>
                    <p class="description"><?php _e('When enabled, the custom template will replace both Description and Reviews tabs completely. When disabled, the template content will appear inside the Description tab.', 'wootale'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Save Settings', 'wootale'), 'primary', 'wootale_save_settings'); ?>
    </form>
    
    <hr>
    
    <h2><?php _e('Available Placeholders', 'wootale'); ?></h2>
    <div class="wootale-placeholders">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div>
                <h3><?php _e('Basic Information', 'wootale'); ?></h3>
                <ul>
                    <li><code>[product_name]</code> - <?php _e('Product name', 'wootale'); ?></li>
                    <li><code>[product_price]</code> - <?php _e('Current price (formatted)', 'wootale'); ?></li>
                    <li><code>[product_regular_price]</code> - <?php _e('Regular price', 'wootale'); ?></li>
                    <li><code>[product_sale_price]</code> - <?php _e('Sale price', 'wootale'); ?></li>
                    <li><code>[product_sku]</code> - <?php _e('Product SKU', 'wootale'); ?></li>
                </ul>
            </div>
            
            <div>
                <h3><?php _e('Descriptions', 'wootale'); ?></h3>
                <ul>
                    <li><code>[product_description]</code> - <?php _e('Full description', 'wootale'); ?></li>
                    <li><code>[product_short_description]</code> - <?php _e('Short description', 'wootale'); ?></li>
                </ul>
                
                <h3><?php _e('Physical Properties', 'wootale'); ?></h3>
                <ul>
                    <li><code>[product_weight]</code> - <?php _e('Weight with unit', 'wootale'); ?></li>
                    <li><code>[product_dimensions]</code> - <?php _e('Formatted dimensions', 'wootale'); ?></li>
                </ul>
            </div>
            
            <div>
                <h3><?php _e('Categories & Reviews', 'wootale'); ?></h3>
                <ul>
                    <li><code>[product_categories]</code> - <?php _e('Product categories', 'wootale'); ?></li>
                    <li><code>[product_tags]</code> - <?php _e('Product tags', 'wootale'); ?></li>
                    <li><code>[product_rating]</code> - <?php _e('Star rating HTML', 'wootale'); ?></li>
                    <li><code>[product_review_count]</code> - <?php _e('Number of reviews', 'wootale'); ?></li>
                    <li><code>[product_stock_status]</code> - <?php _e('Stock status', 'wootale'); ?></li>
                </ul>
            </div>
        </div>
        
        <h3><?php _e('Shortcode Alternative', 'wootale'); ?></h3>
        <p><?php _e('You can also use shortcodes instead of placeholders:', 'wootale'); ?></p>
        <ul>
            <li><code>[wootale_product_data field="name"]</code></li>
            <li><code>[wootale_product_data field="price" format="price"]</code></li>
            <li><code>[wootale_product_data field="description"]</code></li>
        </ul>
    </div>
    
    <hr>
    
    <h2><?php _e('How to Use', 'wootale'); ?></h2>
    <ol>
        <li><?php _e('Create a WordPress Page or Elementor Template with your design', 'wootale'); ?></li>
        <li><?php _e('Add placeholders like [product_name] or [product_price] where you want dynamic content', 'wootale'); ?></li>
        <li><?php _e('Optionally, mark the page as a "Product Template" using the checkbox in the page editor', 'wootale'); ?></li>
        <li><?php _e('Go to any product edit page and select your template from the "Product Description Template" dropdown', 'wootale'); ?></li>
        <li><?php _e('Update the product and view it on the frontend', 'wootale'); ?></li>
    </ol>
</div>