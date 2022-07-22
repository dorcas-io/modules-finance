<div class="modal fade" id="entries-import-modal" tabindex="-1" role="dialog" aria-labelledby="entries-import-modalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="entries-import-modalLabel">{{ $importEntriesModal ?? 'Import Account Entries' }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="" id="form-entries-import" method="post" enctype="multipart/form-data">
					{{ csrf_field() }}
					<fieldset class="form-fieldset">
						<div class="form-group col-md-6">
	                        <select id="account" name="account" class="form-control" required>
	                            <optgroup v-for="account in accounts" :key="account.id" v-if="typeof account.sub_accounts !== 'undefined' && account.sub_accounts.data.length > 0"
	                                      v-bind:label="account.display_name">
	                                <option v-for="sub_account in account.sub_accounts.data"
	                                        :key="sub_account.id" v-if="!hide_cash_and_bank || (hide_cash_and_bank && sub_account.name !== 'cash' && sub_account.name !== 'bank')"
	                                        v-bind:value="sub_account.id">@{{ sub_account.display_name }} (@{{ sub_account.entry_type.title_case() }})</option>
	                            </optgroup>
	                            <option v-else v-bind:value="account.id">@{{ account.display_name }} (@{{ account.entry_type.title_case() }})</option>

	                            <option v-if="accounts.length === 0 && accounts.length === 1"
	                                    v-bind:value="accounts[0].id">@{{ accounts[0].display_name }} (@{{ accounts[0].entry_type.title_case() }})</option>
	                        </select>
							<label class="form-label" for="account">Account</label>
						</div>
	                    <div class="form-group col-md-6">
	                        <div class="form-label">Select CSV</div>
	                        <div class="custom-file">
	                            <input type="file" name="import_file" id="import_file" accept="text/csv" class="custom-file-input">
	                            <label class="custom-file-label">Choose File</label>
	                        </div>
	                    </div>
					</fieldset>
				</form>
                <p>
                	Feel free to <a href="{{ cdn('samples/finance-entries.csv') }}" class="btn btn-primary btn-sm" target="_blank">Download</a> our <strong>CSV Template</strong>: <em>Add your data and then Upload</em>.
                </p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit" form="form-entries-import" class="btn btn-primary" name="action"
                    value="save_entries">Save Entries</button>
			</div>
		</div>
	</div>
</div>

