<div class="modal fade" id="reports-balance-sheet-modal" tabindex="-1" role="dialog" aria-labelledby="reports-balance-sheetLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="reports-balance-sheetLabel">Balance Sheet Report</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="" id="form-reports-balance-sheet" method="post">
					{{ csrf_field() }}
					<fieldset class="form-fieldset">
						<div class="form-group">
							<label class="form-label" for="team_name">Team Name</label><!--v-bind:class="{'active': team.name.length > 0}"-->
							<input class="form-control" id="team_name" type="text" name="name" maxlength="80" v-model="team.name">
						</div>
						<div class="form-group">
							<label class="form-label" for="description">Description (Optional)</label><!-- v-bind:class="{'active': team.description.length > 0}"-->
							<textarea class="form-control" id="description" name="description" v-model="team.description"></textarea>
						</div>
					</fieldset>
					<input type="hidden" name="team_id" id="team_id" v-model="team.id" v-if="showTeamId" />
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit" name="create_report" form="form-reports-balance-sheet" class="btn btn-primary" value="1">Create Report</button>
			</div>
		</div>
	</div>
</div>