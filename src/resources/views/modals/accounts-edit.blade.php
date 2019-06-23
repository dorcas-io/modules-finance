<div class="modal fade" id="accounts-edit-modal" tabindex="-1" role="dialog" aria-labelledby="accounts-edit-modalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="accounts-edit-modalLabel">Edit Account</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="" id="form-accounts-edit" method="post" v-on:submit.prevent="editAccount(account_index)">
					{{ csrf_field() }}
					<fieldset class="form-fieldset">
						<div class="form-group col-md-6">
                            <input class="form-control" id="name" type="text" v-model="accounts[account_index].display_name" name="name" maxlength="80" required>
                            <label class="form-label" for="name">Account Name</label>
						</div>
						<div class="form-group col-md-6">
                            <select class="form-control" name="entry_type" id="entry_type" v-model="accounts[account_index].entry_type">
                                <option value="" disabled>Record Entry Type</option>
                                <option value="credit">Credit</option>
                                <option value="debit">Debit</option>
                            </select>
                            <label class="form-label" for="name">Entry Type</label>
						</div>
					</fieldset>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit" name="save_account" form="form-accounts-edit" class="btn btn-primary" name="action" v-if="!is_processing">Save Changes</button>
			</div>
		</div>
	</div>
</div>