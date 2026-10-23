<?php
// All the global helper functions will go here.
defined( 'ABSPATH' ) || exit;

if ( !function_exists( 'poptics_get_post_status' ) ) {

    /**
     * Get post status
     *
     * @return  array
     */
    function poptics_get_post_status() {
        return array(
            'active',
            'paused',
            'scheduled',
            'completed',
        );
    }

}

if ( !function_exists( 'poptics_get_all_related_post_status' ) ) {

    /**
     * Get all related post status
     *
     * @return  array
     */
    function poptics_get_all_related_post_status() {
        return array(
            'active',
            'paused',
            'scheduled',
            'completed',
            'trash',
            'draft',
        );
    }

}

if ( !function_exists( 'poptics_sanitize_recursive' ) ) {
    /**
     * Recursively sanitize nested array of objects input field
     *
     * @since 1.0.0
     *
     * @param array|object|string $input Input data to sanitize.
     *
     * @return array|object|string Sanitized input.
     */
    function poptics_sanitize_recursive( $input ) {

        if ( is_array( $input ) ) {

            foreach ( $input as $key => $value ) {
                $input[$key] = poptics_sanitize_recursive( $value );
            }

        } elseif ( is_object( $input ) ) {

            foreach ( $input as $key => $value ) {
                $input->$key = poptics_sanitize_recursive( $value );
            }

        } else {
            if ( filter_var( $input, FILTER_VALIDATE_URL ) !== false ) {
                $input = sanitize_url( $input );
            } elseif ( is_email( $input ) ) {
                $input = sanitize_email( $input );
            } elseif ( is_int( $input ) ) {
                $input = intval( $input );
            } elseif ( is_float( $input ) ) {
                $input = floatval( $input );
            } elseif ( is_string( $input ) ) {
                if ( strpos( $input, '<svg' ) === false ) {
                    $input = wp_kses_post( $input );
                }
            }
        }

        return $input;
    }
}

if ( !function_exists( 'poptics_editor_settings' ) ) {
    /**
     * Retrieves the settings for the Gutenberg editor.
     *
     * This function retrieves the settings for the Gutenberg editor, including the allowed block types,
     * typography, color palette, and other experimental features. It also applies filters to allow
     * customization of the settings.
     *
     * @return array The settings for the Gutenberg editor.
     */
    function poptics_editor_settings() {
        $coreSettings            = get_block_editor_settings( array(), 'post' );
        $wordpressCoreTypography = $coreSettings['__experimentalFeatures']['typography'];
        $coreExperimentalSpacing = $coreSettings['__experimentalFeatures']['spacing'];

        $allowedBlocks = array(
            'core/paragraph',
            'core/heading',
            'core/buttons',
            'core/image',
            'core/columns',
            'core/list',
            'core/list-item',
            'core/freeform',
            'core/html',
            'core/spacer',
            'core/subhead',
            'core/table',
            'core/verse',
            'core/group',
            'core/column',
            'core/buttons',
            'core/button',
            'core/cover',
            'core/rss',
            'core/audio',
            'core/video',
            'core/quote',
            'core/gallery',
            'core/file',
            'core/code',
            'core/preformatted',
            'core/pullquote',
            'core/media-text',
            'core/more',
            'core/nextpage',
            'core/separator',
            'core/shortcode',
            'core/embed',
            'core/latest-posts',
            'core/latest-comments',
            'core/archives',
            'core/categories',
            'core/tag-cloud',
            'core/search',
            'poptics/cover',
            'poptics/custom-form',
            'poptics/custom-input',
            'poptics/style-settings',
            'poptics/countdown',
            'poptics/custom-button',
            'poptics/heading',
            'poptics/image',
            'poptics/custom-image',
            'poptics/woo-commerce',
            'poptics/container',
            'poptics/icon',
            'poptics/funnel-button',
        );

        $allowedBlocks = apply_filters( 'poptics_allowed_block_types', $allowedBlocks );
        $themePref     = poptics_get_theme_pref_scheme();

        $settings = array(
            'gradients'                         => array(),
            'alignWide'                         => false,
            'allowedMimeTypes'                  => get_allowed_mime_types(),
            'allowedBlockTypes'                 => $allowedBlocks,
            '__experimentalBlockPatterns'       => array(),
            '__experimentalFeatures'            => array(
                'appearanceTools' => true,
                'border'          => array(
                    'color'  => false,
                    'radius' => true,
                    'style'  => false,
                    'width'  => false,
                ),
                'color'           => array(
                    'background'       => true,
                    'customDuotone'    => false,
                    'defaultGradients' => false,
                    'defaultPalette'   => false,
                    'duotone'          => array(),
                    'gradients'        => array(),
                    'link'             => false,
                    'palette'          => array(
                        'theme' => $themePref['colors'],
                    ),
                    'text'             => true,
                ),
                'spacing'         => $coreExperimentalSpacing,
                'typography'      => $wordpressCoreTypography,
                'blocks'          => array(
                    'core/button' => array(
                        'border'     => array(
                            'radius' => true,
                            "style"  => true,
                            "width"  => true,
                        ),
                        'typography' => array(
                            'fontSizes' => array(),
                        ),
                        'spacing'    => $coreExperimentalSpacing,
                    ),

                ),
            ),
            '__experimentalSetIsInserterOpened' => false,
            'disableCustomColors'               => get_theme_support( 'disable-custom-colors' ),
            'disableCustomFontSizes'            => false,
            'disableCustomGradients'            => true,
            'enableCustomLineHeight'            => get_theme_support( 'custom-line-height' ),
            'enableCustomSpacing'               => get_theme_support( 'custom-spacing' ),
            'enableCustomUnits'                 => false,
            'keepCaretInsideBlock'              => false,
        );

        $color_palette = current( (array) get_theme_support( 'editor-color-palette' ) );

        if ( false !== $color_palette ) {
            $settings['colors'] = $color_palette;
        } else {
            $settings['colors'] = array();
        }

        return $settings;
    }

}

