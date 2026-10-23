<?php

namespace Poptics\Core\Template;

use Poptics\Base\Api;
use Poptics\Utils\Singleton;
use WP_HTTP_Response;

/**
 * Class API Campaign
 *
 * @since 1.0.0
 *
 * @package Poptics
 */
class Api_Template extends Api {

    use Singleton;

    /**
     * Store namespace
     *
     * @since 1.0.0
     *
     * @var string
     */
    protected $namespace = 'poptics/v1';

    /**
     * Store rest base
     *
     * @since 1.0.0
     *
     * @var string
     */
    protected $rest_base = 'remote';

    /**
     * Register rest routes.
     *
     * @since 1.0.0
     *
     * @return  void
     */
    public function register_routes() {
        /**
         * Register popup-templates category list API
         */
        register_rest_route( $this->namespace, $this->rest_base . '/categories', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'template_categories'],
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            ],
        ] );

        /**
         * Register popup-templates API
         */
        register_rest_route( $this->namespace, $this->rest_base . '/templates', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'templates'],
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            ],
        ] );
    }

    /**
     * Fetch template categories
     *
     * @since 1.0.0
     *
     * @return \WP_REST_Response
     */
    public function template_categories() {
        $cache_key   = 'poptics_categories_data';
        $cached_data = get_transient( $cache_key );

        if ( !$cached_data ) {
            $response = wp_remote_get( 'https://demo.aethonic.com/poptics/wp-json/poptics/v1/categories' );
            if ( is_wp_error( $response ) ) {
                if ( is_wp_error( $response ) ) {
                    $response = [
                        'success'     => 0,
                        'status_code' => 500,
                        'message'     => __( 'Unable to fetch template categories', 'poptics' ),
                        'data'        => [],
                    ];

                    return rest_ensure_response( $response, 500 );
                }
            }

            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );

            set_transient( $cache_key, $data, 86400 );
        } else {
            $data = $cached_data;
        }

        return rest_ensure_response( $data, 200 );
    }

    /**
     * Retrieve templates from Aethonic demo site.
     *
     * Retrieves templates from Aethonic demo site and cache them for 10 seconds.
     * If the transient is not set or is expired, it will call the Aethonic demo site
     * to fetch the templates.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function templates( $request ) {
        $response = poptics_verify_nonce( $request->get_header( 'x_wp_nonce' ) );
        if ( $response instanceof WP_HTTP_Response ) {
            return $response;
        }

        $cache_key   = 'poptics_templates_data';
        $cache_group = 'poptics';
        $cache_keys  = wp_cache_get( "{$cache_key}_keys", $cache_group ) ?: get_transient( "{$cache_key}_keys" );

        $cache_fields = ['total', 'types', 'goals', 'events', 'categories'];
        $cached_data  = array_combine(
            $cache_fields,
            array_map( fn( $field ) => wp_cache_get( "{$cache_key}_{$field}", $cache_group ) ?: get_transient( "{$cache_key}_{$field}" ), $cache_fields )
        );

        $cached_items = [];
        if ( !empty( $cache_keys ) ) {
            foreach ( $cache_keys as $index ) {
                $item = wp_cache_get( "{$cache_key}_{$index}", $cache_group ) ?: get_transient( "{$cache_key}_{$index}" );
                if ( $item !== false ) {
                    $cached_items[$index] = $item;
                }
            }
        }
        
        if ( is_array( $cached_items ) && is_array(  $cache_keys ) && count( $cached_items ) === count( $cache_keys ) )
        {
            return rest_ensure_response( [
                'success'     => 1,
                'status_code' => 200,
                'message'     => __( 'Templates loaded from cache', 'poptics' ),
                'data'        => array_merge( $cached_data, ['items' => array_values( $cached_items )] ),
            ], 200 );
        }

        $response = wp_remote_get( 'https://demo.aethonic.com/poptics/wp-json/poptics/v1/templates', [
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return rest_ensure_response( [
                'success'     => 0,
                'status_code' => 500,
                'message'     => __( 'Unable to fetch templates', 'poptics' ),
                'data'        => [],
            ], 500 );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( !isset( $data['data']['items'] ) || !is_array( $data['data']['items'] ) ) {
            return rest_ensure_response( ['error' => 'Invalid data received'], 500 );
        }

        $items      = $data['data']['items'];
        $items_keys = array_keys( $items );

        foreach ( array_merge( $cache_fields, ['keys'] ) as $field ) {
            wp_cache_set( "{$cache_key}_{$field}", $data['data'][$field] ?? $items_keys, $cache_group, 86400 );
            set_transient( "{$cache_key}_{$field}", $data['data'][$field] ?? $items_keys, 86400 );
        }

        foreach ( $items as $index => $item ) {
            wp_cache_set( "{$cache_key}_{$index}", $item, $cache_group, 86400 );
            set_transient( "{$cache_key}_{$index}", $item, 86400 );
        }

        return rest_ensure_response( [
            'success'     => 1,
            'status_code' => 200,
            'message'     => __( 'Templates loaded successfully', 'poptics' ),
            'data'        => array_merge( $data['data'], ['items' => array_values( $items )] ),
        ], 200 );
    }
}