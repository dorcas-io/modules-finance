<div class="modal fade" id="accounts-sub-modal" tabindex="-1" role="dialog" aria-labelledby="accounts-sub-modalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="accounts-sub-modalLabel">New Sub-Account</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="" id="form-accounts-sub" method="post">
					{{ csrf_field() }}
					<fieldset class="form-fieldset">
						<div class="form-group">
	                        <input class="form-control" id="name" name="name" maxlength="80" type="text" required>
	                        <label class="form-label" for="name">Account Name</label>
						</div>
					</fieldset>
                    <input type="hidden" name="parent_account_id" value="{{ $baseAccount->id }}">
                    <input type="hidden" name="entry_type" value="{{ $baseAccount->entry_type }}">
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit" name="save_product" value="1" form="form-accounts-sub" class="btn btn-primary">Add Account</button>
			</div>
		</div>
	</div>
</div>
