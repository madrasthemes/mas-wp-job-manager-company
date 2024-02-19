<?php
/**
 * Email content when notifying admin of a new company.
 *
 * This template can be overridden by copying it to yourtheme/mas-wp-job-manager-company/emails/admin-new-job.php.
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
?>
	<p>
		<?php
		echo wp_kses_post(
			sprintf(
				// translators: %1$s placeholder is URL to the blog. %2$s placeholder is the name of the site.
				__( 'A new company has been submitted to <a href="%1$s">%2$s</a>.', 'mas-wp-job-manager-company' ),
				home_url(),
				get_bloginfo( 'name' )
			)
		);
		switch ( $company->post_status ) {
			case 'publish':
				printf( ' ' . esc_html__( 'It has been published and is now available to the public.', 'mas-wp-job-manager-company' ) );
				break;
			case 'pending':
				echo wp_kses_post(
					sprintf(
						// translators: Placeholder %s is the admin company listings URL.
						' ' . __( 'It is awaiting approval by an administrator in <a href="%s">WordPress admin</a>.', 'mas-wp-job-manager-company' ),
						esc_url( admin_url( 'edit.php?post_type=company' ) )
					)
				);
				break;
		}
		?>
		</p>
<?php

/**
 * Show details about the company.
 *
 * @param WP_Post              $company        The company to show details for.
 * @param WP_Job_Manager_Email $email          Email object for the notification.
 * @param bool                 $sent_to_admin  True if this is being sent to an administrator.
 * @param bool                 $plain_text     True if the email is being sent as plain text.
 */
do_action( 'company_manager_email_company_details', $company, $email, true, $plain_text );
