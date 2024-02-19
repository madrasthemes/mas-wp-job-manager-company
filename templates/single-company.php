<?php
/**
 * The template for displaying all single posts.
 *
 * @package MAS Companies For WP Job Manager
 */

get_header();

do_action( 'single_company_content_before' );

?>

<?php

while ( have_posts() ) :

	the_post();

	do_action( 'single_company_content_start' );

	get_job_manager_template( 'content-single-company.php', array(), 'mas-wp-job-manager-company', mas_wpjmc()->plugin_dir . 'templates/' );

	do_action( 'single_company_content_end' );

endwhile;

?>

<?php

do_action( 'single_company_content_after' );

get_footer();
