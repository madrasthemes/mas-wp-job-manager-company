<?php
/**
 * Template for the company dashboard (`[mas_company_dashboard]`) shortcode.
 *
 * This template can be overridden by copying it to yourtheme/mas-wp-job-manager-company/company-dashboard.php.
 *
 * @package MAS Companies For WP Job Manager
 * @version 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$submission_limit            = get_option( 'job_manager_company_submission_limit' );
$submit_company_form_page_id = get_option( 'job_manager_submit_company_form_page_id' );
?>
<div id="job-manager-company-dashboard">
	<p><?php echo esc_html( _n( 'Your company can be viewed, edited or removed below.', 'Your companies can be viewed, edited or removed below.', mas_wpjmc_company_manager_count_user_companies(), 'mas-wp-job-manager-company' ) ); ?></p>
	<table class="job-manager-companies">
		<thead>
			<tr>
				<?php foreach ( $company_dashboard_columns as $key => $column ) : ?>
					<th class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! $companies ) : ?>
				<tr>
					<td colspan="<?php echo sizeof( $company_dashboard_columns ); ?>"><?php _e( 'You do not have any active company listings.', 'mas-wp-job-manager-company' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $companies as $company ) : ?>
					<tr>
						<?php foreach ( $company_dashboard_columns as $key => $column ) : ?>
							<td class="<?php echo esc_attr( $key ); ?>">
								<?php if ( 'company-title' === $key ) : ?>
									<?php if ( $company->post_status == 'publish' ) : ?>
										<a href="<?php echo get_permalink( $company->ID ); ?>"><?php echo esc_html( $company->post_title ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $company->post_title ); ?>
									<?php endif; ?>
									<ul class="company-dashboard-actions">
										<?php
											$actions = array();

											switch ( $company->post_status ) {
												case 'publish' :
													$actions['edit'] = array(
														'label' => esc_html__( 'Edit', 'mas-wp-job-manager-company' ),
														'nonce' => false
													);
													$actions['hide'] = array(
														'label' => esc_html__( 'Hide', 'mas-wp-job-manager-company' ),
														'nonce' => true
													);
												break;
												case 'private' :
													$actions['publish'] = array(
														'label' => esc_html__( 'Publish', 'mas-wp-job-manager-company' ),
														'nonce' => true
													);
												break;
												case 'hidden' :
													$actions['edit'] = array(
														'label' => esc_html__( 'Edit', 'mas-wp-job-manager-company' ),
														'nonce' => false
													);
													$actions['publish'] = array(
														'label' => esc_html__( 'Publish', 'mas-wp-job-manager-company' ),
														'nonce' => true
													);
												break;
												case 'pending' :
												case 'pending_review' :
													if ( get_option( 'job_manager_user_can_edit_pending_company_submissions' ) ) {
														$actions['edit'] = array(
															'label' => esc_html__( 'Edit', 'mas-wp-job-manager-company' ),
															'nonce' => false
														);
													}
												break;
												case 'expired' :
													if ( get_option( 'job_manager_manager_submit_company_form_page_id' ) ) {
														$actions['relist'] = array(
															'label' => esc_html__( 'Relist', 'mas-wp-job-manager-company' ),
															'nonce' => true
														);
													}
												break;
											}

											$actions['delete'] = array( 'label' => esc_html__( 'Delete', 'mas-wp-job-manager-company' ), 'nonce' => true );

											$actions = apply_filters( 'mas_job_manager_company_my_company_actions', $actions, $company );

											foreach ( $actions as $action => $value ) {
												$action_url = add_query_arg( array( 'action' => $action, 'company_id' => $company->ID ) );
												if ( $value['nonce'] )
													$action_url = wp_nonce_url( $action_url, 'mas_job_manager_company_my_company_actions' );
												echo '<li><a href="' . esc_url($action_url) . '" class=" company-dashboard-action-' . esc_attr($action) . '">' .  esc_html($value['label']) . '</a></li>';
											}
										?>
									</ul>
								<?php elseif ( 'status' === $key ) : ?>
									<?php mas_wpjmc_the_company_status( $company ); ?>
								<?php elseif ( 'date' === $key ) : ?>
									<?php echo date_i18n( get_option( 'date_format' ), strtotime( $company->post_date ) ); ?>
								<?php else : ?>
									<?php do_action( 'mas_job_manager_company_dashboard_column_' . $key, $company ); ?>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		<?php if ( $submit_company_form_page_id && ( mas_wpjmc_company_manager_count_user_companies() < $submission_limit || ! $submission_limit ) ) : ?>
			<tfoot>
				<tr>
					<td colspan="<?php echo sizeof( $company_dashboard_columns ); ?>">
						<a href="<?php echo esc_url( get_permalink( $submit_company_form_page_id ) ); ?>"><?php _e( 'Add Company', 'mas-wp-job-manager-company' ); ?></a>
					</td>
				</tr>
			</tfoot>
		<?php endif; ?>
	</table>
	<?php get_job_manager_template( 'pagination.php', array( 'max_num_pages' => $max_num_pages ) ); ?>
</div>
