<div class="modal fade" id="entries-confirm-modal" tabindex="-1" role="dialog" aria-labelledby="entries-confirm-modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" v-if="entry!==''">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="entries-confirm-modalLabel">@{{ 'Confirm Transaction: ' + entry.memo }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="form-entries-confirm" method="post" v-on:submit.prevent="confirmEntry">
                    {{ csrf_field() }}
                    <fieldset class="form-fieldset">

                        <div class="row">
                            <p class="col-md-4"><strong>Date</strong>: @{{ entryDate }}</p>
                            <p class="col-md-4"><strong>Memo</strong>: @{{ entry.memo }}</p>
                            <p class="col-md-4"><strong>Account</strong>: @{{ entry.account.data.display_name }}</p>
                            <p class="col-md-4"><strong>Amount</strong>: @{{ entry.currency + ' ' + entry.amount.formatted }}</p>
                            <p class="col-md-4"><strong>Type</strong>: @{{ entry.entry_type.title_case() }}</p>
                            <p class="col-md-4"><strong>Source</strong>: @{{ entry.source_type.title_case() + ' / ' + entry.source_info }}</p>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <select class="form-control" id="account" name="account" v-model="confirmed_account" required>
                                    <option value="" disabled>Select Confirmation Account</option>
                                    <optgroup v-for="account in accounts_all" :key="account.id" v-bind:label="account.display_name + ' (' + account.entry_type.title_case() + ')'"
                                              v-if="typeof account.sub_accounts !== 'undefined' && account.sub_accounts.data.length > 0 && account.entry_type === entry.entry_type">
                                        <option v-for="sub_account in account.sub_accounts.data"
                                                :key="sub_account.id" v-bind:value="sub_account.id">@{{ sub_account.display_name }}</option>
                                    </optgroup>
                                </select>
                                <label class="form-label" for="account">Confirmation Account</label>
                            </div>
                        </div>

                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" name="action" value="save_entry" form="form-entries-confirm" class="btn btn-primary" v-bind:class="{'btn-loading': entry_processing}">Confirm Entry</button>
            </div>
        </div>
    </div>
</div>