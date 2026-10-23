<?php

namespace Poptics\Core\Campaign;

use DateTime;
use DateTimeZone;
use Poptics\Base\PostModel;
use WP_Query;

/**
 * Class Campaign
 *
 * @since 1.0.0
 *
 * @package Poptics
 */
class Campaign extends PostModel {

    /**
     * Store post type
     *
     * @since 1.0.0
     *
     * @var string $post_type
     */
    public $post_type = 'poptics-campaign';

    /**
     * Store id
     *
     * @since 1.0.0
     *
     * @var int $id
     */
    protected $id;

    /**
     * Store post metadata
     *
     * @since 1.0.0
     *
     * @var array $data
     */
    public $data = [
        'name'     => '',
        'goal'     => '',
        'type'     => '',
        'steps'    => [],
        'controls' => [],
        'status'   => '',
        'blog_id'  => '',
    ];

    /**
     * Store meta key prefix
     *
     * @since 1.0.0
     *
     * @var string $prefix
     */
    public $prefix = '_pt_campaign_';

    /**
     * Campaign Constructor
     *
     * @since 1.0.0
     *
     * @param int $campaign Optional. Default 0.
     * @return void
     */
    public function __construct( $campaign = 0 ) {
        if ( $campaign instanceof self ) {
            $this->set_id( $campaign->get_id() );
        } elseif ( !empty( $campaign->ID ) ) {
            $this->set_id( $campaign->ID );
        } elseif ( is_numeric( $campaign ) && $campaign > 0 ) {
            $this->set_id( $campaign );
        }
    }

    /**
     * Get campaign id.
     *
     * @since 1.0.0
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get campaign slug
     *
     * @since 1.0.0
     *
     * @return  mixed
     */
    public function get_slug() {
        $post = get_post( $this->get_id() );

        if ( $post ) {
            return $post->post_name;
        }

        return false;
    }

    /**
     * Get campaign name.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name() {
        return get_post_field( 'post_title', $this->id );
    }

    /**
     * Get campaign goal.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_goal() {
        $data = [
            'term_id' => $this->get_prop( 'goal_term_id' ),
            'name'    => $this->get_prop( 'goal_name' ),
            'icon'    => $this->get_prop( 'goal_icon' ),
        ];
        return $data;
    }

    /**
     * Get campaign type.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_type() {
        $data = [
            'term_id' => $this->get_prop( 'type_term_id' ),
            'name'    => $this->get_prop( 'type_name' ),
            'icon'    => $this->get_prop( 'type_icon' ),
        ];
        return $data;
    }

    /**
     * Get campaign steps.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_steps() {
        return $this->get_prop( 'steps' );
    }

    /**
     * Get campaign controls.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_controls() {
        return $this->get_prop( 'controls' );
    }

    /**
     * Get all integrations for a campaign
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_integrations() {
        $integrations = (array) $this->get_prop( 'integrations' );
        return $integrations;
    }

    /**
     * Get all data for a campaign
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_data() {
        return [
            'name'     => $this->get_name(),
            'goal'     => $this->get_goal(),
            'type'     => $this->get_type(),
            'steps'    => $this->get_steps(),
            'controls' => $this->get_controls(),
            'status'   => $this->get_status(),
        ];
    }

    /**
     * Get permalink
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_link() {
        return get_post_permalink( $this->id );
    }

    /**
     * Get campaign author
     *
     * @since 1.0.0
     *
     * @return  integer
     */
    public function get_author() {
        $post = get_post( $this->id );

        return !is_null( $post ) ? $post->post_author : 0;
    }

    /**
     * Get campaign status
     *
     * @since 1.0.0
     *
     * @return  integer
     */
    public function get_status() {
        $post_status = get_post_status( $this->id );

        if ( $post_status == 'scheduled' ) {
            $is_scheduled_over = $this->is_campaign_schedule_over();

            if ( $is_scheduled_over ) {
                wp_update_post( [
                    'ID'          => $this->id,
                    'post_status' => 'completed',
                ] );
            }
        }

        $post_status = get_post_status( $this->id );

        return $post_status;
    }

    /**
     * Check if campaign is a pro campaign
     *
     * @since 1.0.0
     *
     * @return  boolean
     */
    public function get_is_pro() {
        $is_pro = $this->get_prop( 'is_pro' );
        if ( $is_pro == 'true' ) {
            $is_pro = true;
        } else {
            $is_pro = false;
        }

        return $is_pro;
    }

    /**
     * Get campaign data
     *
     * @since 1.0.0
     *
     * @param   string  $prop
     *
     * @return  mixed
     */
    public function get_prop( $prop = '' ) {
        return $this->get_metadata( $prop );
    }

    /**
     * Get metadata
     *
     * @since 1.0.0
     *
     * @param string $prop Optional. Default empty string.
     *
     * @return  mixed
     */
    private function get_metadata( $prop = '' ) {
        $meta_key = $this->prefix . $prop;
        return get_post_meta( $this->id, $meta_key, true );
    }

