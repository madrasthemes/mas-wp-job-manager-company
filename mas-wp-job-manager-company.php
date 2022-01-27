<?php
/**
 * Plugin Name: MAS Companies For WP Job Manager
 * Description: This plugin helps to create a custom post type company for WP Job Manager
 * Version: 1.0.5
 * Author: MadrasThemes
 * Author URI: https://madrasthemes.com/
 *
 * Text Domain: mas-wp-job-manager-company
 * Domain Path: /languages/
 *
 * @package MAS Companies For WP Job Manager
 * @category Core
 * @author Madras Themes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class MAS_WP_Job_Manager_Company.
 *
 * Main MAS_WPJMC class initializes the plugin.
 *
 * @class     MAS_WP_Job_Manager_Company
 * @version   1.0.0
 * @author    Madras Themes
 */
class MAS_WP_Job_Manager_Company {

    /**
     * Instace of MAS_WP_Job_Manager_Company.
     *
     * @since 1.0.0
     * @access private
     * @var object $instance The instance of MAS_WPJMC.
     */
    private static $instance;

    public $version = '1.0.5';

    /**
     * Query instance.
     *
     * @var MAS_WPJMC_Query
     */
    public $query = null;

    /**
     * Plugin file.
     *
     * @since 1.0.0
     * @var string $file Plugin file path.
     */
    public $file = __FILE__;

    /**
     * Construct.
     *
     * Initialize the class and plugin.
     *
     * @since 1.0.0
     */
    public function __construct() {
        define( 'MAS_JOB_MANAGER_COMPANY_VERSION', $this->version );

        $this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
        $this->plugin_dir = plugin_dir_path( __FILE__ );
        $this->init();
    }

