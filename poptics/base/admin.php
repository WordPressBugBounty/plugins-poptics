<?php

namespace Poptics\Base;
use Poptics;

/**
 * Admin class
 *
 * @since 1.0.0
 *
 * @package Poptics
 */
class Admin {
    use \Poptics\Utils\Singleton;

    /**
     * Initialize the class
     *
     * @since 1.0.0
     *
     * @return  void
     */
    public function init() {
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'], 10 );
    }

    /**
     * Enqueue scripts and styles
     *
     * @since 1.0.0
     *
     * @return  void
     */
    public function enqueue_scripts( $handle ) {

        if('plugins.php' === $handle){

            wp_enqueue_script( 'poptics-feedback-modal');
            wp_register_style( "poptics-feedback-modal-style", Poptics::url( 'assets/build/css/feedback-modal.css' ), [], \Poptics::get_version() );
            wp_enqueue_style("poptics-feedback-modal-style", Poptics::url( 'assets/build/css/feedback-modal.css' ), [], \Poptics::get_version() );

             $feedback_obj = array(
                'site_url'               => site_url(),
            );

            wp_localize_script( 'poptics-feedback-modal', 'poptics_feedback', $feedback_obj );
        }

        
        if ( 'toplevel_page_poptics' !== $handle ) {
            return;
        }

        wp_enqueue_media();

        // Get the handle of the global styles inline CSS
        $global_styles = wp_get_global_stylesheet();

          // Only load if global styles are available
        if ( $global_styles && 'toplevel_page_poptics' === $handle ) {
            // Output global styles as inline CSS in the admin area
            wp_add_inline_style( 'wp-admin', $global_styles );
        }

        wp_enqueue_script( 'wp-edit-post' );
        wp_enqueue_script( 'poptics-script' );
        wp_enqueue_script( 'poptics-packages' );
        wp_enqueue_script( 'poptics-guten-block' );
        wp_set_script_translations( 'poptics-script', 'poptics' );
        wp_enqueue_style( 'poptics-style' );
        wp_enqueue_style( 'wp-block-editor' );

        $page_list = apply_filters( 'poptics_get_cpt_wise_dynamic_posts', get_all_post_types_with_posts() );

        $category_list = poptics_all_post_categories();
        $tag_list      = poptics_all_post_tags();

        $page_list = array_merge( $page_list, $category_list, $tag_list );

        $currency = get_currency();

        $localize_obj = array(
            'site_url'               => site_url(),
            'admin_url'              => admin_url(),
            'nonce'                  => wp_create_nonce( 'wp_rest' ),
            'currency'               => $currency,
            'post_categories'        => poptics_all_post_categories(),
            'post_tags'              => poptics_all_post_tags(),
            'page_lists'             => $page_list,
            'is_woo_commerce_active' => class_exists( 'WooCommerce' ) ? 1 : 0,
            'is_edd_active'          => class_exists( 'Easy_Digital_Downloads' ) ? 1 : 0,
        );

        $localize_obj = apply_filters( 'poptics_admin_localize_data', $localize_obj );

        wp_localize_script( 'poptics-script', 'poptics', $localize_obj );

        $settings = poptics_editor_settings();
        wp_add_inline_script( 'poptics-script', 'window.popticsEditorSettings = ' . wp_json_encode( $settings ) . ';' );
    }
}