<div class="modal fade" id="reports-generation-modal" tabindex="-1" role="dialog" aria-labelledby="reports-generationLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="reports-generationLabel">Report Generation (@{{ config.display_name }})</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form-reports-generation" method="post" action="/mfn/finance-reports/generate">
					{{ csrf_field() }}
					<h4>A <strong>@{{ config.display_name }}</strong> report will be generated:</h4>
					<div class="row">
						<div class="form-group col-md-12">
	                        <input type="text" class="custom-datepicker" name="report_date" id="report_date">
	                        <label for="report_date">@{{ config.report_date_text }}</label>
						</div>
					</div>
					<input type="hidden" name="report_id" id="report_id" :value="config.id">
					<input type="hidden" name="report_name" id="report_name" :value="config.report_name">
				</form>
                <!-- <p>If you need to add/remove accounts, close this window and use the <strong>Edit Configuration</strong> button</p> -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit" form="form-reports-generation" class="btn btn-primary" name="action"
                    value="generate_report">Generate Report</button>
			</div>
		</div>
	</div>
</div>