<?php

/**
 * Handle product data placeholders
 */
class WooTale_Placeholders {

    /**
     * Replace product placeholders in content
     */
    public function replace_placeholders($content, $product) {
        if (!$product || !is_object($product)) {
            return $content;
        }
        
        $placeholders = array(
            '[product_name]' => $product->get_name(),
            '[product_price]' => wc_price($product->get_price()),
            '[product_regular_price]' => $product->get_regular_price() ? wc_price($product->get_regular_price()) : '',
            '[product_sale_price]' => $product->is_on_sale() && $product->get_sale_price() ? wc_price($product->get_sale_price()) : '',
            '[product_sku]' => $product->get_sku() ? $product->get_sku() : '',
            '[product_description]' => $product->get_description(),
            '[product_short_description]' => $product->get_short_description(),
            '[product_weight]' => $product->get_weight() ? $product->get_weight() . ' ' . get_option('woocommerce_weight_unit') : '',
            '[product_dimensions]' => wc_format_dimensions($product->get_dimensions(false)),
            '[product_categories]' => wc_get_product_category_list($product->get_id()),
            '[product_tags]' => wc_get_product_tag_list($product->get_id()),
            '[product_rating]' => wc_get_rating_html($product->get_average_rating()),
            '[product_review_count]' => $product->get_review_count(),
            '[product_stock_status]' => $product->is_in_stock() ? __('In Stock', 'wootale') : __('Out of Stock', 'wootale'),
        );
        
        $placeholders = apply_filters('wootale_placeholders', $placeholders, $product);
        
        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }

    /**
     * Get product data for shortcode
     */
    public function get_product_data($product, $field, $format = 'text') {
        if (!$product || !is_object($product)) {
            return '';
        }

        switch ($field) {
            case 'name':
                return $product->get_name();
            case 'price':
                return $format === 'price' ? wc_price($product->get_price()) : $product->get_price();
            case 'regular_price':
                return $format === 'price' ? wc_price($product->get_regular_price()) : $product->get_regular_price();
            case 'sale_price':
                $sale_price = $product->get_sale_price();
                return $sale_price ? ($format === 'price' ? wc_price($sale_price) : $sale_price) : '';
            case 'sku':
                return $product->get_sku();
            case 'description':
                return $product->get_description();
            case 'short_description':
                return $product->get_short_description();
            case 'weight':
                return $product->get_weight() ? $product->get_weight() . ' ' . get_option('woocommerce_weight_unit') : '';
            case 'dimensions':
                return wc_format_dimensions($product->get_dimensions(false));
            case 'categories':
                return wc_get_product_category_list($product->get_id());
            case 'tags':
                return wc_get_product_tag_list($product->get_id());
            case 'rating':
                return wc_get_rating_html($product->get_average_rating());
            case 'review_count':
                return $product->get_review_count();
            case 'stock_status':
                return $product->is_in_stock() ? __('In Stock', 'wootale') : __('Out of Stock', 'wootale');
            default:
                return apply_filters('wootale_shortcode_field', '', $field, $product, array('format' => $format));
        }
    }
}