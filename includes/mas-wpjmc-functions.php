<?php

if ( ! function_exists( 'mas_wpjmc_get_page_id' ) ) {
    function mas_wpjmc_get_page_id( $page ) {

        $option_name = '';
        switch( $page ) {
            case 'companies':
                $option_name = 'job_manager_companies_page_id';
            break;
            case 'company_dashboard':
                $option_name = 'job_manager_company_dashboard_page_id';
            break;
            case 'submit_company_form':
                $option_name = 'job_manager_submit_company_form_page_id';
            break;
        }

        $page_id = 0;

        if ( ! empty( $option_name ) ) {
            $page_id = get_option( $option_name );
        }

        $page_id = apply_filters( 'mas_wpjmc_get_' . $page . '_page_id', $page_id );
        return $page_id ? absint( $page_id ) : -1;
    }
}

/**
 * Is_company_taxonomy - Returns true when viewing a company taxonomy archive.
 *
 * @return bool
 */
if ( ! function_exists( 'mas_wpjmc_is_company_taxonomy' ) ) {
    function mas_wpjmc_is_company_taxonomy() {
        return is_tax( get_object_taxonomies( 'company' ) );
    }
}


if ( ! function_exists( 'mas_wpjmc_add_showing_to_company_listings_result' ) ) {
    function mas_wpjmc_add_showing_to_company_listings_result( $results, $companies ) {

        $search_location    = isset( $_REQUEST['search_location'] ) ? sanitize_text_field( stripslashes( $_REQUEST['search_location'] ) ) : '';
        $search_keywords    = isset( $_REQUEST['search_keywords'] ) ? sanitize_text_field( stripslashes( $_REQUEST['search_keywords'] ) ) : '';

        $showing     = '';
        $showing_all = false;

        if ( $companies->post_count ) {

            $showing_all = true;

            $start = (int) $companies->get( 'offset' ) + 1;
            $end   = $start + (int)$companies->post_count - 1;

            if ( $companies->max_num_pages > 1 ) {
                $showing = sprintf( esc_html__( 'Showing %s - %s of %s companies', 'mas-wp-job-manager-company'), $start, $end, $companies->found_posts );
            } else {
                $showing =  sprintf( _n( 'Showing one job', 'Showing all %s companies', $companies->found_posts, 'mas-wp-job-manager-company' ), $companies->found_posts );
            }


            if ( ! empty( $search_keywords ) ) {
                $showing = sprintf( wp_kses_post( '%s matching <span class="highlight">%s</span>', 'mas-wp-job-manager-company' ), $showing, $search_keywords );
            }

            if ( ! empty( $search_location ) ) {
                $showing = sprintf( wp_kses_post( '%s in <span class="highlight">%s</span>', 'mas-wp-job-manager-company' ), $showing, $search_location );
            }
        }
        $results['showing']     = $showing;
        $results['showing_all'] = $showing_all;
        return $results;
    }
}

/**
 * Sets up the mas_wpjmc_loop global from the passed args or from the main query.
 *
 * @param array $args Args to pass into the global.
 */
if ( ! function_exists( 'mas_wpjmc_setup_loop' ) ) {
    function mas_wpjmc_setup_loop( $args = array() ) {
        $default_args = array(
            'loop'         => 0,
            'columns'      => 1,
            'name'         => '',
            'is_shortcode' => false,
            'is_paginated' => true,
            'is_search'    => false,
            'is_filtered'  => false,
            'total'        => 0,
            'total_pages'  => 0,
            'per_page'     => 0,
            'current_page' => 1,
        );

        // If this is a main WC query, use global args as defaults.
        if ( $GLOBALS['wp_query']->get( 'mas_wpjmc_query' ) ) {
            $default_args = array_merge( $default_args, array(
                'is_search'    => $GLOBALS['wp_query']->is_search(),
                // 'is_filtered'  => is_filtered(),
                'total'        => $GLOBALS['wp_query']->found_posts,
                'total_pages'  => $GLOBALS['wp_query']->max_num_pages,
                'per_page'     => $GLOBALS['wp_query']->get( 'posts_per_page' ),
                'current_page' => max( 1, $GLOBALS['wp_query']->get( 'paged', 1 ) ),
            ) );
        }

        // Merge any existing values.
        if ( isset( $GLOBALS['mas_wpjmc_loop'] ) ) {
            $default_args = array_merge( $default_args, $GLOBALS['mas_wpjmc_loop'] );
        }

        $GLOBALS['mas_wpjmc_loop'] = wp_parse_args( $args, $default_args );
    }
}