if ( !function_exists( 'poptics_get_theme_pref_scheme' ) ) {
    /**
     * Retrieves the theme preference scheme.
     *
     * This function retrieves the theme preference scheme, which includes the color palette and font sizes.
     * The color palette is an array of objects, each representing a color with its name, slug, and hex code.
     * The font sizes is an array of objects, each representing a font size with its name, short name, size, and slug.
     * The function applies filters to allow customization of the theme preference scheme.
     *
     * @return array The theme preference scheme.
     */
    function poptics_get_theme_pref_scheme() {
        static $pref;

        if ( !$pref ) {

            $color_palette = array(
                array(
                    "name"  => __( "Black", "poptics" ),
                    "slug"  => "black",
                    "color" => "#000000",
                ),
                array(
                    "name"  => __( "Cyan bluish gray", "poptics" ),
                    "slug"  => "cyan-bluish-gray",
                    "color" => "#abb8c3",
                ),
                array(
                    "name"  => __( "White", "poptics" ),
                    "slug"  => "white",
                    "color" => "#ffffff",
                ),
                array(
                    "name"  => __( "Pale pink", "poptics" ),
                    "slug"  => "pale-pink",
                    "color" => "#f78da7",
                ),
                array(
                    "name"  => __( "Luminous vivid orange", "poptics" ),
                    "slug"  => "luminous-vivid-orange",
                    "color" => "#ff6900",
                ),
                array(
                    "name"  => __( "Luminous vivid amber", "poptics" ),
                    "slug"  => "luminous-vivid-amber",
                    "color" => "#fcb900",
                ),
                array(
                    "name"  => __( "Light green cyan", "poptics" ),
                    "slug"  => "light-green-cyan",
                    "color" => "#7bdcb5",
                ),
                array(
                    "name"  => __( "Vivid green cyan", "poptics" ),
                    "slug"  => "vivid-green-cyan",
                    "color" => "#00d084",
                ),
                array(
                    "name"  => __( "Pale cyan blue", "poptics" ),
                    "slug"  => "pale-cyan-blue",
                    "color" => "#8ed1fc",
                ),
                array(
                    "name"  => __( "Vivid cyan blue", "poptics" ),
                    "slug"  => "vivid-cyan-blue",
                    "color" => "#0693e3",
                ),
                array(
                    "name"  => __( "Vivid purple", "poptics" ),
                    "slug"  => "vivid-purple",
                    "color" => "#9b51e0",
                ),
            );

            $font_sizes = array(
                array(
                    'name'      => __( 'Small', 'poptics' ),
                    'shortName' => 'S',
                    'size'      => 14,
                    'slug'      => 'small',
                ),
                array(
                    'name'      => __( 'Medium', 'poptics' ),
                    'shortName' => 'M',
                    'size'      => 18,
                    'slug'      => 'medium',
                ),
                array(
                    'name'      => __( 'Large', 'poptics' ),
                    'shortName' => 'L',
                    'size'      => 24,
                    'slug'      => 'large',
                ),
                array(
                    'name'      => __( 'Larger', 'poptics' ),
                    'shortName' => 'XL',
                    'size'      => 32,
                    'slug'      => 'larger',
                ),
            );

            $pref = apply_filters( 'poptics_theme_pref', array(
                'colors'     => (array) $color_palette,
                'font_sizes' => (array) $font_sizes,
            ) );
        }

        return $pref;
    }
}

