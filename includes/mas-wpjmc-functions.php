<?php

function mas_wpjmc_get_companies_page_id() {
    $page_id = 0;
    $page_id = get_option( 'job_manager_companies_page_id' );
    $page_id = apply_filters( 'mas_wpjmc_get_companies_page_id', $page_id );

    return $page_id ? absint( $page_id ) : -1;
}

if ( ! function_exists( 'is_company_taxonomy' ) ) {

    /**
     * Is_company_taxonomy - Returns true when viewing a company taxonomy archive.
     *
     * @return bool
     */
    function is_company_taxonomy() {
        return is_tax( get_object_taxonomies( 'company' ) );
    }
}

function mas_add_showing_to_company_listings_result( $results, $companies ) {

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

/**
 * Sets up the mas_wpjmc_loop global from the passed args or from the main query.
 *
 * @param array $args Args to pass into the global.
 */
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

/**
 * Resets the mas_wpjmc_loop global.
 *
 */
function mas_wpjmc_reset_loop() {
    unset( $GLOBALS['mas_wpjmc_loop'] );
}

/**
 * Gets a property from the mas_wpjmc_loop global.
 *
 * @param string $prop Prop to get.
 * @param string $default Default if the prop does not exist.
 * @return mixed
 */
function mas_wpjmc_get_loop_prop( $prop, $default = '' ) {
    mas_wpjmc_setup_loop(); // Ensure shop loop is setup.

    return isset( $GLOBALS['mas_wpjmc_loop'], $GLOBALS['mas_wpjmc_loop'][ $prop ] ) ? $GLOBALS['mas_wpjmc_loop'][ $prop ] : $default;
}

/**
 * Sets a property in the mas_wpjmc_loop global.
 *
 * @param string $prop Prop to set.
 * @param string $value Value to set.
 */
function mas_wpjmc_set_loop_prop( $prop, $value = '' ) {
    if ( ! isset( $GLOBALS['mas_wpjmc_loop'] ) ) {
        mas_wpjmc_setup_loop();
    }
    $GLOBALS['mas_wpjmc_loop'][ $prop ] = $value;
}

function mas_wpjmc_clean( $var ) {
    if ( is_array( $var ) ) {
        return array_map( 'mas_wpjmc_clean', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }
}

function mas_wpjmc_strlen( $var ) {
    if ( is_array( $var ) ) {
        return array_map( 'mas_wpjmc_strlen', $var );
    } else {
        return strlen( $var );
    }
}

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

if ( ! function_exists( 'mas_get_company_keyword_search' ) ) {
    /**
     * Adds join and where query for keywords.
     *
     * @since 1.0.0
     * @param string $search
     * @return string
     */
    function mas_get_company_keyword_search( $search ) {
        global $wpdb, $mas_wpjmc_search_keyword;

        // Searchable Meta Keys: set to empty to search all meta keys
        $searchable_meta_keys = array(
            '_company_tagline',
            '_company_headquarters',
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
         * @since 1.26.0
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

/**
 * Add company fields in post job form
 */
if ( ! function_exists( 'mas_wpjmc_submit_job_form_fields' ) ) {
    function mas_wpjmc_submit_job_form_fields() {
        $fields = array(
            'company_email' => array(
                'label'       => esc_html__( 'Email', 'mas-wp-job-manager-company' ),
                'type'        => 'text',
                'required'    => false,
                'placeholder' => esc_html__( 'you@yourdomain.com', 'mas-wp-job-manager-company' ),
                'priority'    => 2,
            ),
            'company_phone' => array(
                'label'       => esc_html__( 'Phone', 'mas-wp-job-manager-company' ),
                'type'        => 'text',
                'required'    => false,
                'placeholder' => esc_html__( 'Phone Number', 'mas-wp-job-manager-company' ),
                'priority'    => 2,
            ),
            'company_headquarters' => array(
                'label'       => esc_html__( 'Headquarters', 'mas-wp-job-manager-company' ),
                'description' => esc_html__( 'Leave this blank if the headquarters location is not important', 'mas-wp-job-manager-company' ),
                'type'        => 'text',
                'required'    => false,
                'placeholder' => esc_html__( 'e.g. "London"', 'mas-wp-job-manager-company' ),
                'priority'    => 2,
            ),
            'company_since' => array(
                'label'       => esc_html__( 'Since', 'mas-wp-job-manager-company' ),
                'type'        => 'date',
                'required'    => false,
                'placeholder' => esc_html__( 'Established date/year', 'mas-wp-job-manager-company' ),
                'priority'    => 2,
            ),
            'company_facebook' => array(
                'label'       => esc_html__( 'Facebook', 'mas-wp-job-manager-company' ),
                'type'        => 'text',
                'required'    => false,
                'placeholder' => esc_html__( 'Facebook page url', 'mas-wp-job-manager-company' ),
                'priority'    => 5,
            ),
            'company_industry' => array(
                'label'       => esc_html__( 'Industry', 'mas-wp-job-manager-company' ),
                'type'        => 'term-select',
                'required'    => false,
                'placeholder' => esc_html__( 'Choose Industry&hellip;', 'mas-wp-job-manager-company' ),
                'priority'    => 10,
                'default'     => '',
                'taxonomy'    => 'company_industry',
            ),
            'company_employees_strength' => array(
                'label'       => esc_html__( 'Employer Strength', 'mas-wp-job-manager-company' ),
                'type'        => 'term-select',
                'required'    => false,
                'placeholder' => '',
                'priority'    => 10,
                'default'     => '',
                'taxonomy'    => 'company_employees_strength',
            ),
            'company_average_salary' => array(
                'label'       => esc_html__( 'Average Salary', 'mas-wp-job-manager-company' ),
                'type'        => 'term-select',
                'required'    => false,
                'placeholder' => '',
                'priority'    => 10,
                'default'     => '',
                'taxonomy'    => 'company_average_salary',
            ),
            'company_revenue' => array(
                'label'       => esc_html__( 'Company Revenue', 'mas-wp-job-manager-company' ),
                'type'        => 'term-select',
                'required'    => false,
                'placeholder' => '',
                'priority'    => 10,
                'default'     => '',
                'taxonomy'    => 'company_revenue',
            ),
            'company_description' => array(
                'label'       => esc_html__( 'Description', 'mas-wp-job-manager-company' ),
                'type'        => 'wp-editor',
                'required'    => false,
                'priority'    => 10,
            ),
        );

        return apply_filters( 'mas_wpjmc_submit_job_form_company_fields' , $fields );
    }
}

if ( ! function_exists( 'mas_wpjmc_submit_company_form_required_fields' ) ) {
    function mas_wpjmc_submit_company_form_required_fields() {
        $required_fields = array(
            'post_fields'  => array( 'company_name', 'company_logo', 'company_description' ),
            'tax_fields'   => array( 'company_industry', 'company_employees_strength', 'company_average_salary', 'company_revenue' ),
            'meta_fields'  => array( 'company_website', 'company_tagline', 'company_video', 'company_twitter', 'company_headquarters', 'company_email', 'company_phone', 'company_facebook', 'company_since' )
        );

        return apply_filters( 'mas_wpjmc_submit_company_form_required_fields' , $required_fields );
    }
}

if ( ! function_exists( 'mas_wpjmc_add_custom_job_company_fields' ) ) {
    function mas_wpjmc_add_custom_job_company_fields() {
        $company_fields = mas_wpjmc_submit_job_form_fields();
        $required_fields = mas_wpjmc_submit_company_form_required_fields();

        $job_id = ! empty( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : 0;
        $company_id = 0;

        if ( ! job_manager_user_can_edit_job( $job_id ) ) {
            $job_id = 0;
        }

        if( $job_id ) {
            $post_title = get_post_meta( $job_id, '_company_name', true );
            if( ! empty( $post_title ) ) {
                $company = get_page_by_title( $post_title, OBJECT, 'company' );
                $company_id = isset( $company->ID ) ? $company->ID : 0;
            }
        }

        foreach ( $company_fields as $key => $field ) : ?>
            <?php if( $company_id ) {
                if ( ! isset( $field['value'] ) ) {
                    if ( 'company_description' === $key ) {
                        $field['value'] = $company->post_content;

                    } elseif ( ! empty( $field['taxonomy'] ) ) {
                        $field['value'] = wp_get_object_terms( $company->ID, $field['taxonomy'], array( 'fields' => 'ids' ) );

                    } else {
                        $field['value'] = get_post_meta( $company->ID, '_' . $key, true );
                    }
                }
            } ?>
            <?php  ?>
            <fieldset class="fieldset-<?php echo esc_attr( $key ); ?>">
                <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) . wp_kses_post( apply_filters( 'submit_job_form_required_label', $field['required'] ? '' : ' <small>' . esc_html__( '(optional)', 'mas-wp-job-manager-company' ) . '</small>', $field ) ); ?></label>
                <div class="field <?php echo esc_attr( $field['required'] ? 'required-field' : '' ); ?>">
                    <?php get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
                </div>
            </fieldset>
        <?php endforeach;
    }
}

add_action( 'submit_job_form_company_fields_end', 'mas_wpjmc_add_custom_job_company_fields' );

if ( ! function_exists( 'mas_wpjmc_update_job_form_fields' ) ) {
    function mas_wpjmc_update_job_form_fields( $job_id, $values ) {
        $required_fields = mas_wpjmc_submit_company_form_required_fields();

        $post_fields = array();
        $tax_fields = array();
        $meta_fields = array();

        foreach ( $required_fields['post_fields'] as $field_name ) {
            $post_fields[ $field_name ] = isset( $_POST[ $field_name ] ) ? mas_wpjmc_clean( $_POST[ $field_name ] ) : '';
        }

        foreach ( $required_fields['tax_fields'] as $field_name ) {
            $tax_fields[ $field_name ] = isset( $_POST[ $field_name ] ) ? mas_wpjmc_clean( $_POST[ $field_name ] ) : '';
        }

        foreach ( $required_fields['meta_fields'] as $field_name ) {
            $meta_fields[ $field_name ] = isset( $_POST[ $field_name ] ) ? mas_wpjmc_clean( $_POST[ $field_name ] ) : '';
        }

        if( empty( $post_fields['company_logo'] ) && ! empty( $values['company']['company_logo'] ) ) {
            $post_fields['company_logo'] = $values['company']['company_logo'];
        }

        if( isset( $_POST['job_manager_form'] ) && $_POST['job_manager_form'] == 'submit-job' ) {
            $post_fields    = array_filter( $post_fields, 'mas_wpjmc_strlen' );
            $tax_fields     = array_filter( $tax_fields, 'mas_wpjmc_strlen' );
            $meta_fields    = array_filter( $meta_fields, 'mas_wpjmc_strlen' );
        }

        if( ! empty( $post_fields ) ) {

            $post = get_page_by_title( $post_fields['company_name'], OBJECT, 'company' );
            $company_id = isset( $post->ID ) ? $post->ID : 0;

            $post_data = array(
                'post_title'     => $post_fields['company_name'],
                'post_content'   => isset( $post_fields['company_description'] ) ? $post_fields['company_description'] : '',
                'post_type'      => 'company',
                'comment_status' => 'closed',
                'post_status'    => 'pending'
            );

            if ( $company_id ) {
                $post_data['ID'] = $company_id;
                if( isset( $_POST['job_manager_form'] ) && $_POST['job_manager_form'] == 'submit-job' ) {
                    $post_data['post_content'] = isset( $post->post_content ) ? $post->post_content : '';
                    $post_data['post_status'] = isset( $post->post_status ) ? $post->post_status : 'pending';
                }
                wp_update_post( $post_data );
            } else {
                $company_id = wp_insert_post( $post_data );
            }

            if( ! empty( $post_fields['company_logo'] ) ) {
                $attachment_id = is_numeric( $post_fields['company_logo'] ) ? absint( $post_fields['company_logo'] ) : '';
                if ( empty( $attachment_id ) ) {
                    delete_post_thumbnail( $company_id );
                } else {
                    set_post_thumbnail( $company_id, $attachment_id );
                }
            }

            if( ! empty( $tax_fields ) ) {
                foreach ( $tax_fields as $key => $value ) {
                    $terms = array();
                    if ( is_array( $value ) ) {
                        $terms = array_map( 'absint', $value );
                    } elseif( $value > 0 ) {
                        $terms = array( absint( $value ) );
                    }
                    wp_set_object_terms( $company_id, $terms, $key, false );
                }
            }

            if( ! empty( $meta_fields ) ) {
                foreach ( $meta_fields as $key => $value ) {
                    update_post_meta( $company_id, '_' . $key, $value );
                }
            }
        }
    }
}

add_action( 'job_manager_update_job_data', 'mas_wpjmc_update_job_form_fields', 10, 2 );

/**
 * Output the class
 *
 * @param string $class (default: '')
 * @param mixed $post_id (default: null)
 * @return void
 */
if ( ! function_exists( 'company_class' ) ) {
    function company_class( $class = '', $post_id = null ) {
        echo 'class="' . join( ' ', get_company_class( $class, $post_id ) ) . '"';
    }
}

/**
 * Get the class
 *
 * @access public
 * @return array
 */
if ( ! function_exists( 'get_company_class' ) ) {
    function get_company_class( $class = '', $post_id = null ) {
        $post = get_post( $post_id );
        if ( $post->post_type !== 'company' )
            return array();

        $classes = array();

        if ( empty( $post ) ) {
            return $classes;
        }

        $classes[] = 'company';

        if ( is_company_featured( $post ) ) {
            $classes[] = 'company_featured';
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
if ( ! function_exists( 'is_company_featured' ) ) {
    function is_company_featured( $post = null ) {
        $post = get_post( $post );

        return $post->_featured ? true : false;
    }
}

/**
 * Get the company openings jobs
 */
if ( ! function_exists( 'mas_wpjmc_get_the_company_job_listing' ) ) {
    function mas_wpjmc_get_the_company_job_listing( $post = null ) {
       $post = get_post( $post );

       return get_posts( array( 'post_type' => 'job_listing', 'meta_key' => '_company_name', 'meta_value' => $post->post_title, 'nopaging' => true ) );
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
    function mas_wpjmc_get_taxomony_data( $taxonomy = "company_industry", $post = null, $linkable = false, $linkable_class = '', $separator = ", " ) {

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
