<?php
/**
 * MAS Companies For WP Job Manager Class
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'MAS_WPJMC' ) ) :

    class MAS_WPJMC {

        public function __construct() {
            add_filter( 'body_class', array( $this, 'company_body_classes' ) );
            add_action( 'widgets_init', array( $this, 'widgets_register' ) );
        }

        public function company_body_classes( $classes ) {
            $classes[] = 'mas-wpjmc-activated';

            if( is_post_type_archive( 'company' ) || is_page( mas_wpjmc_get_page_id( 'companies' ) ) || is_page( mas_wpjmc_get_page_id( 'company_dashboard' ) ) || is_page( mas_wpjmc_get_page_id( 'submit_company_form' ) ) || mas_wpjmc_is_company_taxonomy() || is_singular( 'company' ) ) {
                $classes[] = 'mas-wpjmc-pages';
            }

            if( is_post_type_archive( 'company' ) || is_page( mas_wpjmc_get_page_id( 'companies' ) ) || mas_wpjmc_is_company_taxonomy() ) {
                $classes[] = 'post-type-archive-company';
            }

            return $classes;
        }

        public function widgets_register() {
            // Search Widget
            require_once( mas_wpjmc()->plugin_dir . 'includes/widgets/class-mas-wpjmc-widget-company-search.php' );

            // Filter Widget
            require_once( mas_wpjmc()->plugin_dir . 'includes/widgets/class-mas-wpjmc-widget-layered-nav.php' );

            if ( current_theme_supports( 'mas-wp-job-manager-company-archive' ) ) {
                // Search Widget
                register_widget( 'MAS_WPJMC_Widget_Company_Search' );

                // Filter Widget
                register_widget( 'MAS_WPJMC_Widget_Layered_Nav' );
            }
        }

        public static function get_current_page_url() {
            if ( ! ( is_post_type_archive( 'company' ) || is_page( mas_wpjmc_get_page_id( 'companies' ) ) ) && ! mas_wpjmc_is_company_taxonomy() ) {
                return;
            }

            if ( defined( 'MAS_WPJMC_COMPANIES_IS_ON_FRONT' ) ) {
                $link = home_url( '/' );
            } elseif ( is_post_type_archive( 'company' ) || is_page( mas_wpjmc_get_page_id( 'companies' ) ) ) {
                $link = get_permalink( mas_wpjmc_get_page_id( 'companies' ) );
            } else {
                $queried_object = get_queried_object();
                $link = get_term_link( $queried_object->slug, $queried_object->taxonomy );
            }

            // Order by.
            if ( isset( $_GET['orderby'] ) ) {
                $link = add_query_arg( 'orderby', mas_wpjmc_clean( wp_unslash( $_GET['orderby'] ) ), $link );
            }

            /**
             * Search Arg.
             * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
             */
            if ( get_search_query() ) {
                $link = add_query_arg( 's', rawurlencode( wp_specialchars_decode( get_search_query() ) ), $link );
            }

            // Post Type Arg.
            if ( isset( $_GET['post_type'] ) ) {
                $link = add_query_arg( 'post_type', mas_wpjmc_clean( wp_unslash( $_GET['post_type'] ) ), $link );
            }

            // Location Arg.
            if ( isset( $_GET['search_location'] ) ) {
                $link = add_query_arg( 'search_location', mas_wpjmc_clean( wp_unslash( $_GET['search_location'] ) ), $link );
            }

            // Date Filter Arg.
            if ( isset( $_GET['posted_before'] ) ) {
                $link = add_query_arg( 'posted_before', mas_wpjmc_clean( wp_unslash( $_GET['posted_before'] ) ), $link );
            }

            // All current filters.
            if ( $_chosen_taxonomies = MAS_WPJMC_Query::get_layered_nav_chosen_taxonomies() ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found, WordPress.CodeAnalysis.AssignmentInCondition.Found
                foreach ( $_chosen_taxonomies as $name => $data ) {
                    $filter_name = sanitize_title( $name );
                    if ( ! empty( $data['terms'] ) ) {
                        $link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
                    }
                    if ( 'or' === $data['query_type'] ) {
                        $link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
                    }
                }
            }

            return $link;
        }

        public static function get_current_page_query_args() {
            $args = array();

            // Order by.
            if ( isset( $_GET['orderby'] ) ) {
                $args['orderby'] = mas_wpjmc_clean( wp_unslash( $_GET['orderby'] ) );
            }

            /**
             * Search Arg.
             * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
             */
            if ( get_search_query() ) {
                $args['s'] = rawurlencode( wp_specialchars_decode( get_search_query() ) );
            }

            // Post Type Arg.
            if ( isset( $_GET['post_type'] ) ) {
                $args['post_type'] = mas_wpjmc_clean( wp_unslash( $_GET['post_type'] ) );
            }

            // Location Arg.
            if ( isset( $_GET['search_location'] ) ) {
                $args['search_location'] = mas_wpjmc_clean( wp_unslash( $_GET['search_location'] ) );
            }

            // Date Filter Arg.
            if ( isset( $_GET['posted_before'] ) ) {
                $args['posted_before'] = mas_wpjmc_clean( wp_unslash( $_GET['posted_before'] ) );
            }

            // All current filters.
            if ( $_chosen_taxonomies = MAS_WPJMC_Query::get_layered_nav_chosen_taxonomies() ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found, WordPress.CodeAnalysis.AssignmentInCondition.Found
                foreach ( $_chosen_taxonomies as $name => $data ) {
                    $filter_name = sanitize_title( $name );
                    if ( ! empty( $data['terms'] ) ) {
                        $args['filter_' . $filter_name] = implode( ',', $data['terms'] );
                    }
                    if ( 'or' === $data['query_type'] ) {
                        $args['query_type_' . $filter_name] = 'or';
                    }
                }
            }

            return $args;
        }

        public function job_manager_get_current_user_companies_select_options() {
            global $current_user;
            $options = array(
                ''  => esc_html__( 'Select Company', 'mas-wp-job-manager-company' ),
            );

            if( is_user_logged_in() && ! empty( $current_user ) ) {
                $args = array(
                    'post_type'     => 'company',
                    'orderby'       => 'title',
                    'order'         => 'ASC',
                    'numberposts'   => -1,
                );
                
                $has_capability = $current_user->has_cap( 'edit_posts' );
                if( ! $has_capability ){
                    $args['author'] = $current_user->ID;
                }
                $companies = get_posts( apply_filters( 'masjm_get_current_user_companies_args', $args ) );

                if( ! empty( $companies ) ) {
                    foreach( $companies as $company ) {
                        $options[$company->ID] = get_the_title( $company );
                    }

                } else {
                    $options = array(
                        ''  => esc_html__( 'No Company Found', 'mas-wp-job-manager-company' ),
                    );
                }
            } else {
                $options = array(
                    ''  => esc_html__( 'User Not Logged In', 'mas-wp-job-manager-company' ),
                );
            }

            return $options;
        }
    }

endif;