if ( !function_exists( 'poptics_verify_nonce' ) ) {

    /**
     * Verifies the given nonce against the 'wp_rest' action.
     *
     * @param string $nonce The nonce to be verified.
     * @return bool Returns true if the nonce is valid, false otherwise.
     */

    function poptics_verify_nonce( $nonce ) {
        if ( ( isset( $nonce ) && wp_verify_nonce( $nonce, 'wp_rest' ) ) || POPTICS_DEBUG_MODE ) {
            return true;
        }

        $response = [
            'success'     => 0,
            'status_code' => 403,
            'message'     => __( 'Nonce not verified', 'poptics' ),
            'data'        => [],
        ];
        return new WP_HTTP_Response( $response, 403 );
    }
}

if ( !function_exists( 'poptics_get_active_posts' ) ) {
    /**
     * Will return all published posts
     *
     * @return void
     */
    function poptics_get_active_posts( $post_type ) {
        $args = array(
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );

        $query = new WP_Query( $args );

        $results = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $results[] = array(
                    'id'    => get_the_ID(),
                    'title' => get_the_title(),
                    'link'  => get_permalink(),
                );
            }
        }

        wp_reset_postdata();

        return $results;
    }
}

if ( !function_exists( 'poptics_delete_campaigns' ) ) {
    /**
     * Deletes all campaigns.
     *
     * @since 1.0.0
     */
    function poptics_delete_campaigns() {
        $posts = get_posts( array(
            'post_type'   => 'poptics-campaign',
            'post_status' => 'any',
            'numberposts' => -1,
        ) );

        foreach ( $posts as $post ) {
            wp_delete_post( $post->ID, true );
        }
    }
}

if ( !function_exists( 'get_email_block_editor_styles' ) ) {
    /**
     * Returns the HTML for the combined block library and global stylesheet for use in email templates.
     *
     * @return string The HTML for the combined CSS styles.
     */
    function get_email_block_editor_styles() {
        // Load wp-block-library styles
        $block_library_css = file_get_contents( ABSPATH . WPINC . '/css/dist/block-library/style.css' );

        // Load global styles (if available)
        $global_styles_css = wp_get_global_stylesheet();

        // Combine both styles
        return $block_library_css . $global_styles_css;
    }
}

if ( !function_exists( 'poptics_all_post_categories' ) ) {

    /**
     * Retrieves all post categories with their permalinks.
     *
     * @since 1.0.0
     * @return array[] An array of categories with their respective options.
     *                 Each category is an associative array containing the category ID as 'id',
     *                 the title of the category as 'title', and the permalink as 'link'.
     */
    function poptics_all_post_categories() {
        $categories_list = [];

        // Get all categories
        $categories = get_categories( array(
            'taxonomy'   => 'category',
            'hide_empty' => false,
        ) );

        $options = [];
        foreach ( $categories as $category ) {
            $options[] = [
                'id'    => $category->term_id,
                'title' => $category->name,
                'link'  => get_category_link( $category->term_id ),
            ];
        }

        // Add to categories_list if options are available
        if ( !empty( $options ) ) {
            $categories_list[] = [
                'key'     => 'category',
                'label'   => __( 'Categories', 'poptics' ),
                'options' => $options,
            ];
        }

        return $categories_list;
    }
}

if ( !function_exists( 'poptics_all_post_tags' ) ) {

    /**
     * Retrieves all post tags with their permalinks.
     *
     * @since 1.0.0
     * @return array[] An array of tags with their respective options.
     *                 Each tag is an associative array containing the tag ID as 'id',
     *                 the title of the tag as 'title', and the permalink as 'link'.
     */
    function poptics_all_post_tags() {
        $tags_list = [];

        // Get all tags
        $tags = get_tags( array(
            'hide_empty' => false,
        ) );

        $options = [];
        foreach ( $tags as $tag ) {
            $options[] = [
                'id'    => $tag->term_id,
                'title' => $tag->name,
                'link'  => get_tag_link( $tag->term_id ),
            ];
        }

        // Add to tags_list if options are available
        if ( !empty( $options ) ) {
            $tags_list[] = [
                'key'     => 'tag',
                'label'   => __( 'Tags', 'poptics' ),
                'options' => $options,
            ];
        }

        return $tags_list;
    }
}

