<?php
/**
 * Company in the loop.
 *
 * This template can be overridden by copying it to yourtheme/mas-wp-job-manager-company/content-company.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

// Ensure visibility.
if ( empty( $post ) ) {
	return;
}

?>

<li <?php mas_wpjmc_company_class(); ?>>
	<?php
		do_action( 'company_content_area_before' );

		do_action( 'company_start' );

		do_action( 'company' );

		do_action( 'company_end' );

		do_action( 'company_content_area_after' );
	?>
</li>
