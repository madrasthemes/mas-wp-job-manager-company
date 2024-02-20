<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * MAS_WP_Job_Manager_Company_Form_Submit_Company class.
 */
class MAS_WP_Job_Manager_Company_Form_Submit_Company extends WP_Job_Manager_Form {

	public $form_name = 'submit-company';
	public $company_id;
	public $preview_company;

	/** @var MAS_WP_Job_Manager_Company_Form_Submit_Company The single instance of the class */
	protected static $_instance = null;

	/**
	 * Main Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'process' ) );

		if ( $this->use_recaptcha_field() ) {
			add_action( 'submit_company_form_company_fields_end', array( $this, 'display_recaptcha_field' ) );
			add_action( 'submit_company_form_validate_fields', array( $this, 'validate_recaptcha_field' ) );
		}

		$this->steps  = (array) apply_filters( 'submit_company_steps', array(
			'submit' => array(
				'name'     => esc_html__( 'Submit Details', 'mas-wp-job-manager-company' ),
				'view'     => array( $this, 'submit' ),
				'handler'  => array( $this, 'submit_handler' ),
				'priority' => 10
				),
			'preview' => array(
				'name'     => esc_html__( 'Preview', 'mas-wp-job-manager-company' ),
				'view'     => array( $this, 'preview' ),
				'handler'  => array( $this, 'preview_handler' ),
				'priority' => 20
			),
			'done' => array(
				'name'     => esc_html__( 'Done', 'mas-wp-job-manager-company' ),
				'view'     => array( $this, 'done' ),
				'before'   => array(  $this, 'done_before' ),
				'handler'  => '',
				'priority' => 30
			)
		) );

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		// Get step/company
		if ( ! empty( $_REQUEST['step'] ) ) {
			$this->step = is_numeric( $_REQUEST['step'] ) ? max( absint( $_REQUEST['step'] ), 0 ) : array_search( $_REQUEST['step'], array_keys( $this->steps ) );
		}

		$this->company_id = ! empty( $_REQUEST['company_id'] ) ? absint( $_REQUEST[ 'company_id' ] ) : 0;

		if ( ! mas_wpjmc_company_manager_user_can_edit_company( $this->get_company_id() ) ) {
			$this->company_id = 0;
		}

		// Load company details
		if ( $this->get_company_id() ) {
			$company_status = get_post_status( $this->get_company_id() );
			if ( 'expired' === $company_status ) {
				if ( ! mas_wpjmc_company_manager_user_can_edit_company( $this->get_company_id() ) ) {
					$this->company_id = 0;
					$this->step      = 0;
				}
			} elseif ( 0 === $this->step && ! in_array( $company_status, apply_filters( 'mas_wp_job_manager_company_valid_submit_company_statuses', array( 'preview' ) ) ) ) {
				$this->company_id = 0;
				$this->step      = 0;
			}
		}
	}

	/**
	 * Get the submitted company ID
	 * @return int
	 */
	public function get_company_id() {
		return absint( $this->company_id );
	}