    /**
     * Instace.
     *
     * An global instance of the class. Used to retrieve the instance
     * to use on other files/plugins/themes.
     *
     * @since 1.0.0
     * @return object Instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Initialize plugin.
     * Load all file and classes.
     *
     * @since 1.0.0
     */
    public function init() {

        // Load Plugin Translation.
        load_plugin_textdomain( dirname( plugin_basename( __FILE__ ) ), false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        // Functions.
        require( $this->plugin_dir . 'includes/mas-wpjmc-functions.php' );
        require( $this->plugin_dir . 'includes/mas-wpjmc-template-functions.php' );

        // Template Hooks.
        require( $this->plugin_dir . 'includes/mas-wpjmc-template-hooks.php' );

        // Abstracts Class
        include_once( $this->plugin_dir . 'includes/abstracts/abstract-mas-wp-job-manager-company-email-template.php' );

         //Admin Class
        include_once( $this->plugin_dir . 'includes/admin/class-wp-job-manager-company-admin.php' );

        /* === CLASSES === */

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company.php' );
        $this->company = new MAS_WPJMC();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-cpt.php' );
        $this->cpt = new MAS_WPJMC_CPT();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-shortcode.php' );
        $this->shortcode = new MAS_WPJMC_Shortcode();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-forms.php' );
        $this->forms = new MAS_WPJMC_Forms();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-query.php' );
        $this->query = new MAS_WPJMC_Query();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-template-loader.php' );

        if( is_admin() ) {
            require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-writepanels.php' );
            $this->writepanels = new MAS_WPJMC_Writepanels();
        }

        // Trigger Styles & Srcipts
        add_action( 'wp_enqueue_scripts', array( $this, 'mas_wpjmc_enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'mas_wpjmc_admin_enqueue_scripts' ), 20 );
    }

    /**
     * Enqueue scripts.
     *
     * Enqueue all style en javascripts.
     *
     * @since 1.0.0
     */
    public function mas_wpjmc_enqueue_scripts() {
        wp_register_script( 'mas-wp-job-manager-company-dashboard', plugins_url( 'assets/js/company-dashboard.min.js', __FILE__ ), array( 'jquery' ), MAS_JOB_MANAGER_COMPANY_VERSION, true );
        wp_register_script( 'mas-wp-job-manager-company-submission', plugins_url( 'assets/js/company-submission.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ), MAS_JOB_MANAGER_COMPANY_VERSION, true );

        wp_localize_script(
            'mas-wp-job-manager-company-submission', 'mas_wp_job_manager_company_submission', array(
                'i18n_navigate'       => esc_html__( 'If you wish to edit the posted details use the "edit resume" button instead, otherwise changes may be lost.', 'mas-wp-job-manager-company' ),
                'i18n_confirm_remove' => esc_html__( 'Are you sure you want to remove this item?', 'mas-wp-job-manager-company' ),
                'i18n_remove'         => esc_html__( 'remove', 'mas-wp-job-manager-company' ),
            )
        );

        wp_localize_script(
            'mas-wp-job-manager-company-dashboard', 'mas_wp_job_manager_company_dashboard', array(
                'i18n_confirm_delete' => esc_html__( 'Are you sure you want to delete this resume?', 'mas-wp-job-manager-company' ),
            )
        );

        // General stylesheet.
        if( apply_filters( 'mas_wpjmc_enqueue_scripts_enable_frontend_css', true ) ) {
            wp_enqueue_style( 'mas-wp-job-manager-company-frontend', plugins_url( 'assets/css/frontend.css', __FILE__ ), array( 'dashicons' ) );
            wp_style_add_data( 'mas-wp-job-manager-company-frontend', 'rtl', 'replace' );
        }
    }

    public function mas_wpjmc_admin_enqueue_scripts() {
        $screen = get_current_screen();
        if ( in_array( $screen->id, apply_filters( 'mas_wpjmc_admin_screen_ids', [ 'edit-company', 'plugins', 'company' ] ), true ) ) {
            $script_deps = array( 'jquery', 'jquery-tiptip' );

            if ( wp_script_is( 'select2', 'registered' ) ) {
                $script_deps[] = 'select2';
                wp_enqueue_style( 'select2' );
            } elseif ( wp_script_is( 'chosen', 'registered' ) && apply_filters( 'job_manager_chosen_enabled', true ) ) {
                // Support for themes and plugins not using WP Job Manager 1.32.0's select2 support.
                $script_deps[] = 'chosen';
            }

            wp_enqueue_style( 'job_manager_admin_css', JOB_MANAGER_PLUGIN_URL . '/assets/css/admin.css', array(), JOB_MANAGER_VERSION );
            wp_enqueue_script( 'job_manager_admin_js', JOB_MANAGER_PLUGIN_URL . '/assets/js/admin.min.js', $script_deps, JOB_MANAGER_VERSION, true );

            wp_localize_script(
                'job_manager_admin_js',
                'job_manager_admin_params',
                [
                    'user_selection_strings' => [
                        'no_matches'        => _x( 'No matches found', 'user selection', 'mas-wp-job-manager-company' ),
                        'ajax_error'        => _x( 'Loading failed', 'user selection', 'mas-wp-job-manager-company' ),
                        'input_too_short_1' => _x( 'Please enter 1 or more characters', 'user selection', 'mas-wp-job-manager-company' ),
                        'input_too_short_n' => _x( 'Please enter %qty% or more characters', 'user selection', 'mas-wp-job-manager-company' ),
                        'load_more'         => _x( 'Loading more results&hellip;', 'user selection', 'mas-wp-job-manager-company' ),
                        'searching'         => _x( 'Searching&hellip;', 'user selection', 'mas-wp-job-manager-company' ),
                    ],
                    'ajax_url'               => admin_url( 'admin-ajax.php' ),
                    'search_users_nonce'     => wp_create_nonce( 'search-users' ),
                ]
            );
        }
    }

    /**
     * Gets string as a bool.
     *
     * @param  string $value
     * @return bool
     */
    public function string_to_bool( $value ) {
        return ( is_bool( $value ) && $value ) || in_array( $value, array( '1', 'true', 'yes' ) ) ? true : false;
    }
}

/**
 * The main function responsible for returning the MAS_WP_Job_Manager_Company object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * @since 1.0.0
 *
 * @return object MAS_WP_Job_Manager_Company class object.
 */
function mas_wpjmc() {
    if ( ! class_exists( 'WP_Job_Manager' ) )
        return;

    return MAS_WP_Job_Manager_Company::instance();
}

// Load plugin instance on plugins loaded.
add_action( 'plugins_loaded', 'mas_wpjmc' );