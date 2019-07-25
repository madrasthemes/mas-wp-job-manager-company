<?php
/**
 * Plugin Name: Mas Company for WP Job Manager
 * Description: This plugin helps to create a custom post type company for WP Job Manager
 * Version: 1.0.0
 * Author: MadrasThemes
 * Author URI: https://madrasthemes.com/
 *
 * Text Domain: mas-wp-job-manager-company
 * Domain Path: /languages/
 *
 * @package Company
 * @category Core
 * @author Madras Themes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Mas_WP_Job_Manager_Company.
 *
 * Main Mas_WPJMC class initializes the plugin.
 *
 * @class     Mas_WP_Job_Manager_Company
 * @version   1.0.0
 * @author    Madras Themes
 */
class Mas_WP_Job_Manager_Company {

    /**
     * Instace of Mas_WP_Job_Manager_Company.
     *
     * @since 1.0.0
     * @access private
     * @var object $instance The instance of Mas_WPJMC.
     */
    private static $instance;

    public $version = '1.0.0';

    /**
     * Query instance.
     *
     * @var Mas_WPJMC_Query
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

        /* === CLASSES === */

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company.php' );
        $this->company = new Mas_WPJMC();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-cpt.php' );
        $this->cpt = new Mas_WPJMC_CPT();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-shortcode.php' );
        $this->shortcode = new Mas_WPJMC_Shortcode();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-forms.php' );
        $this->forms = new Mas_WPJMC_Forms();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-query.php' );
        $this->query = new Mas_WPJMC_Query();

        require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-template-loader.php' );

        if( is_admin() ) {
            require( $this->plugin_dir . 'includes/class-mas-wp-job-manager-company-writepanels.php' );
            $this->writepanels = new Mas_WPJMC_Writepanels();
        }

        //
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
                'i18n_navigate'       => __( 'If you wish to edit the posted details use the "edit resume" button instead, otherwise changes may be lost.', 'mas-wp-job-manager-company' ),
                'i18n_confirm_remove' => __( 'Are you sure you want to remove this item?', 'mas-wp-job-manager-company' ),
                'i18n_remove'         => __( 'remove', 'mas-wp-job-manager-company' ),
            )
        );

        wp_localize_script(
            'mas-wp-job-manager-company-dashboard', 'mas_wp_job_manager_company_dashboard', array(
                'i18n_confirm_delete' => __( 'Are you sure you want to delete this resume?', 'mas-wp-job-manager-company' ),
            )
        );

        // General stylesheet.
        if( apply_filters( 'mas_wpjmc_enqueue_scripts_enable_frontend_css', true ) ) {
            wp_enqueue_style( 'mas-wp-job-manager-company-frontend', plugins_url( 'assets/css/frontend.css', __FILE__ ), array( 'dashicons' ) );
            wp_style_add_data( 'mas-wp-job-manager-company-frontend', 'rtl', 'replace' );
        }
    }

    public function mas_wpjmc_admin_enqueue_scripts() {
        wp_enqueue_style( 'job_manager_admin_css', JOB_MANAGER_PLUGIN_URL . '/assets/css/admin.css', array(), JOB_MANAGER_VERSION );
        wp_enqueue_script( 'job_manager_admin_js', JOB_MANAGER_PLUGIN_URL . '/assets/js/admin.min.js', array( 'jquery', 'jquery-tiptip' ), JOB_MANAGER_VERSION, true );
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
 * The main function responsible for returning the Mas_WP_Job_Manager_Company object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * @since 1.0.0
 *
 * @return object Mas_WP_Job_Manager_Company class object.
 */
function mas_wpjmc() {
    if ( ! class_exists( 'WP_Job_Manager' ) )
        return;

    return Mas_WP_Job_Manager_Company::instance();
}

// Load plugin instance on plugins loaded.
add_action( 'plugins_loaded', 'mas_wpjmc' );