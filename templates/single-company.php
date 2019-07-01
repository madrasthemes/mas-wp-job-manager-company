<?php
/**
 * The template for displaying all single posts.
 *
 * @package Company
 */

get_header();

    do_action( 'single_company_content_before' );

        while ( have_posts() ) : the_post();

            do_action( 'single_company_content_start' );

            get_job_manager_template( 'content-single-company.php', array() , 'mas-wp-job-manager-companies', mas_wpjmc()->plugin_dir . 'templates/' );

            do_action( 'single_company_content_end' );

        endwhile;

    do_action( 'single_company_content_after' );

get_footer();