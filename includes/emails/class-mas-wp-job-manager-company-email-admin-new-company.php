<?php
/**
 * File containing the class MAS_WPJMC_Email_Admin_New_Company.
 *
 * @package mas-wp-job-manager-company
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email notification to administrator when a new company is submitted.
 *
 * @since 1.31.0
 * @extends WP_Job_Manager_Email
 */
class MAS_WPJMC_Email_Admin_New_Company extends MAS_WPJMC_Email_Template {
	/**
	 * Get the unique email notification key.
	 *
	 * @return string
	 */
	public static function get_key() {
		return 'admin_new_company';
	}

	/**
	 * Get the friendly name for this email notification.
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Admin Notice of New Company', 'mas-wp-job-manager-company' );
	}

	/**
	 * Get the description for this email notification.
	 *
	 * @type abstract
	 * @return string
	 */
	public static function get_description() {
		return __( 'Send a notice to the site administrator when a new comapny is submitted on the frontend.', 'mas-wp-job-manager-company' );
	}

	/**
	 * Get the email subject.
	 *
	 * @return string
	 */
	public function get_subject() {
		$args = $this->get_args();

		/**
		 * Company post object.
		 *
		 * @var WP_Post $company
		 */
		$company = $args['company'];

		// translators: Placeholder %s is the company post title.
		return sprintf( __( 'New Company Submitted: %s', 'mas-wp-job-manager-company' ), $company->post_title );
	}

	/**
	 * Get `From:` address header value. Can be simple email or formatted `Firstname Lastname <email@example.com>`.
	 *
	 * @return string|bool Email from value or false to use WordPress' default.
	 */
	public function get_from() {
		return false;
	}

	/**
	 * Get array or comma-separated list of email addresses to send message.
	 *
	 * @return string|array
	 */
	public function get_to() {
		return get_option( 'admin_email', false );
	}

	/**
	 * Checks the arguments and returns whether the email notification is properly set up.
	 *
	 * @return bool
	 */
	public function is_valid() {
		$args = $this->get_args();
		return isset( $args['company'] )
				&& $args['company'] instanceof WP_Post
				&& $this->get_to();
	}
}
