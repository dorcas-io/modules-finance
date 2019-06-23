<div class="modal fade" id="entries-add-modal" tabindex="-1" role="dialog" aria-labelledby="entries-add-modalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="entries-add-modalLabel">{{ $addEntryModalTitle or 'Add Account Entry' }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="" id="form-entries-add" method="post">
					{{ csrf_field() }}
					<fieldset class="form-fieldset">
						<div class="row">
							<div class="form-group col-md-4">
		                        <select class="form-control" id="account" name="account" required>
		                            <optgroup v-for="account in filteredAccounts" :key="account.id"
		                                      v-bind:label="account.display_name">
		                                <option v-for="sub_account in account.sub_accounts.data"
		                                        :key="sub_account.id" v-if="!hide_cash_and_bank || (hide_cash_and_bank && sub_account.name !== 'cash' && sub_account.name !== 'bank')"
		                                        v-bind:value="sub_account.id">@{{ sub_account.display_name }} (@{{ sub_account.entry_type.title_case() }})</option>
		                            </optgroup>
		                            <option v-if="filteredAccounts.length === 0 && accounts.length === 1"
		                                    v-bind:value="accounts[0].id">@{{ accounts[0].display_name }} (@{{ accounts[0].entry_type.title_case() }})</option>
		                        </select>
								<label class="form-label" for="account">Account</label>
							</div>
							<div class="form-group col-md-4">
		                        <select class="form-control" id="currency" name="currency" v-model="defaultCurrency" required>
		                            @foreach ($isoCurrencies as $currency)
		                                <option value="{{ $currency['alphabeticCode'] }}">{{ $currency['currency'] }} - {{ $currency['alphabeticCode'] }}</option>
		                            @endforeach
		                        </select>
		                        <label class="form-label" for="currency">Currency</label>
							</div>
							<div class="form-group col-md-4">
		                        <input class="form-control" id="amount" type="number" name="amount" step="0.01" min="0" required="required">
		                        <label class="form-label" for="amount">Amount</label>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-md-8">
		                        <input class="form-control" id="memo" name="memo" maxlength="300" type="text">
		                        <label class="form-label" for="memo">Memo</label>
							</div>
							<div class="form-group col-md-4">
		                        <input type="text" class="custom-datepicker" name="created_at" id="created_at">
		                        <label for="created_at">Transaction Date</label>
							</div>
						</div>
					</fieldset>
                    <input type="hidden" name="source_type" id="source_type" value="manual">
                    <input type="hidden" name="source_info" id="source_info" value="Hub Entry">
                    <input type="hidden" name="double_entry_type" id="double_entry_type" v-model="entry_type">
                    <input type="hidden" name="double_entry_period" id="double_entry_period" v-model="entry_period">
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit"  name="action" value="save_entry" form="form-entries-add" class="btn btn-primary">Save Entry</button>
			</div>
		</div>
	</div>
</div>
