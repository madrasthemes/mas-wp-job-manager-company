<?php
/**
 * Mas WP Job Manager Company CPT Class
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mas_WPJMC_CPT {

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_types' ), 0 );
        add_filter( 'job_manager_settings', array( $this, 'job_manager_company_settings' ) );
        add_filter( 'manage_company_posts_columns', array( $this, 'custom_company_columns' ) );
        add_action( 'manage_company_posts_custom_column' , array( $this, 'custom_company_column' ), 10, 2 );
    }

    public function custom_company_columns($columns) {
        $columns_new['cb'] = $columns['cb'];
        $columns_old = $columns;
        unset( $columns );
        $columns_new['company_image'] = '';
        $columns_new['title'] = esc_html__( 'Company Name', 'mas-wp-job-manager-company' );

        $columns = array_merge( $columns_new, $columns_old );

        echo '<style type="text/css">';
        echo '.column-company_image { width:60px; box-sizing:border-box } .column-company_image img { max-width:100%; } @media (max-width: 768px) { .column-title,.column-company_image { display: table-cell !important; } .wp-list-table .is-expanded,.wp-list-table .column-primary .toggle-row { display:none !important } .wp-list-table td.column-primary { padding-right: 10px; } }';
        echo '</style>';

        return $columns;
    }

    // Add the data to the custom columns for the company post type:
    public function custom_company_column( $column, $post_id ) {
        switch ( $column ) {
            case 'company_image' :
                echo the_company_logo();
            break;
        }
    }

    public function register_post_types() {
        if ( post_type_exists( "company" ) ) {
            return;
        }

        $admin_capability = 'manage_job_listings';

        /**
         * Taxonomies
         */
        $taxonomies_args = apply_filters( 'mas_company_taxonomies_list', array(
            'company_industry'  => array(
                'singular'                  => esc_html__( 'Industry', 'mas-wp-job-manager-company' ),
                'plural'                    => esc_html__( 'Industries', 'mas-wp-job-manager-company' ),
                'slug'                      => esc_html_x( 'company-industry', 'Company industry permalink - resave permalinks after changing this', 'mas-wp-job-manager-company' ),
                'enable'                    => get_option('job_manager_company_enable_salary', true)
            ),
            'company_employees_strength' => array(
                'singular'                  => esc_html__( 'Employee Strength', 'mas-wp-job-manager-company' ),
                'plural'                    => esc_html__( 'Employees Strength', 'mas-wp-job-manager-company' ),
                'slug'                      => esc_html_x( 'company-employees-strength', 'Company employees strength permalink - resave permalinks after changing this', 'mas-wp-job-manager-company' ),
                'enable'                    => get_option('job_manager_company_enable_company_employees_strength', true)
            ),
            'company_average_salary'    => array(
                'singular'                  => esc_html__( 'Avg. Salary', 'mas-wp-job-manager-company' ),
                'plural'                    => esc_html__( 'Avg. Salary', 'mas-wp-job-manager-company' ),
                'slug'                      => esc_html_x( 'company-avearge-salary', 'Company avearge salary permalink - resave permalinks after changing this', 'mas-wp-job-manager-company' ),
                'enable'                    => get_option('job_manager_company_enable_average_salary', true)
            ),
            'company_revenue'    => array(
                'singular'                  => esc_html__( 'Revenue', 'mas-wp-job-manager-company' ),
                'plural'                    => esc_html__( 'Revenue', 'mas-wp-job-manager-company' ),
                'slug'                      => esc_html_x( 'company-revenue', 'Company revenue permalink - resave permalinks after changing this', 'mas-wp-job-manager-company' ),
                'enable'                    => get_option('job_manager_company_enable_company_revenue', true)
            ),
        ) );

        foreach ( $taxonomies_args as $taxonomy_name => $taxonomy_args ) {
            if( $taxonomy_args['enable'] ) {
                $singular  = $taxonomy_args['singular'];
                $plural    = $taxonomy_args['plural'];
                $slug      = $taxonomy_args['slug'];

                $args = apply_filters( 'register_taxonomy_{$taxonomy_name}_args',
                    array(
                        'hierarchical'      => true,
                        'update_count_callback' => '_update_post_term_count',
                        'label'             => $plural,
                        'labels'            => array(
                            'name'              => $plural,
                            'singular_name'     => $singular,
                            'menu_name'         => ucwords( $plural ),
                            'search_items'      => sprintf( esc_html__( 'Search %s', 'mas-wp-job-manager-company' ), $plural ),
                            'all_items'         => sprintf( esc_html__( 'All %s', 'mas-wp-job-manager-company' ), $plural ),
                            'parent_item'       => sprintf( esc_html__( 'Parent %s', 'mas-wp-job-manager-company' ), $singular ),
                            'parent_item_colon' => sprintf( esc_html__( 'Parent %s:', 'mas-wp-job-manager-company' ), $singular ),
                            'edit_item'         => sprintf( esc_html__( 'Edit %s', 'mas-wp-job-manager-company' ), $singular ),
                            'update_item'       => sprintf( esc_html__( 'Update %s', 'mas-wp-job-manager-company' ), $singular ),
                            'add_new_item'      => sprintf( esc_html__( 'Add New %s', 'mas-wp-job-manager-company' ), $singular ),
                            'new_item_name'     => sprintf( esc_html__( 'New %s Name', 'mas-wp-job-manager-company' ),  $singular )
                        ),
                        'show_ui'               => true,
                        'show_in_rest'          => true,
                        'show_tagcloud'         => false,
                        'public'                => true,
                        'capabilities'          => array(
                            'manage_terms'      => $admin_capability,
                            'edit_terms'        => $admin_capability,
                            'delete_terms'      => $admin_capability,
                            'assign_terms'      => $admin_capability,
                        ),
                        'rewrite'           => array(
                            'slug'          => $slug,
                            'with_front'    => false,
                            'hierarchical'  => true
                        )
                    )
                );

                register_taxonomy( $taxonomy_name, 'company', $args );
            }
        }

        /**
         * Post Type
         */
        $singular  = esc_html__( 'Company', 'mas-wp-job-manager-company' );
        $plural    = esc_html__( 'Companies', 'mas-wp-job-manager-company' );
        $supports   = array( 'title', 'editor', 'publicize', 'thumbnail', 'excerpt', 'author', 'custom-fields' );
        $companies_page_id = mas_wpjmc_get_companies_page_id();

        if ( current_theme_supports( 'mas-wp-job-manager-company-archive' ) ) {
            $has_archive = $companies_page_id && get_post( $companies_page_id ) ? urldecode( get_page_uri( $companies_page_id ) ) : 'companies';
        } else {
            $has_archive = false;
        }

        $rewrite     = array(
            'slug'       => esc_html_x( 'company', 'Company permalink - resave permalinks after changing this', 'mas-wp-job-manager-company' ),
            'with_front' => false,
            'feeds'      => true
        );

        register_post_type( "company",
            apply_filters( "register_post_type_company", array(
                'labels'                => array(
                    'name'                  => $plural,
                    'singular_name'         => $singular,
                    'menu_name'             => $plural,
                    'all_items'             => sprintf( esc_html__( 'All %s', 'mas-wp-job-manager-company' ), $plural ),
                    'add_new'               => esc_html__( 'Add New', 'mas-wp-job-manager-company' ),
                    'add_new_item'          => sprintf( esc_html__( 'Add %s', 'mas-wp-job-manager-company' ), $singular ),
                    'edit'                  => esc_html__( 'Edit', 'mas-wp-job-manager-company' ),
                    'edit_item'             => sprintf( esc_html__( 'Edit %s', 'mas-wp-job-manager-company' ), $singular ),
                    'new_item'              => sprintf( esc_html__( 'New %s', 'mas-wp-job-manager-company' ), $singular ),
                    'view'                  => sprintf( esc_html__( 'View %s', 'mas-wp-job-manager-company' ), $singular ),
                    'view_item'             => sprintf( esc_html__( 'View %s', 'mas-wp-job-manager-company' ), $singular ),
                    'search_items'          => sprintf( esc_html__( 'Search %s', 'mas-wp-job-manager-company' ), $plural ),
                    'not_found'             => sprintf( esc_html__( 'No %s found', 'mas-wp-job-manager-company' ), $plural ),
                    'not_found_in_trash'    => sprintf( esc_html__( 'No %s found in trash', 'mas-wp-job-manager-company' ), $plural ),
                    'parent'                => sprintf( esc_html__( 'Parent %s', 'mas-wp-job-manager-company' ), $singular )
                ),
                'description'           => sprintf( esc_html__( 'This is where you can create and manage %s.', 'mas-wp-job-manager-company' ), $plural ),
                'public'                => true,
                'show_ui'               => class_exists( 'WP_Job_Manager' ),
                'show_in_rest'          => true,
                'menu_icon'             => 'dashicons-building',
                'capability_type'       => 'post',
                'capabilities' => array(
                    'publish_posts'         => $admin_capability,
                    'edit_posts'            => $admin_capability,
                    'edit_others_posts'     => $admin_capability,
                    'delete_posts'          => $admin_capability,
                    'delete_others_posts'   => $admin_capability,
                    'read_private_posts'    => $admin_capability,
                    'edit_post'             => $admin_capability,
                    'delete_post'           => $admin_capability,
                    'read_post'             => $admin_capability
                ),
                'publicly_queryable'    => true,
                'exclude_from_search'   => false,
                'hierarchical'          => false,
                'rewrite'               => $rewrite,
                'query_var'             => true,
                'supports'              => $supports,
                'has_archive'           => $has_archive,
                'template'              => array( array( 'core/freeform' ) ),
                'template_lock'         => 'all',
            ) )
        );
    }

    public function job_manager_company_settings( $settings ) {
        $settings['mas_wpjmc_settings'] = array(
            esc_html__( 'Company', 'mas-wp-job-manager-company' ),
            array(
                'job_manager_companies_page_id' => array(
                    'name'      => 'job_manager_companies_page_id',
                    'std'       => '',
                    'label'     => esc_html__( 'Company Listings Page', 'mas-wp-job-manager-company' ),
                    'desc'      => esc_html__( 'Select the page for company listing. This lets the plugin know the location of the company listings page.', 'mas-wp-job-manager-company' ),
                    'type'      => 'page',
                ),
                'job_manager_companies_per_page' => array(
                    'name'        => 'job_manager_companies_per_page',
                    'std'         => '10',
                    'placeholder' => '',
                    'label'       => esc_html__( 'Listings Per Page', 'mas-wp-job-manager-company' ),
                    'desc'        => esc_html__( 'Number of job listings to display per page.', 'mas-wp-job-manager-company' ),
                    'attributes'  => array(),
                ),
            ),
        );
        if ( ! current_theme_supports( 'mas-wp-job-manager-company-archive' ) ) {
            unset( $settings['mas_wpjmc_settings'][1]['job_manager_companies_page_id'] );
            delete_option( 'job_manager_companies_page_id' );
            unset( $settings['mas_wpjmc_settings'][1]['job_manager_companies_per_page'] );
            delete_option( 'job_manager_companies_per_page' );
            if( empty( $settings['mas_wpjmc_settings'][1] ) ) {
                unset( $settings['mas_wpjmc_settings'] );
            }
        }
        return $settings;
    }
}