/**
 * Resets the mas_wpjmc_loop global.
 *
 */
if ( ! function_exists( 'mas_wpjmc_reset_loop' ) ) {
    function mas_wpjmc_reset_loop() {
        unset( $GLOBALS['mas_wpjmc_loop'] );
    }
}

/**
 * Gets a property from the mas_wpjmc_loop global.
 *
 * @param string $prop Prop to get.
 * @param string $default Default if the prop does not exist.
 * @return mixed
 */
if ( ! function_exists( 'mas_wpjmc_get_loop_prop' ) ) {
    function mas_wpjmc_get_loop_prop( $prop, $default = '' ) {
        mas_wpjmc_setup_loop(); // Ensure shop loop is setup.

        return isset( $GLOBALS['mas_wpjmc_loop'], $GLOBALS['mas_wpjmc_loop'][ $prop ] ) ? $GLOBALS['mas_wpjmc_loop'][ $prop ] : $default;
    }
}

/**
 * Sets a property in the mas_wpjmc_loop global.
 *
 * @param string $prop Prop to set.
 * @param string $value Value to set.
 */
if ( ! function_exists( 'mas_wpjmc_set_loop_prop' ) ) {
    function mas_wpjmc_set_loop_prop( $prop, $value = '' ) {
        if ( ! isset( $GLOBALS['mas_wpjmc_loop'] ) ) {
            mas_wpjmc_setup_loop();
        }
        $GLOBALS['mas_wpjmc_loop'][ $prop ] = $value;
    }
}

if ( ! function_exists( 'mas_wpjmc_clean' ) ) {
    function mas_wpjmc_clean( $var ) {
        if ( is_array( $var ) ) {
            return array_map( 'mas_wpjmc_clean', $var );
        } else {
            return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
        }
    }
}

if ( ! function_exists( 'mas_wpjmc_strlen' ) ) {
    function mas_wpjmc_strlen( $var ) {
        if ( is_array( $var ) ) {
            return array_map( 'mas_wpjmc_strlen', $var );
        } else {
            return strlen( $var );
        }
    }
}

if ( ! function_exists( 'mas_wpjmc_number_format_i18n' ) ) {
    function mas_wpjmc_number_format_i18n( $n ) {
        // first strip any formatting;
        $n = ( 0 + str_replace( ",", "", $n ) );

        // is this a number?
        if( ! is_numeric( $n ) ) {
            return $n;
        }

        // now filter it;
        if( $n >= 1000000000000 ) {
            return round( ( $n/1000000000000 ), 1 ) . 'T';
        } elseif( $n >= 1000000000 ) {
            return round( ( $n/1000000000 ), 1 ) . 'B';
        } elseif( $n >= 1000000 ) {
            return round( ( $n/1000000 ), 1 ) . 'M';
        } elseif( $n >= 10000 ) {
            return round( ( $n/10000 ), 10 ) . 'K';
        }

        return number_format_i18n( $n );
    }
}

/**
 * Adds join and where query for keywords.
 *
 * @since 1.0.0
 * @param string $search
 * @return string
 */
if ( ! function_exists( 'mas_wpjmc_get_company_keyword_search' ) ) {
    function mas_wpjmc_get_company_keyword_search( $search ) {
        global $wpdb, $mas_wpjmc_search_keyword;

        // Searchable Meta Keys: set to empty to search all meta keys
        $searchable_meta_keys = array(
            '_company_tagline',
            '_company_location',
            '_company_website',
            '_company_email',
            '_company_phone',
            '_company_twitter',
            '_company_facebook',
        );

        $searchable_meta_keys = apply_filters( 'mas_wpjmc_searchable_meta_keys', $searchable_meta_keys );

        // Set Search DB Conditions
        $conditions   = array();

        // Search Post Meta
        if( apply_filters( 'mas_wpjmc_search_post_meta', true ) ) {

            // Only selected meta keys
            if( $searchable_meta_keys ) {
                $conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ( '" . implode( "','", array_map( 'esc_sql', $searchable_meta_keys ) ) . "' ) AND meta_value LIKE '%" . esc_sql( $mas_wpjmc_search_keyword ) . "%' )";
            } else {
                // No meta keys defined, search all post meta value
                $conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%" . esc_sql( $mas_wpjmc_search_keyword ) . "%' )";
            }
        }

        // Search taxonomy
        $conditions[] = "{$wpdb->posts}.ID IN ( SELECT object_id FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id WHERE t.name LIKE '%" . esc_sql( $mas_wpjmc_search_keyword ) . "%' )";

        /**
         * Filters the conditions to use when querying job listings. Resulting array is joined with OR statements.
         *
         * @since 1.0.0
         *
         * @param array  $conditions          Conditions to join by OR when querying job listings.
         * @param string $mas_wpjmc_search_keyword Search query.
         */
        $conditions = apply_filters( 'mas_wpjmc_search_conditions', $conditions, $mas_wpjmc_search_keyword );
        if ( empty( $conditions ) ) {
            return $search;
        }

        $conditions_str = implode( ' OR ', $conditions );

        if ( ! empty( $search ) ) {
            $search = preg_replace( '/^ AND /', '', $search );
            $search = " AND ( {$search} OR ( {$conditions_str} ) )";
        } else {
            $search = " AND ( {$conditions_str} )";
        }

        return $search;
    }
}

