<?php

namespace Poptics\Core\Frontend;

use Poptics\Core\Campaign\Api_Campaign;
use Poptics\Core\Campaign\Campaign;
use Poptics\Utils\Singleton;

/**
 * Frontend hook class
 *
 * @since 1.0.0
 *
 * @package Poptics
 */
class Hook {
    use Singleton;

    /**
     * Initializing frontend hook
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init() {
        add_action( 'wp_footer', [$this, 'add_footer_content'] );
        add_action( 'wp_head', [$this, 'add_header_content'] );
    }

    /**
     * Add content to the footer
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_footer_content() {
        // Prepare page information
        $page_id = get_the_ID();
        if ( function_exists( 'is_shop' ) && is_shop() ) {
            $page_id = wc_get_page_id( 'shop' );
        }

        $page_info = wp_json_encode( ['id' => $page_id] );

        // Get currency
        $currency = get_currency();

        // Prepare localized object
        $localize_obj = apply_filters( 'poptics_frontend_localize_data', [
            'site_url'     => site_url(),
            'currency'     => $currency,
            'pt_woo_nonce' => wp_create_nonce( 'wc_store_api' ),
        ] );

        // Localize the script with data
        wp_localize_script( 'poptics-frontend-script', 'poptics', $localize_obj );

        $items = $this->get_campaigns();

        if ( !empty( $items ) ) {
            // Enqueue necessary scripts
            wp_enqueue_script( 'poptics-frontend-script' );
            wp_enqueue_script( 'poptics-guten-block' );
            wp_enqueue_script( 'wc-cart-fragments' );
        }

        // Prepare campaign data for frontend
        $jsonString   = wp_json_encode( $items );
        $base64String = base64_encode( $jsonString );
        ?>

    <div id="poptics-popup-wrapper" page-info='<?php echo esc_attr( $page_info ); ?>'>
        <div data-campaigns="<?php echo esc_attr( $base64String ); ?>" id="active-campaigns"></div>
    </div>
    <?php
}

    /**
     * Enqueues the frontend styles for the Poptics plugin.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_header_content() {
        $items = $this->get_campaigns();

        if ( !empty( $items ) ) {
            wp_enqueue_style( 'poptics-frontend' );
        }
    }

    /**
     * Get active campaigns
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_campaigns() {
        // Fetch active campaigns
        $api_campaign     = new Api_Campaign();
        $active_campaigns = Campaign::all( [
            'posts_per_page' => -1,
            'paged'          => -1,
            'post_status'    => ['active', 'scheduled'],
        ] );

        // Filter and prepare campaign items
        $items = array_filter( array_map( function ( $item ) use ( $api_campaign ) {
            $single_campaign = $api_campaign->prepare_item( $item->ID );

            // Check if the campaign has a schedule
            if ( !empty( $single_campaign['controls']['schedule']['fixed'] ) || !empty( $single_campaign['controls']['schedule']['repeating'] ) ) {
                $is_current_time_in_schedule = false;
                $does_cart_conditions_match  = true;

                // Check if current time is within fixed schedule
                if ( !empty( $single_campaign['controls']['schedule']['fixed'] ) ) {
                    $is_current_time_in_schedule = is_current_time_in_schedule( $single_campaign['controls']['schedule'] );
                }

                // Check if current time is within repeating schedule
                if ( !empty( $single_campaign['controls']['schedule']['repeating'] ) ) {
                    $is_current_time_in_schedule = apply_filters( 'poptics_is_time_in_range', $single_campaign['controls']['schedule'] );
                }

                if ( !empty( $single_campaign['controls']['ecommerce_popup_rules'] ) && is_array( $single_campaign['controls']['ecommerce_popup_rules'] ) ) {
                    $does_cart_conditions_match = apply_filters( 'poptics_does_cart_conditions_match', $single_campaign['controls']['ecommerce_popup_rules'] );
                }

                // Return campaign if it's within the schedule
                return ( $is_current_time_in_schedule && $does_cart_conditions_match ) ? $api_campaign->prepare_item( $item->ID ) : null;
            }

            return $api_campaign->prepare_item( $item->ID );
        }, $active_campaigns['items'] ) );

        // Apply filters to campaigns
        $items = apply_filters( 'poptics_filter_campaigns', $items );
        $items = apply_filters( 'poptics_filter_ab_test_campaigns', $items );

        return $items;
    }
}
