<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

// Ensure visibility.
if ( empty( $post ) ) {
	return;
}

do_action( 'single_company_before_start' );
do_action( 'single_company_start' );
do_action( 'single_company' );
do_action( 'single_company_end' );
do_action( 'single_company_after_end' );
