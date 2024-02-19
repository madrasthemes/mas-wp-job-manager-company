<?php
/**
 * Email content when notifying admin of a new company.
 *
 * This template can be overridden by copying it to yourtheme/mas-wp-job-manager-company/emails/plain/admin-new-job.php.
 *
 * @package     mas-wp-job-manager-company
 * @version     1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * @var WP_Post $company
 */
$company = $args['company'];

// translators: %1$s placeholder is the name of the site, %2$s placeholder is URL to the blog.
printf( esc_html__( 'A new company has been submitted to %1$s (%2$s).', 'mas-wp-job-manager-company' ), esc_html( get_bloginfo( 'name' ) ), esc_url( home_url() ) );
switch ( $company->post_status ) {
	case 'publish':
		printf( ' ' . esc_html__( 'It has been published and is now available to the public.', 'mas-wp-job-manager-company' ) );
		break;
	case 'pending':
		// translators: Placeholder %s is the admin companys URL.
		printf( ' ' . esc_html__( 'It is awaiting approval by an administrator in WordPress admin (%s).', 'mas-wp-job-manager-company' ), esc_url( admin_url( 'edit.php?post_type=company' ) ) );
		break;
}

/**
 * Show details about the company.
 *
 * @param WP_Post              $company        The company to show details for.
 * @param WP_Job_Manager_Email $email          Email object for the notification.
 * @param bool                 $sent_to_admin  True if this is being sent to an administrator.
 * @param bool                 $plain_text     True if the email is being sent as plain text.
 */
do_action( 'job_manager_email_job_details', $company, $email, true, $plain_text );