if ( ! function_exists( 'mas_wpjmc_get_all_taxonomies' ) ) {
    function mas_wpjmc_get_all_taxonomies() {
        $taxonomies = array();

        $taxonomy_objects = get_object_taxonomies( 'company', 'objects' );
        foreach ( $taxonomy_objects as $taxonomy_object ) {
            $taxonomies[] = array(
                'taxonomy'  => $taxonomy_object->name,
                'name'      => $taxonomy_object->label,
            );
        }

        return $taxonomies;
    }
}

/**
 * Queries company listings with certain criteria and returns them.
 *
 * @since 1.0.0
 * @param string|array|object $args Arguments used to retrieve company listings.
 * @return WP_Query
 */
if ( ! function_exists( 'mas_wpjmc_get_companies' ) ) {
    function mas_wpjmc_get_companies( $args = array() ) {
        $args = wp_parse_args(
            $args,
            array(
                'post_status'       => array(),
                'posts_per_page'    => 10,
                'orderby'           => 'date',
                'order'             => 'DESC',
                'featured'          => null,
                'fields'            => 'all',
                'offset'            => 0,
                'category'          => array(),
                'average_salary'    => array(),
                'author'            => array(),
                'paged'             => 1,
            )
        );

        /**
         * Perform actions that need to be done prior to the start of the company listings query.
         *
         * @since 1.0.0
         *
         * @param array $args Arguments used to retrieve company listings.
         */
        do_action( 'mas_wpjmc_get_companies_init', $args );

        if ( ! empty( $args['post_status'] ) ) {
            $post_status = $args['post_status'];
        } else {
            $post_status = 'publish';
        }

        $query_args = array(
            'post_type'              => 'company',
            'post_status'            => $post_status,
            'ignore_sticky_posts'    => 1,
            'offset'                 => absint( $args['offset'] ),
            'posts_per_page'         => intval( $args['posts_per_page'] ),
            'orderby'                => $args['orderby'],
            'order'                  => $args['order'],
            'tax_query'              => array(),
            'meta_query'             => array(),
            'author'                 => array(),
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'cache_results'          => false,
            'fields'                 => $args['fields'],
            'paged'                  => $args['paged'],
        );

        if ( $args['posts_per_page'] < 0 ) {
            $query_args['no_found_rows'] = true;
        }

        if ( ! is_null( $args['featured'] ) ) {
            $query_args['meta_query'][] = array(
                'key'     => '_featured',
                'value'   => '1',
                'compare' => $args['featured'] ? '=' : '!=',
            );
        }

        if ( ! empty( $args['category'] ) ) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'company_category',
                'field'    => 'slug',
                'terms'    => $args['category'],
                'operator' => 'IN',
            );
        }

        if ( ! empty( $args['average_salary'] ) ) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'company_average_salary',
                'field'    => 'slug',
                'terms'    => $args['average_salary'],
                'operator' => 'IN',
            );
        }

        if ( 'featured' === $args['orderby'] ) {
            $query_args['orderby'] = array(
                'menu_order' => 'ASC',
                'date'       => 'DESC',
                'ID'         => 'DESC',
            );
        }

        if ( 'rand_featured' === $args['orderby'] ) {
            $query_args['orderby'] = array(
                'menu_order' => 'ASC',
                'rand'       => 'ASC',
            );
        }

        $query_args = apply_filters( 'mas_job_manager_company_get_listings', $query_args, $args );

        if ( empty( $query_args['meta_query'] ) ) {
            unset( $query_args['meta_query'] );
        }

        if ( empty( $query_args['tax_query'] ) ) {
            unset( $query_args['tax_query'] );
        }

        /** This filter is documented in wp-job-manager.php */
        $query_args['lang'] = apply_filters( 'mas_wpjmc_lang', null );

        // Filter args.
        $query_args = apply_filters( 'mas_wpjmc_get_companies_query_args', $query_args, $args );

        do_action( 'before_mas_wpjmc_get_companies', $query_args, $args );

        $result = new WP_Query( $query_args );

        do_action( 'after_mas_wpjmc_get_companies', $query_args, $args );

        return $result;
    }
}

