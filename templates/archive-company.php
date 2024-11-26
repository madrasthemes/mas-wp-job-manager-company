<?php

get_header();

do_action( 'company_before_loop_content' );

if ( have_posts() ) {

	do_action( 'company_before_loop' );

	echo '<ul class="wpjmc-companies">';

	do_action( 'company_loop_start' );

	while ( have_posts() ) :
		the_post();

		do_action( 'company_loop' );

		get_job_manager_template_part( 'content', 'company' );

	endwhile; // End of the loop.

	do_action( 'company_loop_end' );

	echo '</ul>';

	do_action( 'company_after_loop' );

} else {
	do_action( 'company_output_no_results' );
}

do_action( 'company_after_loop_content' );

get_footer();
