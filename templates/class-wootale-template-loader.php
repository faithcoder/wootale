<?php

/**
 * Template loading functionality
 */
class WooTale_Template_Loader {

    /**
     * Get available templates (Pages + Elementor)
     */
    public function get_available_templates() {
        $templates = array(
            '' => __('Default WooCommerce Description', 'wootale'),
        );
        
        // Get WordPress Pages
        $pages_args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        $only_template_pages = get_option('wootale_only_template_pages', 'no');
        if ($only_template_pages === 'yes') {
            $pages_args['meta_key'] = '_wootale_is_template';
            $pages_args['meta_value'] = '1';
        }
        
        $pages = get_posts($pages_args);
        
        foreach ($pages as $page) {
            $templates['page_' . $page->ID] = __('Page:', 'wootale') . ' ' . $page->post_title;
        }
        
        // Get Elementor Templates
        if (defined('ELEMENTOR_VERSION')) {
            $elementor_templates = get_posts(array(
                'post_type' => 'elementor_library',
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => '_elementor_template_type',
                        'value' => array('page', 'section', 'widget'),
                        'compare' => 'IN'
                    )
                )
            ));
            
            foreach ($elementor_templates as $template) {
                $template_type = get_post_meta($template->ID, '_elementor_template_type', true);
                $templates['elementor_' . $template->ID] = sprintf(
                    __('Elementor %s:', 'wootale'),
                    ucfirst($template_type)
                ) . ' ' . $template->post_title;
            }
        }
        
        return apply_filters('wootale_custom_templates', $templates);
    }

    /**
     * Main function to load product description template
     */
    public function load_template($product) {
        if (!$product || !is_object($product)) {
            return;
        }
        
        $product_id = $product->get_id();
        $selected_template = get_post_meta($product_id, '_wootale_product_template', true);
        
        if (!empty($selected_template)) {
            $renderer = new WooTale_Template_Renderer();
            
            if (strpos($selected_template, 'page_') === 0) {
                $template_id = str_replace('page_', '', $selected_template);
                $renderer->render_page_template($template_id, $product);
                return;
            } elseif (strpos($selected_template, 'elementor_') === 0) {
                $template_id = str_replace('elementor_', '', $selected_template);
                $renderer->render_elementor_template($template_id, $product);
                return;
            }
        }
        
        // Fallback to WooCommerce default description
        echo '<div class="wootale-default-description">';
        echo wpautop($product->get_description());
        echo '</div>';
    }

    /**
     * Product data shortcode
     */
    public function product_data_shortcode($atts) {
        global $wootale_current_product, $product;
        
        $current_product = $wootale_current_product ? $wootale_current_product : $product;
        
        if (!$current_product) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'field' => 'name',
            'format' => 'text'
        ), $atts, 'wootale_product_data');
        
        $placeholders = new WooTale_Placeholders();
        return $placeholders->get_product_data($current_product, $atts['field'], $atts['format']);
    }
}