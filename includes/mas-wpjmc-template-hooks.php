<?php
/*
 *
 * Template Functions
 *
 */

add_filter( 'the_company_name', 'mas_wpjmc_get_job_listing_company_name', 10, 2 );
add_filter( 'submit_job_form_fields', 'mas_wpjmc_edit_submit_job_form_fields', 10 );
add_filter( 'job_listing_search_conditions', 'mas_wpjmc_edit_job_listing_search_conditions', 10, 2 );

/*
 * Email Handling
 */

add_filter( 'job_manager_email_notifications', 'mas_wpjmc_email_notifications', 20 );
add_action( 'job_manager_email_init', 'mas_wpjmc_email_init', 20 );
add_action( 'company_manager_company_submitted', 'mas_wpjmc_send_new_company_notification', 10 );
add_action( 'company_manager_user_edit_comapny', 'mas_wpjmc_send_updated_company_notification', 10 );

add_action( 'single_company_start', 'mas_wpjmc_single_company_content_open', 10 );
add_action( 'single_company', 'mas_wpjmc_single_company_header', 10 );
add_action( 'single_company', 'mas_wpjmc_single_company_features', 20 );
add_action( 'single_company', 'mas_wpjmc_single_company_description', 30 );
add_action( 'single_company', 'mas_wpjmc_single_company_video', 40 );
add_action( 'single_company_end', 'mas_wpjmc_single_company_content_close', 10 );

add_action( 'company_start', 'mas_wpjmc_company_loop_open', 10 );
add_action( 'company', 'mas_wpjmc_company_loop_content', 10 );
add_action( 'company_end', 'mas_wpjmc_company_loop_close', 10 );

add_action( 'company_before_loop', 'mas_wpjmc_setup_loop' );
add_action( 'company_after_loop', 'mas_wpjmc_pagination', 100 );
add_action( 'company_after_loop', 'mas_wpjmc_reset_loop', 999 );