/**
 * Output the class
 *
 * @param string $class (default: '')
 * @param mixed $post_id (default: null)
 * @return void
 */
if ( ! function_exists( 'mas_wpjmc_company_class' ) ) {
    function mas_wpjmc_company_class( $class = '', $post_id = null ) {
        echo 'class="' . join( ' ', mas_wpjmc_get_company_class( $class, $post_id ) ) . '"';
    }
}

/**
 * Get the class
 *
 * @access public
 * @return array
 */
if ( ! function_exists( 'mas_wpjmc_get_company_class' ) ) {
    function mas_wpjmc_get_company_class( $class = '', $post_id = null ) {
        $post = get_post( $post_id );
        if ( $post->post_type !== 'company' )
            return array();

        $classes = array();

        if ( empty( $post ) ) {
            return $classes;
        }

        $classes[] = 'company';

        if ( mas_wpjmc_is_company_featured( $post ) ) {
            $classes[] = 'company_featured';
        }

        if ( ! empty( $class ) ) {
            $classes[] = $class;
        }

        return get_post_class( $classes, $post->ID );
    }
}

/**
 * Return whether or not the company has been featured
 *
 * @param  object $post
 * @return boolean
 */
if ( ! function_exists( 'mas_wpjmc_is_company_featured' ) ) {
    function mas_wpjmc_is_company_featured( $post = null ) {
        $post = get_post( $post );

        return $post->_featured ? true : false;
    }
}

/**
 * Outputs the company status
 *
 * @param WP_Post|int $post (default: null)
 */
if ( ! function_exists( 'mas_wpjmc_the_company_status' ) ) {
    function mas_wpjmc_the_company_status( $post = null ) {
        echo mas_wpjmc_get_the_company_status( $post );
    }
}

/**
 * Gets the company status
 * @param WP_Post|int $post (default: null)
 * @return string
 */
if ( ! function_exists( 'mas_wpjmc_get_the_company_status' ) ) {
    function mas_wpjmc_get_the_company_status( $post = null ) {
        $post = get_post( $post );

        $status = $post->post_status;

        if ( $status == 'publish' )
            $status = esc_html__( 'Published', 'mas-wp-job-manager-company' );
        elseif ( $status == 'expired' )
            $status = esc_html__( 'Expired', 'mas-wp-job-manager-company' );
        elseif ( $status == 'pending' )
            $status = esc_html__( 'Pending Review', 'mas-wp-job-manager-company' );
        elseif ( $status == 'hidden' )
            $status = esc_html__( 'Hidden', 'mas-wp-job-manager-company' );
        elseif ( $status == 'private' )
            $status = esc_html__( 'Private', 'mas-wp-job-manager-company' );
        else
            $status = esc_html__( 'Inactive', 'mas-wp-job-manager-company' );

        return apply_filters( 'mas_wpjmc_the_company_status', $status, $post );
    }
}

/**
 * True if an the user can post a company. By default, you must be logged in.
 *
 * @return bool
 */
if ( ! function_exists( 'mas_wpjmc_company_manager_user_can_post_company' ) ) {
    function mas_wpjmc_company_manager_user_can_post_company() {
        $can_post = true;

        if ( ! is_user_logged_in() ) {
            if ( job_manager_user_requires_account() && ! job_manager_enable_registration() ) {
                $can_post = false;
            }
        }

        return apply_filters( 'mas_wpjmc_company_manager_user_can_post_company', $can_post );
    }
}

/**
 * True if an the user can edit a company.
 *
 * @param $company_id
 *
 * @return bool
 */
