<?php

require_once 'class-mas-wp-job-manager-company-form-submit-company.php';

/**
 * MAS_WP_Job_Manager_Company_Form_Edit_Company class.
 */
class MAS_WP_Job_Manager_Company_Form_Edit_Company extends MAS_WP_Job_Manager_Company_Form_Submit_Company {

	/**
	 * Form slug.
	 *
	 * @var string
	 */
	public $form_name = 'edit-company';

	/** @var MAS_WP_Job_Manager_Company_Form_Edit_Company The single instance of the class */
	protected static $instance = null;

	/**
	 * Main Instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->company_id = ! empty( $_REQUEST['company_id'] ) ? absint( $_REQUEST['company_id'] ) : 0;

		if ( ! mas_wpjmc_company_manager_user_can_edit_company( $this->company_id ) ) {
			$this->company_id = 0;
		}
	}

	/**
	 * output function.
	 */
	public function output( $atts = array() ) {
		$this->submit_handler();
		$this->submit();
	}

	/**
	 * Submit Step
	 */
	public function submit() {
		global $post;

		$company = get_post( $this->company_id );

		if ( empty( $this->company_id ) || ( $company->post_status !== 'publish' && $company->post_status !== 'hidden' && ! get_option( 'job_manager_user_can_edit_pending_company_submissions' ) ) ) {
			echo wpautop( esc_html__( 'Invalid company', 'mas-wp-job-manager-company' ) );
			return;
		}

		$this->init_fields();

		foreach ( $this->fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				if ( ! isset( $this->fields[ $group_key ][ $key ]['value'] ) ) {
					if ( 'company_name' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = $company->post_title;
					} elseif ( 'company_content' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = $company->post_content;
					} elseif ( 'company_excerpt' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = $company->post_excerpt;
					} elseif ( ! empty( $field['taxonomy'] ) ) {
						$this->fields[ $group_key ][ $key ]['value'] = wp_get_object_terms( $company->ID, $field['taxonomy'], array( 'fields' => 'ids' ) );
					} else {
						$this->fields[ $group_key ][ $key ]['value'] = get_post_meta( $company->ID, '_' . $key, true );
					}
				}
			}
		}

		$this->fields = apply_filters( 'submit_company_form_fields_get_company_data', $this->fields, $company );

		get_job_manager_template(
			'company-submit.php',
			array(
				'class'              => $this,
				'form'               => $this->form_name,
				'company_id'         => $this->get_company_id(),
				'action'             => $this->get_action(),
				'company_fields'     => $this->get_fields( 'company_fields' ),
				'step'               => $this->get_step(),
				'submit_button_text' => esc_html__( 'Save changes', 'mas-wp-job-manager-company' ),
			),
			'mas-wp-job-manager-company',
			mas_wpjmc()->plugin_dir . 'templates/'
		);
	}

	/**
	 * Submit Step is posted
	 */
	public function submit_handler() {
		if ( empty( $_POST['submit_company'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'submit_form_posted' ) ) {
			return;
		}

		try {

			// Init fields
			$this->init_fields();

			// Get posted values
			$values = $this->get_posted_fields();

			// Validate required
			$return = $this->validate_fields( $values );
			if ( is_wp_error( $return ) ) {
				throw new Exception( $return->get_error_message() );
			}

			// Update the company
			$this->save_company( $values['company_fields']['company_name'], $values['company_fields']['company_content'], $values['company_fields']['company_excerpt'], '', $values );
			$this->update_company_data( $values );

			/**
			 * Fire action after the user edits a company.
			 *
			 * @since 1.0.2
			 *
			 * @param int    $company_id    Company ID.
			 * @param array  $values        Submitted values for company.
			 */
			do_action( 'company_manager_user_edit_comapny', $this->company_id, $values );

			// Successful
			echo '<div class="job-manager-message">' . esc_html__( 'Your changes have been saved.', 'mas-wp-job-manager-company' ), ' <a href="' . get_permalink( $this->company_id ) . '">' . esc_html__( 'View Company &rarr;', 'mas-wp-job-manager-company' ) . '</a></div>';

		} catch ( Exception $e ) {
			echo '<div class="job-manager-error">' . $e->getMessage() . '</div>';
			return;
		}
	}
}
