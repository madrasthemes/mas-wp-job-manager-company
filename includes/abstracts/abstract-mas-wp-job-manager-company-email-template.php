<?php
/**
 * File containing the class MAS_WPJMC_Email_Template.
 *
 * @package mas-wp-job-manager-company
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MAS_WPJMC_Email_Template
 */
abstract class MAS_WPJMC_Email_Template extends WP_Job_Manager_Email_Template {
	/**
	 * Get the template path for overriding templates.
	 *
	 * @type abstract
	 * @return string
	 */
	public static function get_template_path() {
		return 'mas-wp-job-manager-company';
	}

	/**
	 * Get the default template path that MAS WP Job Manager Company should look for the templates.
	 *
	 * @type abstract
	 * @return string
	 */
	public static function get_template_default_path() {
		return mas_wpjmc()->plugin_dir . 'templates/';
	}

	/**
	 * Expand arguments as necessary for the generation of the email.
	 *
	 * @param array $args Arguments used to generate the email.
	 * @return array
	 */
	protected function prepare_args( $args ) {
		// Fill in the job details.
		$args = parent::prepare_args( $args );

		// Default object is company so we want the `author` argument to be just for that.
		if ( isset( $args['author'] ) ) {
			$args['job_author'] = $args['author'];
			unset( $args['author'] );
		}

		if ( isset( $args['company_id'] ) ) {
			$company = get_post( $args['company_id'] );
			if ( $company instanceof WP_Post ) {
				$args['company'] = $company;
			}
		}

		if ( isset( $args['company'] ) && $args['company'] instanceof WP_Post ) {
			$author = get_user_by( 'ID', $args['company']->post_author );
			if ( $author instanceof WP_User ) {
				$args['author'] = $author;
			}
		}

		return $args;
	}
}
