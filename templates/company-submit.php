<?php
/**
 * Template to show when submitting a company.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companys/company-submit.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Resume Manager
 * @category    Template
 * @version     1.15.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

wp_enqueue_script( 'mas-wp-job-manager-company-submission' );
?>
<form action="<?php echo $action; ?>" method="post" id="submit-company-form" class="company-manager-form" enctype="multipart/form-data">

    <?php do_action( 'submit_company_form_start' ); ?>

    <?php if ( apply_filters( 'submit_company_form_show_signin', true ) ) : ?>

        <?php get_job_manager_template( 'account-signin.php', array( 'class' => $class ) ); ?>

    <?php endif; ?>

    <?php if ( company_manager_user_can_post_company() ) : ?>

        <!-- Company Fields -->
        <?php do_action( 'submit_company_form_company_fields_start' ); ?>

        <?php foreach ( $company_fields as $key => $field ) : ?>
            <fieldset class="fieldset-<?php esc_attr_e( $key ); ?> fieldset-type-<?php echo esc_attr( $field['type'] ); ?>">
                <label for="<?php esc_attr_e( $key ); ?>"><?php echo $field['label'] . apply_filters( 'submit_company_form_required_label', $field['required'] ? '' : ' <small>' . __( '(optional)', 'mas-wp-job-manager-companies' ) . '</small>', $field ); ?></label>
                <div class="field">
                    <?php $class->get_field_template( $key, $field ); ?>
                </div>
            </fieldset>
        <?php endforeach; ?>

        <?php do_action( 'submit_company_form_company_fields_end' ); ?>

        <p>
            <?php wp_nonce_field( 'submit_form_posted' ); ?>
            <input type="hidden" name="company_manager_form" value="<?php echo $form; ?>" />
            <input type="hidden" name="company_id" value="<?php echo esc_attr( $company_id ); ?>" />
            <input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
            <input type="submit" name="submit_company" class="button" value="<?php esc_attr_e( $submit_button_text ); ?>" />
        </p>

    <?php else : ?>

        <?php do_action( 'submit_company_form_disabled' ); ?>

    <?php endif; ?>

    <?php do_action( 'submit_company_form_end' ); ?>
</form>
