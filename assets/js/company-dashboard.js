jQuery(document).ready(function($) {

	$('.company-dashboard-action-delete').click(function() {
		var answer = confirm( mas_wp_job_manager_company_submission.i18n_confirm_delete );

		if (answer)
			return true;

		return false;
	});

});