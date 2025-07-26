<?php
/**
 * Provide a admin area view for the meta box
 */

wp_nonce_field('wootale_template_meta_box', 'wootale_template_meta_box_nonce');

$is_template = get_post_meta($post->ID, '_wootale_is_template', true);
?>

<label for="wootale_is_template">
    <input type="checkbox" id="wootale_is_template" name="wootale_is_template" value="1" <?php checked($is_template, '1'); ?>>
    <?php _e('Mark as Product Template', 'wootale'); ?>
</label>
<p><small><?php _e('Check this box to identify this page as a product description template.', 'wootale'); ?></small></p>

<hr>

<h4><?php _e('Available Placeholders:', 'wootale'); ?></h4>
<div style="font-size: 12px; line-height: 1.4;">
    <code>[product_name]</code><br>
    <code>[product_price]</code><br>
    <code>[product_description]</code><br>
    <code>[product_sku]</code><br>
    <code>[product_categories]</code><br>
    <p><a href="<?php echo admin_url('admin.php?page=wootale-settings'); ?>"><?php _e('View all placeholders', 'wootale'); ?></a></p>
</div>