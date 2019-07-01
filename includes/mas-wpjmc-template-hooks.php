<?php
/*
 *
 * Template Functions
 *
 */

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