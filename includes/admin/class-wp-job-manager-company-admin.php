<?php
/**
 * File containing the class WP_Job_Manager_Company_Admin.
 *
 * @package wp-job-manager-company
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles front admin page for WP Job Manager.
 *
 * @since 1.0.0
 */
class WP_Job_Manager_Company_Admin {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.26.0
	 */
	private static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.26.0
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wp_version;

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueues CSS and JS assets.
	 */
	public function admin_enqueue_scripts() {
		WP_Job_Manager::register_select2_assets();

		$screen = get_current_screen();
		if ( in_array( $screen->id, apply_filters( 'job_manager_admin_screen_ids', array( 'edit-company', 'plugins', 'company' ) ), true ) ) {
			wp_enqueue_style( 'jquery-ui' );
			wp_enqueue_style( 'select2' );

			WP_Job_Manager::register_style( 'job_manager_admin_css', 'css/admin.css', array() );
			wp_enqueue_style( 'job_manager_admin_css' );

			wp_enqueue_script( 'wp-job-manager-datepicker' );

			WP_Job_Manager::register_script( 'job_manager_company_admin_js', 'js/admin.js', array( 'jquery', 'select2' ), true );
			wp_enqueue_script( 'job_manager_company_admin_js' );

			wp_localize_script(
				'job_manager_company_admin_js',
				'job_manager_admin_params',
				array(
					'user_selection_strings' => array(
						'no_matches'        => _x( 'No matches found', 'user selection', 'mas-wp-job-manager-company' ),
						'ajax_error'        => _x( 'Loading failed', 'user selection', 'mas-wp-job-manager-company' ),
						'input_too_short_1' => _x( 'Please enter 1 or more characters', 'user selection', 'mas-wp-job-manager-company' ),
						'input_too_short_n' => _x( 'Please enter %qty% or more characters', 'user selection', 'mas-wp-job-manager-company' ),
						'load_more'         => _x( 'Loading more results&hellip;', 'user selection', 'mas-wp-job-manager-company' ),
						'searching'         => _x( 'Searching&hellip;', 'user selection', 'mas-wp-job-manager-company' ),
					),
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'search_users_nonce'     => wp_create_nonce( 'search-users' ),
				)
			);
		}
	}
}

WP_Job_Manager_Company_Admin::instance();