    /**
     * Check if campaign is in ab test
     *
     * @since 1.0.5
     *
     * @return  boolean
     */
    public function in_ab_test() {
        $id = $this->get_id();

        $ab_tested_campaigns = [];
        $all_active_ab_tests = get_posts( [
            'post_type'      => 'poptics-ab-testing',
            'posts_per_page' => -1,
            'post_status'    => ['draft', 'publish', 'paused'],
        ] );

        foreach ( $all_active_ab_tests as $test ) {
            $campaigns           = get_post_meta( $test->ID, '_pt_ab_testing_campaigns', true );
            $campaign_ids        = array_column( $campaigns, 'id' );
            $ab_tested_campaigns = array_merge( $ab_tested_campaigns, $campaign_ids );
        }
        if ( in_array( $id, $ab_tested_campaigns ) ) {
            return true;
        }

        return false;
    }

    /**
     * Set campaign id
     *
     * @since 1.0.0
     *
     * @param int $id
     *
     * @return void
     */
    public function set_id( $id ) {
        $this->id = $id;
    }

    /**
     * Set props
     *
     * @since 1.0.0
     *
     * @param array  $data Metadata array. Default empty array.
     *
     * @return  void
     */
    public function set_props( $data = [] ) {
        $this->data = $data;
    }

    /**
     * Save campaign
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function save() {
        $args = [
            'post_title'  => $this->data['name'],
            'post_type'   => $this->post_type,
            'post_status' => $this->data['status'],
            'post_author' => get_current_user_id(),
        ];

        if ( !empty( $this->id ) ) {
            $args['ID'] = $this->id;
        }

        $campaign_id = wp_insert_post( $args );
        if ( !is_wp_error( $campaign_id ) ) {
            $this->set_id( $campaign_id );
            $this->save_metadata();
            // Clear campaign meta cache
            $this->clear_campaign_meta_cache();
        }

        return $campaign_id;
    }

    /**
     * Update popup campaign meta data
     *
     * @since 1.0.0
     *
     * @return  void
     */
    private function save_metadata() {
        foreach ( $this->data as $key => $value ) {
            if ( !in_array( $key, ['status', 'name'] ) ) {
                if ( !array_key_exists( $key, $this->data ) ) {
                    continue;
                }

                if ( in_array( $key, ['type', 'goal'] ) ) {
                    $campaign_type_details = $value;
                    foreach ( $campaign_type_details as $index => $value ) {
                        $meta_key = $this->prefix . $key . '_' . $index;

                        if ( !$value ) {
                            $value = $this->get_prop( $key . '_' . $index );
                        }

                        update_post_meta( $this->id, $meta_key, $value );
                    }
                } elseif ( $key == 'integrations' ) {
                    $meta_key = $this->prefix . $key;

                    update_post_meta( $this->id, $meta_key, $value );
                } else {
                    $meta_key = $this->prefix . $key;

                    if ( !$value ) {
                        $value = $this->get_prop( $key );
                    }
                    update_post_meta( $this->id, $meta_key, $value );
                }
            }
        }
    }

    /**
     * Delete campaign
     *
     * @since 1.0.0
     *
     * @return bool | WP_Error
     */
    public function delete() {
        $result = wp_delete_post( $this->id, true );
        if ( $result ) {
            // Clear campaign meta cache
            $this->clear_campaign_meta_cache();
        }
        return $result;
    }

    /**
     * Check the campaign is valid or not
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_campaign() {
        $post = get_post( $this->id );

        if ( $post && $this->post_type === $post->post_type ) {
            return true;
        }

        return false;
    }

    /**
     * Get all campaigns
     *
     * @since 1.0.0
     *
     * @param array $args Campaign args. Default empty array.
     *
     * @return array
     */
    public static function all( $args = [] ) {
        $defaults = [
            'post__in'       => [],
            'post_type'      => ( new self )->post_type,
            'posts_per_page' => 20,
            'paged'          => 1,
            'orderby'        => 'ID',
            'order'          => 'DESC',
            'post_status'    => poptics_get_all_related_post_status(),
        ];

        $args = wp_parse_args( $args, $defaults );

        if ( !empty( $args['goal'] ) ) {
            $args['meta_query'][] = [
                'key'     => '_pt_campaign_goal_term_id',
                'value'   => $args['goal'],
                'compare' => '=',
            ];
        }

        if ( !empty( $args['type'] ) ) {
            $args['meta_query'][] = [
                'key'     => '_pt_campaign_type_term_id',
                'value'   => $args['type'],
                'compare' => '=',
            ];
        }

        if ( !empty( $args['search_key'] ) ) {
            $args['s'] = $args['search_key'];
        }

        $post = new WP_Query( $args );

        return [
            'total' => $post->found_posts,
            'items' => $post->posts,
        ];
    }

    /**
     * clone campaign
     *
     * @since 1.0.0
     *
     * @return  void
     */
    public function clone () {
        $this->set_props( $this->get_data() );

        $this->set_id( 0 );
        $this->save();
    }

