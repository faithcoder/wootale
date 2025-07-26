<?php

/**
 * Template rendering functionality
 */
class WooTale_Template_Renderer {

    /**
     * Render WordPress Page as template
     */
    public function render_page_template($page_id, $product) {
        $page = get_post($page_id);
        
        if (!$page || $page->post_status !== 'publish') {
            echo '<div class="wootale-template-error">' . __('Template page not found or not published.', 'wootale') . '</div>';
            return;
        }
        
        global $wootale_current_product;
        $wootale_current_product = $product;
        
        echo '<div class="wootale-page-template" data-page-id="' . esc_attr($page_id) . '">';
        
        $content = $page->post_content;
        $placeholders = new WooTale_Placeholders();
        $content = $placeholders->replace_placeholders($content, $product);
        
        $content = do_shortcode($content);
        $content = apply_filters('the_content', $content);
        
        echo $content;
        echo '</div>';
        
        $wootale_current_product = null;
    }

    /**
     * Render Elementor Template
     */
    public function render_elementor_template($template_id, $product) {
        if (!defined('ELEMENTOR_VERSION')) {
            echo '<div class="wootale-template-error">' . __('Elementor is not active.', 'wootale') . '</div>';
            return;
        }
        
        $template = get_post($template_id);
        
        if (!$template || $template->post_status !== 'publish') {
            echo '<div class="wootale-template-error">' . __('Elementor template not found or not published.', 'wootale') . '</div>';
            return;
        }
        
        global $wootale_current_product;
        $wootale_current_product = $product;
        
        echo '<div class="wootale-elementor-template" data-template-id="' . esc_attr($template_id) . '">';
        
        if (class_exists('\Elementor\Plugin')) {
            $elementor_instance = \Elementor\Plugin::instance();
            $content = $elementor_instance->frontend->get_builder_content_for_display($template_id);
            
            $placeholders = new WooTale_Placeholders();
            $content = $placeholders->replace_placeholders($content, $product);
            echo $content;
        } else {
            $content = get_post_field('post_content', $template_id);
            $placeholders = new WooTale_Placeholders();
            $content = $placeholders->replace_placeholders($content, $product);
            echo do_shortcode($content);
        }
        
        echo '</div>';
        
        $wootale_current_product = null;
    }
}