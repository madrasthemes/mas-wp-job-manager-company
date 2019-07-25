<?php
/**
 * Message to display when a company has been submitted.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companys/company-submitted.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     Mas WP Job Manager Company
 * @category    Template
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

switch ( $company->post_status ) :
	case 'publish' :
		if ( $company->ID ) {
			printf( '<p class="company-submitted">' . __( 'Your company has been submitted successfully. To view your company <a href="%s">click here</a>.', 'mas-wp-job-manager-companies' ) . '</p>', get_permalink( $company->ID ) );
		} else {
			print( '<p class="company-submitted">' . __( 'Your company has been submitted successfully.', 'mas-wp-job-manager-companies' ) . '</p>' );
		}
	break;
	case 'pending' :
		print( '<p class="company-submitted">' . __( 'Your company has been submitted successfully and is pending approval.', 'mas-wp-job-manager-companies' ) . '</p>' );
	break;
	default :
		do_action( 'company_manager_company_submitted_content_' . str_replace( '-', '_', sanitize_title( $company->post_status ) ), $company );
	break;
endswitch;
