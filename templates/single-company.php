<?php
/**
 * The template for displaying single company posts.
 *
 * @package MAS Companies For WP Job Manager
 */

if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {

	// ✅ Render block template parts BEFORE wp_head() so their CSS can be printed in <head>.
	$header_html = function_exists( 'do_blocks' )
		? do_blocks( '<!-- wp:template-part {"slug":"header"} /-->' )
		: '';

	$footer_html = function_exists( 'do_blocks' )
		? do_blocks( '<!-- wp:template-part {"slug":"footer"} /-->' )
		: '';

	?><!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<div class="wp-site-blocks">
		<?php
		// Echo the already-rendered header/footer.
		echo $header_html;
		?>

		<main>
			<?php
			do_action( 'single_company_content_before' );

			while ( have_posts() ) :
				the_post();

				do_action( 'single_company_content_start' );

				get_job_manager_template(
					'content-single-company.php',
					array(),
					'mas-wp-job-manager-company',
					mas_wpjmc()->plugin_dir . 'templates/'
				);

				do_action( 'single_company_content_end' );

			endwhile;

			do_action( 'single_company_content_after' );
			?>
		</main>

		<?php echo $footer_html; ?>
	</div>

	<?php wp_footer(); ?>
	</body>
	</html>
	<?php
	return;
}

// Classic themes:
get_header();

do_action( 'single_company_content_before' );

while ( have_posts() ) :
	the_post();

	do_action( 'single_company_content_start' );

	get_job_manager_template(
		'content-single-company.php',
		array(),
		'mas-wp-job-manager-company',
		mas_wpjmc()->plugin_dir . 'templates/'
	);

	do_action( 'single_company_content_end' );

endwhile;

do_action( 'single_company_content_after' );

get_footer();
