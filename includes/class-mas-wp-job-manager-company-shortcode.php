<?php
/**
 * MAS Companies For WP Job Manager Class
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'MAS_WPJMC_Shortcode' ) ) :

    class MAS_WPJMC_Shortcode {

        public $company_dashboard_message = '';

        public function __construct() {
            add_action( 'wp', array( $this, 'handle_redirects' ) );
            add_action( 'wp', array( $this, 'shortcode_action_handler' ) );
            add_shortcode( 'mas_submit_company_form', array( $this, 'mas_submit_company_form' ) );
            add_shortcode( 'mas_company_dashboard', array( $this, 'mas_company_dashboard' ) );
            add_shortcode( 'mas_companies', array( $this, 'output_companies' ) );
        }

        /**
         * Handle redirects
         */
        public function handle_redirects() {
            if ( ! get_current_user_id() || ! empty( $_REQUEST['company_id'] ) ) {
                return;
            }

            $submit_company_form_page_id    = get_option( 'job_manager_submit_company_form_page_id' );
            $company_dashboard_page_id      = get_option( 'job_manager_company_dashboard_page_id' );
            $submission_limit               = get_option( 'job_manager_company_submission_limit' );
            $company_count                  = mas_wpjmc_company_manager_count_user_companies();

            if ( $submit_company_form_page_id && $company_dashboard_page_id && $submission_limit && $company_count >= $submission_limit && is_page( $submit_company_form_page_id ) ) {
                wp_redirect( get_permalink( $company_dashboard_page_id ) );
                exit;
            }
        }

        /**
         * Handle actions which need to be run before the shortcode e.g. post actions
         */
        public function shortcode_action_handler() {
            global $post;

            if ( is_page() && strstr( $post->post_content, '[mas_company_dashboard' ) ) {
                $this->company_dashboard_handler();
            }
        }

        /**
         * Handles actions on company dashboard
         */
        public function company_dashboard_handler() {
            if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'mas_job_manager_company_my_company_actions' ) ) {

                $action    = sanitize_title( $_REQUEST['action'] );
                $company_id = absint( $_REQUEST['company_id'] );

                try {
                    // Get company
                    $company = get_post( $company_id );

                    // Check ownership
                    if ( ! $company || $company->post_author != get_current_user_id() )
                        throw new Exception( __( 'Invalid Company ID', 'mas-wp-job-manager-company' ) );

                    switch ( $action ) {
                        case 'delete' :
                            // Trash it
                            wp_trash_post( $company_id );

                            // Message
                            $this->company_dashboard_message = '<div class="job-manager-message">' . sprintf( __( '%s has been deleted', 'mas-wp-job-manager-company' ), $company->post_title ) . '</div>';

                        break;
                        case 'hide' :
                            if ( $company->post_status === 'publish' ) {
                                $update_company = array( 'ID' => $company_id, 'post_status' => 'private' );
                                wp_update_post( $update_company );
                                $this->company_dashboard_message = '<div class="job-manager-message">' . sprintf( __( '%s has been hidden', 'mas-wp-job-manager-company' ), $company->post_title ) . '</div>';
                            }
                        break;
                        case 'publish' :
                            if ( in_array( $company->post_status, array( 'private', 'hidden' ) ) ) {
                                $update_company = array( 'ID' => $company_id, 'post_status' => 'publish' );
                                wp_update_post( $update_company );
                                $this->company_dashboard_message = '<div class="job-manager-message">' . sprintf( __( '%s has been published', 'mas-wp-job-manager-company' ), $company->post_title ) . '</div>';
                            }
                        break;
                        case 'relist' :
                            // redirect to post page
                            wp_redirect( add_query_arg( array( 'company_id' => absint( $company_id ) ), get_permalink( get_option( 'job_manager_submit_company_form_page_id' ) ) ) );

                            break;
                    }

                    do_action( 'mas_job_manager_company_my_company_do_action', $action, $company_id );

                } catch ( Exception $e ) {
                    $this->company_dashboard_message = '<div class="job-manager-error">' . wp_kses_post( $e->getMessage() ) . '</div>';
                }
            }
        }

        /**
         * Shortcode which lists the logged in user's companies
         */
        public function mas_company_dashboard( $atts ) {
            if ( ! is_user_logged_in() ) {
                ob_start();
                get_job_manager_template( 'job-dashboard-login.php' );
                return ob_get_clean();
            }

            extract( shortcode_atts( array(
                'posts_per_page' => '25',
            ), $atts ) );

            wp_enqueue_script( 'mas-wp-job-manager-company-dashboard' );

            // If doing an action, show conditional content if needed....
            if ( ! empty( $_REQUEST['action'] ) ) {

                $action    = sanitize_title( $_REQUEST['action'] );

                switch ( $action ) {
                    case 'edit' :
                        return  mas_wpjmc()->forms->get_form( 'edit-company' );
                }
            }

            // ....If not show the company dashboard
            $args = apply_filters( 'mas_job_manager_company_get_dashboard_companies_args', array(
                'post_type'           => 'company',
                'post_status'         => array( 'publish', 'expired', 'pending', 'private' ),
                'ignore_sticky_posts' => 1,
                'posts_per_page'      => $posts_per_page,
                'offset'              => ( max( 1, get_query_var('paged') ) - 1 ) * $posts_per_page,
                'orderby'             => 'date',
                'order'               => 'desc',
                'author'              => get_current_user_id()
            ) );

            $companies = new WP_Query;

            ob_start();

            echo wp_kses_post( $this->company_dashboard_message );

            $company_dashboard_columns = apply_filters( 'mas_job_manager_company_dashboard_columns', array(
                'company-title'     => esc_html__( 'Name', 'mas-wp-job-manager-company' ),
                'status'            => esc_html__( 'Status', 'mas-wp-job-manager-company' ),
                'date'              => esc_html__( 'Date Posted', 'mas-wp-job-manager-company' )
            ) );

            get_job_manager_template( 'company-dashboard.php', array( 'companies' => $companies->query( $args ), 'max_num_pages' => $companies->max_num_pages, 'company_dashboard_columns' => $company_dashboard_columns ), 'mas-wp-job-manager-company', mas_wpjmc()->plugin_dir . 'templates/' );

            return ob_get_clean();
        }

        /**
         * Show the company submission form
         */
        public function mas_submit_company_form( $atts = array() ) {
            return mas_wpjmc()->forms->get_form( 'submit-company', $atts );
        }

        /**
         * output company function.
         *
         * @access public
         */
        public function output_companies( $atts ) {
            global $post;

            extract( shortcode_atts( array(
                'per_page'          => get_option( 'job_manager_companies_per_page', 10 ),
                'orderby'           => 'date',
                'order'             => 'DESC',
                'category'          => '',
                'average_salary'    => '',
                'post_status'       => '',
                'show_pagination'   => false,
            ), $atts ) );

            $category       = is_array( $category ) ? $category : array_filter( array_map( 'trim', explode( ',', $category ) ) );
            $average_salary = is_array( $average_salary ) ? $average_salary : array_filter( array_map( 'trim', explode( ',', $average_salary ) ) );
            $post_status    = is_array( $post_status ) ? $post_status : array_filter( array_map( 'trim', explode( ',', $post_status ) ) );

            $show_pagination = wp_validate_boolean( $show_pagination );

            $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

            $offset = intval( $per_page * ( $paged - 1 ) ) ;

            $companies = mas_wpjmc_get_companies( apply_filters( 'mas_job_manager_company_output_companies_args', array(
                'post_status'       => $post_status,
                'category'          => $category,
                'average_salary'    => $average_salary,
                'orderby'           => $orderby,
                'order'             => $order,
                'posts_per_page'    => $per_page,
                'page'              => $paged,
                'offset'            => $offset,
            ) ) );

            ob_start();

            if ( $companies->have_posts() ) : ?>

                <?php do_action( 'mas_wpjmc_before_shortcode_company_start', $companies, $atts ); ?>
                
                <ul class="wpjmc-companies">

                <?php while ( $companies->have_posts() ) : $companies->the_post(); ?>
                    <?php get_job_manager_template_part( 'content', 'company', 'mas-wp-job-manager-company', mas_wpjmc()->plugin_dir . 'templates/' ); ?>
                <?php endwhile; ?>

                </ul>

                <?php if ( $show_pagination ) : ?>
                    <?php get_job_manager_template( 'pagination.php', array( 'max_num_pages' => $companies->max_num_pages ) ); ?>
                <?php endif; ?>

            <?php else :
                do_action( 'job_manager_output_jobs_no_results' );
            endif;

            wp_reset_postdata();

            $output = apply_filters( 'mas_job_manager_companies_output', ob_get_clean() );

            return $output;
        }
    }

endif;