	/**
	 * Get a field from either company manager or job manager
	 */
	public function get_field_template( $key, $field ) {
		switch ( $field['type'] ) {
			case 'repeated' :
				get_job_manager_template( 'form-fields/repeated-field.php', array( 'key' => $key, 'field' => $field, 'class' => $this ), 'mas-wp-job-manager-company', mas_wpjmc()->plugin_dir . 'templates/' );
			break;
			default :
				get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field, 'class' => $this ) );
			break;
		}
	}

	/**
	 * init_fields function.
	 */
	public function init_fields() {
		if ( $this->fields ) {
			return;
		}

		$this->fields = apply_filters( 'submit_company_form_fields', array(
			'company_fields' => array(
				'company_name'      => array(
					'label'         => esc_html__( 'Company name', 'mas-wp-job-manager-company' ),
					'type'          => 'text',
					'required'      => true,
					'placeholder'   => esc_html__( 'Company full name', 'mas-wp-job-manager-company' ),
					'priority'      => 5,
				),
				'company_tagline'   => array(
					'label'         => esc_html__( 'Company Tagline', 'mas-wp-job-manager-company' ),
					'type'          => 'text',
					'required'      => false,
					'placeholder'   => esc_html__( 'Company tagline', 'mas-wp-job-manager-company' ),
					'priority'      => 10,
				),
				'company_location'  => array(
					'label'         => esc_html__( 'Headquarters', 'mas-wp-job-manager-company' ),
					'description'   => esc_html__( 'Leave this blank if the headquarters location is not important', 'mas-wp-job-manager-company' ),
					'type'          => 'text',
					'required'      => false,
					'placeholder'   => esc_html__( 'e.g. "London"', 'mas-wp-job-manager-company' ),
					'priority'      => 15,
				),
				'company_logo'      => array(
					'label'         => esc_html__( 'Company Logo', 'mas-wp-job-manager-company' ),
					'type'          => 'file',
					'required'      => false,
					'placeholder'   => '',
					'priority'      => 20,
					'ajax'          => true,
					'allowed_mime_types' => array(
						'jpg'  => 'image/jpeg',
						'jpeg' => 'image/jpeg',
						'gif'  => 'image/gif',
						'png'  => 'image/png',
					),
					'personal_data'      => true,
				),
				'company_video'     => array(
					'label'         => esc_html__( 'Video', 'mas-wp-job-manager-company' ),
					'type'          => 'text',
					'required'      => false,
					'priority'      => 25,
					'placeholder'   => esc_html__( 'A link to a video about yourself', 'mas-wp-job-manager-company' ),
				),
				'company_since'     => array(
					'label'         => esc_html__( 'Since', 'mas-wp-job-manager-company' ),
					'type'          => 'date',
					'required'      => false,
					'placeholder'   => esc_html__( 'Established date/year', 'mas-wp-job-manager-company' ),
					'priority'      => 30,
				),
				'company_website'   => array(
					'label'         => esc_html__( 'Company Website', 'mas-wp-job-manager-company' ),
					'type'          => 'text',
					'required'      => false,
					'placeholder'   => esc_html__( 'Company website', 'mas-wp-job-manager-company' ),
					'priority'      => 35,
				),
				'company_email'     => array(
					'label'         => esc_html__( 'Email', 'mas-wp-job-manager-company' ),
					'type'          => 'text',
					'required'      => false,
					'placeholder'   => esc_html__( 'you@yourdomain.com', 'mas-wp-job-manager-company' ),
					'priority'      => 40,
				),
				'company_phone'     => array(
					'label'         => esc_html__( 'Phone', 'mas-wp-job-manager-company' ),
					'type'          => 'text',
					'required'      => false,
					'placeholder'   => esc_html__( 'Phone Number', 'mas-wp-job-manager-company' ),
					'priority'      => 45,
				),
				'company_twitter'   => array(
					'label'         => esc_html__( 'Twitter', 'mas-wp-job-manager-company' ),
					'type'          => 'text',
					'required'      => false,
					'placeholder'   => esc_html__( 'Twitter page url', 'mas-wp-job-manager-company' ),
					'priority'      => 50,
				),
				'company_facebook'  => array(
					'label'         => esc_html__( 'Facebook', 'mas-wp-job-manager-company' ),
					'type'          => 'text',
					'required'      => false,
					'placeholder'   => esc_html__( 'Facebook page url', 'mas-wp-job-manager-company' ),
					'priority'      => 55,
				),
				'company_category'  => array(
					'label'         => esc_html__( 'Industry', 'mas-wp-job-manager-company' ),
					'type'          => 'term-select',
					'required'      => false,
					'placeholder'   => esc_html__( 'Choose Industry&hellip;', 'mas-wp-job-manager-company' ),
					'priority'      => 60,
					'default'       => '',
					'taxonomy'      => 'company_category',
				),
				'company_strength'  => array(
					'label'         => esc_html__( 'Employer Strength', 'mas-wp-job-manager-company' ),
					'type'          => 'term-select',
					'required'      => false,
					'placeholder'   => '',
					'priority'      => 65,
					'default'       => '',
					'taxonomy'      => 'company_strength',
				),
				'company_average_salary'    => array(
					'label'         => esc_html__( 'Average Salary', 'mas-wp-job-manager-company' ),
					'type'          => 'term-select',
					'required'      => false,
					'placeholder'   => '',
					'priority'      => 70,
					'default'       => '',
					'taxonomy'      => 'company_average_salary',
				),
				'company_revenue'   => array(
					'label'         => esc_html__( 'Company Revenue', 'mas-wp-job-manager-company' ),
					'type'          => 'term-select',
					'required'      => false,
					'placeholder'   => '',
					'priority'      => 75,
					'default'       => '',
					'taxonomy'      => 'company_revenue',
				),
				'company_excerpt'   => array(
					'label'         => esc_html__( 'Short Description', 'mas-wp-job-manager-company' ),
					'type'          => 'textarea',
					'required'      => false,
					'priority'      => 80,
				),
				'company_content'   => array(
					'label'         => esc_html__( 'Company Content', 'mas-wp-job-manager-company' ),
					'type'          => 'wp-editor',
					'required'      => false,
					'priority'      => 85,
				),
			),
		) );
	}

	/**
	 * Reset the `fields` variable so it gets reinitialized. This should only be
	 * used for testing!
	 */
	public function reset_fields() {
		$this->fields = null;
	}

	/**
	 * Use reCAPTCHA field on the form?
	 *
	 * @return bool
	 */
	public function use_recaptcha_field() {
		if ( ! method_exists( $this, 'is_recaptcha_available' ) || ! $this->is_recaptcha_available() ) {
			return false;
		}
		return 1 === absint( get_option( 'job_manager_enable_recaptcha_company_submission' ) );
	}

	/**
	 * Get the value of a posted repeated field
	 * @since  1.0.0
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	public function get_posted_repeated_field( $key, $field ) {
		return apply_filters( 'submit_company_form_fields_get_repeated_field_data', $this->get_repeated_field( $key, $field['fields'] ) );
	}

	/**
	 * Validate the posted fields
	 *
	 * @return bool on success, WP_ERROR on failure
	 */
	protected function validate_fields( $values ) {
		foreach ( $this->fields as $group_key => $fields ) {
			foreach ( $fields as $key => $field ) {
				if ( $field['required'] && empty( $values[ $group_key ][ $key ] ) ) {
					return new WP_Error( 'validation-error', sprintf( esc_html__( '%s is a required field', 'mas-wp-job-manager-company' ), $field['label'] ) );
				}
				if ( ! empty( $field['taxonomy'] ) && in_array( $field['type'], array( 'term-checklist', 'term-select', 'term-multiselect' ) ) ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						foreach ( $values[ $group_key ][ $key ] as $term ) {
							if ( ! term_exists( $term, $field['taxonomy'] ) ) {
								return new WP_Error( 'validation-error', sprintf( esc_html__( '%s is invalid', 'mas-wp-job-manager-company' ), $field['label'] ) );
							}
						}
					} elseif ( ! empty( $values[ $group_key ][ $key ] ) ) {
						if ( ! term_exists( $values[ $group_key ][ $key ], $field['taxonomy'] ) ) {
							return new WP_Error( 'validation-error', sprintf( esc_html__( '%s is invalid', 'mas-wp-job-manager-company' ), $field['label'] ) );
						}
					}
				}

				if ( 'company_email' === $key ) {
					if ( ! empty( $values[ $group_key ][ $key ] ) && ! is_email( $values[ $group_key ][ $key ] ) ) {
						throw new Exception( esc_html__( 'Please enter a valid email address', 'mas-wp-job-manager-company' ) );
					}
				}

				if ( 'file' === $field['type'] ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						$check_value = array_filter( $values[ $group_key ][ $key ] );
					} else {
						$check_value = array_filter( array( $values[ $group_key ][ $key ] ) );
					}
					if ( ! empty( $check_value ) ) {
						foreach ( $check_value as $file_url ) {
							if ( is_numeric( $file_url ) ) {
								continue;
							}
							$file_url = esc_url( $file_url, array( 'http', 'https' ) );
							if ( empty( $file_url ) ) {
								throw new Exception( esc_html__( 'Invalid attachment provided.', 'mas-wp-job-manager-company' ) );
							}
						}
					}
				}
			}
		}

		return apply_filters( 'submit_company_form_validate_fields', true, $this->fields, $values );
	}

	/**
	 * Submit Step
	 */
	public function submit() {
		global $job_manager, $post;

		$this->init_fields();

		// Load data if neccessary
		if ( $this->get_company_id() ) {
			$company = get_post( $this->get_company_id() );
			foreach ( $this->fields as $group_key => $fields ) {
				foreach ( $fields as $key => $field ) {
					switch ( $key ) {
						case 'company_name' :
							$this->fields[ $group_key ][ $key ]['value'] = $company->post_title;
						break;
						case 'company_content' :
							$this->fields[ $group_key ][ $key ]['value'] = $company->post_content;
						break;
						case 'company_excerpt' :
							$this->fields[ $group_key ][ $key ]['value'] = $company->post_excerpt;
						break;
						case 'company_logo':
							$this->fields[ $group_key ][ $key ]['value'] = has_post_thumbnail( $company->ID ) ? get_post_thumbnail_id( $company->ID ) : get_post_meta( $company->ID, '_' . $key, true );
							break;
						default:
							if ( ! empty( $field['taxonomy'] ) && in_array( $field['type'], array( 'term-checklist', 'term-select', 'term-multiselect' ) ) ) {
								$this->fields[ $group_key ][ $key ]['value'] = wp_get_object_terms( $company->ID, $field['taxonomy'], array( 'fields' => 'ids' ) );
							} else {
								$this->fields[ $group_key ][ $key ]['value'] = get_post_meta( $company->ID, '_' . $key, true );
							}
						break;
					}
				}
			}

			$this->fields = apply_filters( 'submit_company_form_fields_get_company_data', $this->fields, $company );

		// Get user meta
		} elseif ( is_user_logged_in() && empty( $_POST['submit_company'] ) ) {
			$user = wp_get_current_user();
			foreach ( $this->fields as $group_key => $fields ) {
				foreach ( $fields as $key => $field ) {
					switch ( $key ) {
						case 'company_name' :
							$this->fields[ $group_key ][ $key ]['value'] = $user->first_name . ' ' . $user->last_name;
						break;
						case 'company_email' :
							$this->fields[ $group_key ][ $key ]['value'] = $user->user_email;
						break;
					}
				}
			}
			$this->fields = apply_filters( 'submit_company_form_fields_get_user_data', $this->fields, get_current_user_id() );
		}

		get_job_manager_template( 'company-submit.php', array(
			'class'              => $this,
			'form'               => $this->form_name,
			'company_id'         => $this->get_company_id(),
			'action'             => $this->get_action(),
			'company_fields'     => $this->get_fields( 'company_fields' ),
			'step'               => $this->get_step(),
			'submit_button_text' => apply_filters( 'submit_company_form_submit_button_text', __( 'Preview &rarr;', 'mas-wp-job-manager-company' ) )
		), 'mas-wp-job-manager-company', mas_wpjmc()->plugin_dir . 'templates/' );
	}

	/**
	 * Submit Step is posted
	 */
	public function submit_handler() {
		try {

			// Init fields
			$this->init_fields();

			// Get posted values
			$values = $this->get_posted_fields();

			if ( empty( $_POST['submit_company'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'submit_form_posted' ) )
				return;

			// Validate required
			if ( is_wp_error( ( $return = $this->validate_fields( $values ) ) ) ) {
				throw new Exception( $return->get_error_message() );
			}

			// Account creation
			if ( ! is_user_logged_in() ) {
				$create_account = false;

				if ( job_manager_enable_registration() ) {
					if ( job_manager_user_requires_account() ) {
						if ( ! job_manager_generate_username_from_email() && empty( $_POST['create_account_username'] ) ) {
							throw new Exception( esc_html__( 'Please enter a username.', 'mas-wp-job-manager-company' ) );
						}
						if ( ! wpjm_use_standard_password_setup_email() ) {
							if ( empty( $_POST['create_account_password'] ) ) {
								throw new Exception( esc_html__( 'Please enter a password.', 'mas-wp-job-manager-company' ) );
							}
						}
						if ( empty( $_POST['company_email'] ) ) {
							throw new Exception( esc_html__( 'Please enter your email address.', 'mas-wp-job-manager-company' ) );
						}
					}

					if ( ! wpjm_use_standard_password_setup_email() && ! empty( $_POST['create_account_password'] ) ) {
						if ( empty( $_POST['create_account_password_verify'] ) || $_POST['create_account_password_verify'] !== $_POST['create_account_password'] ) {
							throw new Exception( esc_html__( 'Passwords must match.', 'mas-wp-job-manager-company' ) );
						}
						if ( ! wpjm_validate_new_password( $_POST['create_account_password'] ) ) {
							$password_hint = wpjm_get_password_rules_hint();
							if ( $password_hint ) {
								throw new Exception( sprintf( esc_html__( 'Invalid Password: %s', 'mas-wp-job-manager-company' ), $password_hint ) );
							} else {
								throw new Exception( esc_html__( 'Password is not valid.', 'mas-wp-job-manager-company' ) );
							}
						}
					}

					if ( ! empty( $_POST['company_email'] ) ) {
						if ( version_compare( JOB_MANAGER_VERSION, '1.20.0', '<' ) ) {
							$create_account = wp_job_manager_create_account( $_POST['company_email'], get_option( 'job_manager_registration_role', 'employer' ) );
						} else {
							$create_account = wp_job_manager_create_account( array(
								'username' => ( job_manager_generate_username_from_email() || empty( $_POST['create_account_username'] ) ) ? '' : sanitize_user( $_POST['create_account_username'] ),
								'password' => ( wpjm_use_standard_password_setup_email() || empty( $_POST['create_account_password'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['create_account_password'] ) ),
								'email'    => sanitize_email( $_POST['company_email'] ),
								'role'     => sanitize_key( get_option( 'job_manager_registration_role', 'employer' ) ),
							) );
						}
					}
				}

				if ( is_wp_error( $create_account ) ) {
					throw new Exception( $create_account->get_error_message() );
				}
			}

			if ( job_manager_user_requires_account() && ! is_user_logged_in() ) {
				throw new Exception( esc_html__( 'You must be signed in to post your company.', 'mas-wp-job-manager-company' ) );
			}

			// Update the job
			$this->save_company( $values['company_fields']['company_name'], $values['company_fields']['company_content'], $values['company_fields']['company_excerpt'], $this->get_company_id() ? '' : 'preview', $values );
			$this->update_company_data( $values );

			// Successful, show next step
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Update or create a job listing from posted data
	 *
	 * @param  string $post_title
	 * @param  string $post_content
	 * @param  string $status
	 */
	protected function save_company( $post_title, $post_content, $post_excerpt, $status = 'preview', $values = array() ) {
		// Get random key
		if ( $this->get_company_id() ) {
			$prefix = get_post_meta( $this->get_company_id(), '_company_name_prefix', true );

			if ( ! $prefix ) {
				$prefix = wp_generate_password( 10 );
			}
		} else {
			$prefix        = wp_generate_password( 10 );
		}

		$company_slug   = array();
		$company_slug[] = current( explode( ' ', $post_title ) );
		$company_slug[] = $prefix;

		if ( ! empty( $values['company_fields']['company_location'] ) ) {
			$company_slug[] = $values['company_fields']['company_location'];
		}

		$data = array(
			'post_title'     => $post_title,
			'post_content'   => $post_content,
			'post_excerpt'   => $post_excerpt,
			'post_type'      => 'company',
			'post_password'  => '',
			'post_name'      => sanitize_title( implode( '-', $company_slug ) )
		);

		if ( $status ) {
			$data['post_status'] = $status;
		}

		$data = apply_filters( 'submit_company_form_save_company_data', $data, $post_title, $post_content, $post_excerpt, $status, $values, $this );

		if ( $this->get_company_id() ) {
			$data['ID'] = $this->get_company_id();
			wp_update_post( $data );
		} else {
			$this->company_id = wp_insert_post( $data );
			update_post_meta( $this->get_company_id(), '_company_name_prefix', $prefix );
			update_post_meta( $this->get_company_id(), '_public_submission', true );

			// If and only if we're dealing with a logged out user and that is allowed, allow the user to continue a submission after it was started.
			if ( ! is_user_logged_in() && ! job_manager_user_requires_account() ) {
				$submitting_key = sha1( uniqid() );
				setcookie( 'mas-wp-job-manager-company-submitting-company-key-' . $this->get_company_id(), $submitting_key, 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
				update_post_meta( $this->get_company_id(), '_submitting_key', $submitting_key );
			}
		}
	}

	/**
	 * Set job meta + terms based on posted values
	 *
	 * @param  array $values
	 */
	protected function update_company_data( $values ) {
		// Set defaults

		$maybe_attach = array();

		// Loop fields and save meta and term data
		foreach ( $this->fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				// Save taxonomies
				if ( ! empty( $field['taxonomy'] ) ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						wp_set_object_terms( $this->get_company_id(), $values[ $group_key ][ $key ], $field['taxonomy'], false );
					} else {
						wp_set_object_terms( $this->get_company_id(), array( $values[ $group_key ][ $key ] ), $field['taxonomy'], false );
					}

				// Save meta data
				} else {
					update_post_meta( $this->get_company_id(), '_' . $key, $values[ $group_key ][ $key ] );
				}

				// Handle attachments
				if ( 'file' === $field['type'] ) {
					// Must be absolute
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						foreach ( $values[ $group_key ][ $key ] as $file_url ) {
							$maybe_attach[] = str_replace( array( WP_CONTENT_URL, site_url() ), array( WP_CONTENT_DIR, ABSPATH ), $file_url );
						}
					} else {
						$maybe_attach[] = str_replace( array( WP_CONTENT_URL, site_url() ), array( WP_CONTENT_DIR, ABSPATH ), $values[ $group_key ][ $key ] );
					}
				}
			}
		}

		// Handle attachments
		if ( sizeof( $maybe_attach ) && mas_wpjmc_company_manager_attach_uploaded_files() ) {
			/** WordPress Administration Image API */
			include_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Get attachments
			$attachments     = get_posts( 'post_parent=' . $this->get_company_id() . '&post_type=attachment&fields=ids&post_mime_type=image&numberposts=-1' );
			$attachment_urls = array();

			// Loop attachments already attached to the job
			foreach ( $attachments as $attachment_key => $attachment ) {
				$attachment_urls[] = str_replace( array( WP_CONTENT_URL, site_url() ), array( WP_CONTENT_DIR, ABSPATH ), wp_get_attachment_url( $attachment ) );
			}

			foreach ( $maybe_attach as $attachment_url ) {
				$attachment_url = esc_url( $attachment_url, array( 'http', 'https' ) );

				if ( empty( $attachment_url ) ) {
					continue;
				}

				if ( ! in_array( $attachment_url, $attachment_urls ) ) {
					$attachment = array(
						'post_title'   => get_the_title( $this->get_company_id() ),
						'post_content' => '',
						'post_status'  => 'inherit',
						'post_parent'  => $this->get_company_id(),
						'guid'         => $attachment_url
					);

					if ( $info = wp_check_filetype( $attachment_url ) ) {
						$attachment['post_mime_type'] = $info['type'];
					}

					$attachment_id = wp_insert_attachment( $attachment, $attachment_url, $this->get_company_id() );

					if ( ! is_wp_error( $attachment_id ) ) {
						wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $attachment_url ) );
					}
				}
			}
		}

		do_action( 'mas_job_manager_company_update_company_data', $this->get_company_id(), $values );
	}

	/**
	 * Preview Step
	 */
	public function preview() {
		global $post, $company_preview;

		wp_enqueue_script( 'mas-wp-job-manager-company-submission' );

		if ( $this->get_company_id() ) {

			$company_preview = true;
			$post = get_post( $this->get_company_id() );
			setup_postdata( $post );
			?>
			<form method="post" id="company_preview">
				<div class="company_preview_title">
					<input type="submit" name="continue" id="company_preview_submit_button" class="button" value="<?php echo apply_filters( 'submit_company_step_preview_submit_text', esc_html__( 'Submit Company &rarr;', 'mas-wp-job-manager-company' ) ); ?>" />
					<input type="submit" name="edit_company" class="button" value="<?php esc_html_e( '&larr; Edit company', 'mas-wp-job-manager-company' ); ?>" />
					<input type="hidden" name="company_id" value="<?php echo esc_attr( $this->get_company_id() ); ?>" />
					<input type="hidden" name="step" value="<?php echo esc_attr( $this->step ); ?>" />
					<input type="hidden" name="company_manager_form" value="<?php echo esc_attr( $this->form_name ); ?>" />
					<h2>
						<?php _e( 'Preview', 'mas-wp-job-manager-company' ); ?>
					</h2>
				</div>
				<div class="company_preview single-company">
					<h1><?php the_title(); ?></h1>
					<?php get_job_manager_template_part( 'content-single', 'company', 'mas-wp-job-manager-company', mas_wpjmc()->plugin_dir . 'templates/' ); ?>
				</div>
			</form>
			<?php

			wp_reset_postdata();
		}
	}

	/**
	 * Preview Step Form handler
	 */
	public function preview_handler() {
		if ( ! $_POST ) {
			return;
		}

		// Edit = show submit form again
		if ( ! empty( $_POST['edit_company'] ) ) {
			$this->step --;
		}

		// Continue = change job status then show next screen
		if ( ! empty( $_POST['continue'] ) ) {
			$company = get_post( $this->get_company_id() );

			if ( in_array( $company->post_status, array( 'preview' ) ) ) {
				// Update listing
				$update_company                  = array();
				$update_company['ID']            = $company->ID;
				$update_company['post_date']     = current_time( 'mysql' );
				$update_company['post_date_gmt'] = current_time( 'mysql', 1 );
				$update_company['post_author']   = get_current_user_id();
				$update_company['post_status']   = apply_filters( 'submit_company_post_status', get_option( 'job_manager_company_submission_requires_approval' ) ? 'pending' : 'publish', $company );

				wp_update_post( $update_company );
			}

			$this->step ++;

		}
	}

	/**
	 * Done Step
	 */
	public function done() {
		get_job_manager_template( 'company-submitted.php', array( 'company' => get_post( $this->get_company_id() ) ), 'mas-wp-job-manager-company', mas_wpjmc()->plugin_dir . 'templates/' );
	}

	/**
	 * Handles the company submissions before the view is called.
	 */
	public function done_before() {
		do_action( 'company_manager_company_submitted', $this->company_id );
	}

	/**
	 * @return array
	 */
	public static function get_company_fields() {
		$instance = self::instance();
		$instance->init_fields();
		return $instance->get_fields( 'company_fields' );
	}
}