if ( ! function_exists( 'mas_wpjmc_company_manager_user_can_edit_company' ) ) {
    function mas_wpjmc_company_manager_user_can_edit_company( $company_id ) {
        $can_edit = true;

        if ( ! $company_id || ! is_user_logged_in() ) {
            $can_edit = false;
            if ( $company_id
                 && ! job_manager_user_requires_account()
                 && isset( $_COOKIE[ 'mas-wp-job-manager-company-submitting-company-key-' . $company_id ] )
                 && $_COOKIE[ 'mas-wp-job-manager-company-submitting-company-key-' . $company_id ] === get_post_meta( $company_id, '_submitting_key', true )
            ) {
                $can_edit = true;
            }
        } else {

            $company = get_post( $company_id );

            if ( ! $company || ( absint( $company->post_author ) !== get_current_user_id() && ! current_user_can( 'edit_post', $company_id ) ) ) {
                $can_edit = false;
            }
        }

        return apply_filters( 'mas_wpjmc_company_manager_user_can_edit_company', $can_edit, $company_id );
    }
}

/**
 * Whether to create attachments for files that are uploaded with a Company.
 *
 * @since 1.0.0
 *
 * @return bool
 */
if ( ! function_exists( 'mas_wpjmc_company_manager_attach_uploaded_files' ) ) {
    function mas_wpjmc_company_manager_attach_uploaded_files() {
        return apply_filters( 'mas_wpjmc_company_manager_attach_uploaded_files', false );
    }
}

/**
 * Count user companies
 * @param  integer $user_id
 * @return int
 */
if ( ! function_exists( 'mas_wpjmc_company_manager_count_user_companies' ) ) {
    function mas_wpjmc_company_manager_count_user_companies( $user_id = 0 ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_author = %d AND post_type = 'company' AND post_status IN ( 'publish', 'pending', 'expired', 'hidden' );", $user_id ) );
    }
}

/**
 * Get the company openings jobs
 */
if ( ! function_exists( 'mas_wpjmc_get_the_company_job_listing' ) ) {
    function mas_wpjmc_get_the_company_job_listing( $post = null ) {
        if( ! is_object( $post ) ) {
            $post = get_post( $post );
        }

        return get_posts( array( 'post_type' => 'job_listing', 'meta_key' => '_company_id', 'meta_value' => $post->ID, 'nopaging' => true ) );
    }
}

/**
 * Get the company openings count
 */
if ( ! function_exists( 'mas_wpjmc_get_the_company_job_listing_count' ) ) {
    function mas_wpjmc_get_the_company_job_listing_count( $post = null ) {
        $posts = mas_wpjmc_get_the_company_job_listing( $post );
        return count( $posts );
    }
}

if ( ! function_exists( 'mas_wpjmc_get_the_meta_data' ) ) {
    function mas_wpjmc_get_the_meta_data( $meta_key, $post = null, $trimed_link = false ) {
        if( ! post_type_exists( 'company' ) ) 
            return;

        if( ! is_object( $post ) ) {
            $post = get_post( $post );
        }

        $meta_data = get_post_meta( $post->ID, $meta_key, true );

        if( $trimed_link ) {
            if( substr( $meta_data, 0, 7 ) === "http://" ) {
                $meta_data = str_replace( 'http://', '', $meta_data);
            } elseif( substr( $meta_data, 0, 8 ) === "https://" ) {
                $meta_data = str_replace( 'https://', '', $meta_data);
            } else {
                $meta_data = $meta_data;
            }
        }

        return apply_filters( 'mas_wpjmc_get_the_meta_data', $meta_data );
    }
}

if( ! function_exists( 'mas_wpjmc_get_taxomony_data' ) ) {
    function mas_wpjmc_get_taxomony_data( $taxonomy = "company_category", $post = null, $linkable = false, $linkable_class = '', $separator = ", " ) {

        if( ! is_object( $post ) ) {
            $post = get_post( $post );
        }

        if ( ! taxonomy_exists( $taxonomy ) ) {
            return;
        }

        $terms = get_the_terms( $post->ID, $taxonomy );
        if ( $terms ) {
            if( $linkable ) {
                $term_links = array();
                foreach ( $terms as $term ){
                    $term_links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '"' . ( !empty( $linkable_class ) ? ' class="' . esc_attr( $linkable_class ) . '"' : "" ) . '>' . esc_html( $term->name ) . '</a>';
                }
                $output = implode( $separator, $term_links );
            } else {
                $term_names = wp_list_pluck( $terms, 'name' );
                $output = implode( $separator, $term_names );
            }

            return apply_filters( 'mas_wpjmc_the_taxomony_data', $output );
        }
    }
}
