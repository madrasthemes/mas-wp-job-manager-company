<?php
/**
 * Message to display when a company has been submitted.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-company/company-submitted.php.
 *
 * @author      Madras Themes
 * @package     MAS Companies For WP Job Manager
 * @category    Template
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

switch ( $company->post_status ) :
	case 'publish' :
		if ( $company->ID ) {
			printf( '<p class="company-submitted">' . esc_html__( 'Your company has been submitted successfully. To view your company <a href="%s">click here</a>.', 'mas-wp-job-manager-company' ) . '</p>', get_permalink( $company->ID ) );
		} else {
			print( '<p class="company-submitted">' . esc_html__( 'Your company has been submitted successfully.', 'mas-wp-job-manager-company' ) . '</p>' );
		}
	break;
	case 'pending' :
		print( '<p class="company-submitted">' . esc_html__( 'Your company has been submitted successfully and is pending approval.', 'mas-wp-job-manager-company' ) . '</p>' );
	break;
	default :
		do_action( 'company_manager_company_submitted_content_' . str_replace( '-', '_', sanitize_title( $company->post_status ) ), $company );
	break;
endswitch;