if ( !function_exists( 'get_all_post_types_with_posts' ) ) {

    /**
     * Retrieves an array of all registered post types with published posts.
     *
     * @return array[] An array of post types with their respective options.
     *                 Each post type is an associative array containing the post type name as 'key',
     *                 the label of the post type as 'label', and an array of options as 'options'.
     *                 Each option is an associative array containing the post ID as 'id',
     *                 the title of the post as 'title', and the permalink of the post as 'link'.
     */
    function get_all_post_types_with_posts() {
        $page_list = [];

        // Restrict to only 'post' and 'page'
        $post_types = [
            'post' => get_post_type_object( 'post' ),
            'page' => get_post_type_object( 'page' ),
        ];

        foreach ( $post_types as $post_type ) {
            // Get all posts of this post type
            $posts = get_posts( [
                'post_type'   => $post_type->name,
                'numberposts' => -1, // Retrieve all posts
            ] );

            $options = [];
            foreach ( $posts as $post ) {
                $options[] = [
                    'id'    => $post->ID,
                    'title' => $post->post_title,
                    'link'  => get_permalink( $post->ID ),
                ];
            }

            // Add to page_list if options are available
            if ( !empty( $options ) ) {
                $page_list[] = [
                    'key'     => $post_type->name,
                    'label'   => esc_html( $post_type->label, 'poptics' ),
                    'options' => $options,
                ];
            }
        }

        return $page_list;
    }

}

if ( !function_exists( 'allow_svg_upload' ) ) {
    /**
     * Allow SVG uploads in WordPress
     *
     * @param array $mimes Array of allowed MIME types
     *
     * @return array Array of allowed MIME types, with SVG added
     */
    function allow_svg_upload( $mimes ) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }
}

if ( !function_exists( 'is_current_time_in_schedule' ) ) {
    /**
     * Checks if the current time is within any of the duration ranges in a schedule.
     *
     * @param array $schedule The schedule to check, with keys 'fixed' and 'recurring'.
     *                         The 'fixed' key points to an array of arrays, each containing
     *                         the keys 'timezone', 'duration', and 'days'. The 'duration' key
     *                         points to an array of strings, each representing a start and end
     *                         time in the format 'YYYY-MM-DD HH:MM:SS'.
     *
     * @return bool True if the current time is within any of the duration ranges, false otherwise.
     */
    function is_current_time_in_schedule( $schedule ) {
        foreach ( $schedule['fixed'] as $schedule_entry ) {
            // Get the timezone of the schedule
            $timezone     = new DateTimeZone( $schedule_entry['timezone'] );
            $current_time = new DateTime( 'now', $timezone );

            // Loop through the duration ranges (they are pairs of start and end times)
            $durations = $schedule_entry['duration'];

            for ( $i = 0; $i < count( $durations ); $i += 2 ) {
                // Convert the start and end time of each duration to DateTime objects
                $start_time = new DateTime( $durations[$i], $timezone );
                $end_time   = new DateTime( $durations[$i + 1], $timezone );

                // Check if current time is within the duration
                if ( $current_time >= $start_time && $current_time <= $end_time ) {
                    return true; // Current time is within the duration
                }
            }
        }

        // If no duration includes the current time, return false
        return false;
    }
}

if ( !function_exists( 'get_domain' ) ) {
    /**
     * Get domain from url.
     *
     * @param   string  $url  URL from string.
     *
     * @return  string|bool Return domain otherwise false.
     */
    function get_domain( $url ) {
        $pieces = wp_parse_url( $url );
        $domain = isset( $pieces['host'] ) ? $pieces['host'] : $pieces['path'];

        if ( preg_match(
            '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,20})$/i',
            $domain, $regs
        ) ) {
            return strtolower( $regs['domain'] );
        }

        return false;
    }
}