    /**
     * Fetch status wise total campaign count
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function total_campaigns_group_by_status() {
        // Try to get from cache first
        $cache_key = 'poptics_campaign_status_count';
        $status_count = wp_cache_get( $cache_key, 'poptics_campaign' );

        if ( false !== $status_count ) {
            return $status_count;
        }

        // Use wp_count_posts() which is optimized and cached by WordPress
        $post_type = ( new self )->post_type;
        $counts = wp_count_posts( $post_type );

        $all_status = poptics_get_post_status();
        $all_status = array_merge( $all_status, ['trash', 'draft'] );

        // Initialize with zeros
        $status_count = [];
        foreach ( $all_status as $status ) {
            $status_count[$status] = 0;
        }

        // Map the counts from wp_count_posts
        foreach ( $counts as $status => $count ) {
            if ( isset( $status_count[$status] ) ) {
                $status_count[$status] = (int) $count;
            }
        }

        // Cache the result
        wp_cache_set( $cache_key, $status_count, 'poptics_campaign' );

        return $status_count;
    }

    /**
     * Retrieves the meta values of a specific meta key for all campaigns.
     *
     * @since 1.0.0
     *
     * @param string $meta_key The meta key to retrieve values for.
     * @return array An array of meta values, where each element is an associative array with keys 'term_id', 'name', and 'icon'.
     */
    public static function get_campaign_meta_value( $meta_key ) {
        // Create cache key based on meta_key and post statuses
        $post_statuses = poptics_get_all_related_post_status();
        $cache_key     = 'poptics_campaign_meta_' . md5( $meta_key . serialize( $post_statuses ) );

        // Try to get from object cache
        $meta_values = wp_cache_get( $cache_key, 'poptics_campaign' );

        if ( false !== $meta_values ) {
            return $meta_values;
        }

        $args = [
            'post_type'      => ( new self )->post_type,
            'posts_per_page' => -1,
            'post_status'    => $post_statuses,
            'fields'         => 'ids', // Only get IDs, not full post objects - saves memory
            'no_found_rows'  => true,  // Skip pagination counting query
        ];

        $campaign_ids = get_posts( $args );

        // Prime the meta cache for all campaigns at once - prevents N+1 queries
        update_meta_cache( 'post', $campaign_ids );

        $meta_values = [];
        $term_ids    = [];

        if ( in_array( $meta_key, ['_pt_campaign_type', '_pt_campaign_goal'] ) ) {
            $meta_keys = ['term_id', 'name', 'icon'];

            foreach ( $campaign_ids as $campaign_id ) {
                $meta_value = [];

                foreach ( $meta_keys as $key ) {
                    $value = get_post_meta( $campaign_id, $meta_key . '_' . $key, true );
                    $meta_value[$key] = $value;
                }

                if ( !in_array( $meta_value['term_id'], $term_ids ) && !empty( $meta_value['term_id'] ) ) {
                    $meta_values[] = $meta_value;
                    $term_ids[]    = $meta_value['term_id'];
                }
            }
        } else {
            foreach ( $campaign_ids as $campaign_id ) {
                $value = get_post_meta( $campaign_id, $meta_key, true );
                if ( !empty( $value ) && !in_array( $value, $meta_values ) ) {
                    $meta_values[] = $value;
                }
            }
        }

        // Cache the result - will be cleared on campaign save/delete
        wp_cache_set( $cache_key, $meta_values, 'poptics_campaign' );

        return $meta_values;
    }

    /**
     * Clear campaign meta cache
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function clear_campaign_meta_cache() {
        // Clear all possible meta cache keys
        $post_statuses = poptics_get_all_related_post_status();
        $meta_keys = ['_pt_campaign_type', '_pt_campaign_goal'];

        foreach ( $meta_keys as $meta_key ) {
            $cache_key = 'poptics_campaign_meta_' . md5( $meta_key . serialize( $post_statuses ) );
            wp_cache_delete( $cache_key, 'poptics_campaign' );
        }

        // Also clear status count cache
        wp_cache_delete( 'poptics_campaign_status_count', 'poptics_campaign' );
    }

    /**
     * Checks if a campaign has a schedule.
     *
     * @param array $controls The campaign controls.
     *
     * @return bool True if the campaign has a schedule, false otherwise.
     */
    public function is_campaign_scheduled() {
        if ( !empty( $this->data['controls']['schedule']['fixed'] ) ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a campaign schedule is over.
     *
     * @return bool True if the campaign schedule is over, false otherwise.
     */
    function is_campaign_schedule_over() {
        $is_scheduled_over = false;
        $controls          = $this->get_controls();
        $schedule          = $controls['schedule'];

        foreach ( $schedule['fixed'] as $schedule_entry ) {
            // Get the timezone of the schedule
            $timezone     = new DateTimeZone( $schedule_entry['timezone'] );
            $current_time = new DateTime( 'now', $timezone );

            // Loop through the duration ranges (they are pairs of start and end times)
            $durations = $schedule_entry['duration'];

            // Convert the start and end time of each duration to DateTime objects
            $end_time = new DateTime( $durations[1] );
            $end_time->setTimezone( $timezone );
            // Check if current time is within the duration
            if ( $current_time > $end_time ) {
                return true; // Current time is within the duration
            }

        }

        // If no duration includes the current time, return false
        return $is_scheduled_over;
    }
}
