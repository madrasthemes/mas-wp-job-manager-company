<?php
/**
 * Mas WP Job Manager Company Class
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Mas_WPJMC' ) ) :

    class Mas_WPJMC {

        public function __construct() {
            add_filter( 'body_class', array( $this, 'company_body_classes' ) );
            // add_action( 'widgets_init', array( $this, 'widgets_register' ) );
        }

        public function company_body_classes( $classes ) {
            if( is_post_type_archive( 'company' ) || is_page( mas_wpjmc_get_companies_page_id() ) || is_company_taxonomy() ) {
                $classes[] = 'post-type-archive-company';
            }

            return $classes;
        }

        public static function get_current_page_url() {
            if ( ! ( is_post_type_archive( 'company' ) || is_page( mas_wpjmc_get_companies_page_id() ) ) && ! is_company_taxonomy() ) {
                return;
            }

            if ( defined( 'COMPANIES_IS_ON_FRONT' ) ) {
                $link = home_url( '/' );
            } elseif ( is_post_type_archive( 'company' ) || is_page( mas_wpjmc_get_companies_page_id() ) ) {
                $link = get_permalink( mas_wpjmc_get_companies_page_id() );
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
            if ( $_chosen_taxonomies = Mas_WPJMC_Query::get_layered_nav_chosen_taxonomies() ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found, WordPress.CodeAnalysis.AssignmentInCondition.Found
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
            if ( $_chosen_taxonomies = Mas_WPJMC_Query::get_layered_nav_chosen_taxonomies() ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found, WordPress.CodeAnalysis.AssignmentInCondition.Found
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
    }

endif;