if ( !function_exists( 'get_currency' ) ) {
    /**
     * Get woocommerce currency site
     *
     * @return string
     */
    function get_currency() {
        $currency = [];

        if ( class_exists( 'WooCommerce' ) ) {
            $currency          = get_woocommerce_currency();
            $currency_symbol   = html_entity_decode( get_woocommerce_currency_symbol( $currency ) );
            $currency_position = get_option( 'woocommerce_currency_pos' );

            $currency = [
                'symbol'   => $currency_symbol,
                'position' => $currency_position,
            ];
        }

        return $currency;
    }
}

if ( !function_exists( 'parse_block' ) ) {
    /**
     * Parse block content and apply shortcodes recursively.
     *
     * @param array $steps Steps of the campaign.
     *
     * @return array Parsed blocks content.
     */
    function parse_block( $steps ) {
        $parsed_blocks = [];
        foreach ( $steps as $item ) {
            $item['desktop']['content'] = apply_do_shortcode_recursive( $item['desktop']['content'] );
            $item['mobile']['content']  = apply_do_shortcode_recursive( $item['mobile']['content'] );

            $parsed_blocks[] = $item;
        }

        return $parsed_blocks;
    }
}

if ( !function_exists( 'apply_do_shortcode_recursive' ) ) {
    /**
     * Recursively traverse an array of block content and apply do_shortcode to
     * the 'text' attribute of any 'core/shortcode' blocks.
     *
     * @param array $block_content_array Array of block content. Can be an array of
     *                                   blocks, or an array of innerBlocks,
     *                                   or an array of attributes.
     *
     * @return array Processed block content array.
     */
    function apply_do_shortcode_recursive( $block_content_array ) {
        $result = [];

        foreach ( $block_content_array as $item ) {
            // Check if the current item is an array
            if ( is_array( $item ) ) {
                // If the block name is 'core/shortcode', process the shortcode
                if ( isset( $item['name'] ) && $item['name'] === 'core/shortcode' && isset( $item['attributes']['text'] ) ) {
                    // Apply do_shortcode to the 'text' attribute
                    $item['attributes']['text'] = do_shortcode( $item['attributes']['text'] );
                }

                // Recursively apply the function to innerBlocks or other nested arrays
                if ( isset( $item['innerBlocks'] ) && is_array( $item['innerBlocks'] ) ) {
                    $item['innerBlocks'] = apply_do_shortcode_recursive( $item['innerBlocks'] );
                }
            }

            // Append the processed item to the result array
            $result[] = $item;
        }

        return $result;
    }
}

if ( !function_exists( 'poptics_array_csv_column' ) ) {
    /**
     * Convert array to CSV column
     *
     * @param array $data
     *
     * @return string
     */
    function poptics_array_csv_column( $data = [] ) {
        $result_string = '';

        foreach ( $data as $data_key => $value ) {
            if ( !is_array( $value ) ) {
                return poptics_is_associative_array( $data ) ? poptics_get_all_active_campaigns( $data ) : implode( ',', $data );
            }

            if ( poptics_is_associative_array( $value ) ) {
                $valueString = poptics_get_all_active_campaigns( $value );
                $result_string .= rtrim( $valueString, ', ' ) . '|';
            } else {
                $result_string .= implode( ',', $value ) . '|';
            }
        }

        // Remove the trailing '|'
        $result_string = rtrim( $result_string, '|' );

        return $result_string;
    }
}

if ( !function_exists( 'poptics_is_associative_array' ) ) {
    /**
     * Check an associative array or not
     *
     * @param array $array
     *
     * @return bool
     */
    function poptics_is_associative_array( $array ) {
        return is_array( $array ) && count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
    }
}

if ( !function_exists( 'poptics_get_all_active_campaigns' ) ) {
    /**
     * Convert single array to csv column
     *
     * @param array $data
     *
     * @return string
     */
    function poptics_get_all_active_campaigns( $data ) {
        if ( !is_array( $data ) ) {
            return false;
        }

        $result_string = '';

        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $result_string .= implode( ',', $value );
            } else {
                $result_string .= "$key:$value,";
            }
        }

        return rtrim( $result_string, ',' );
    }
}

if ( !function_exists( 'poptics_has_active_campaigns' ) ) {
    /**
     * Get all active campaigns
     *
     * @return array
     */
    function poptics_has_active_campaigns() {

        $api_campaign     = new Poptics\Core\Campaign\Api_Campaign();
        $active_campaigns = Poptics\Core\Campaign\Campaign::all( [
            'posts_per_page' => -1,
            'paged'          => -1,
            'post_status'    => ['active', 'scheduled'],
        ] );

        return $active_campaigns;
    }
}