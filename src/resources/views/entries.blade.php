@extends('layouts.tabler')
@section('head_css')
<link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('body_content_main')

@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9">
        <div class="row row-cards row-deck" id="entries">
            <div class="col-sm-12">
                <div class="table-responsive">
                    <table class="table card-table table-vcenter text-nowrap bootstrap-table" v-if="entriesCount > 0"
                           data-pagination="true"
                           data-side-pagination="server"
                           data-show-refresh="true"
                           data-unique-id="id"
                           data-id-field="id"
                           data-row-attributes="processRows"
                           data-url="{{ route('finance-entries-search') . '?' . http_build_query($args) }}"
                           data-page-list="[10,25,50,100,200,300,500]"
                           data-sort-class="sortable"
                           data-search-on-enter-key="true"
                           id="entries-table" v-on:click="clickAction($event)">
                        <thead>
                        <tr>
		                    <th data-field="account_link">Account</th>
		                    <th data-field="entry_type">Type</th>
		                    <th data-field="currency">Currency</th>
		                    <th data-field="amount.formatted">Amount</th>
		                    <th data-field="source_info">Source</th>
		                    <th data-field="memo" data-width="25%">Memo</th>
		                    <th data-field="created_at" data-width="10%">Added On</th>
		                    <th data-field="buttons">&nbsp;</th>
                            <th data-field="generate_invoice">&nbsp;</th>

                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

                    <div class="col s12" v-if="entriesCount === 0">
                        @component('layouts.blocks.tabler.empty-fullpage')
                            @slot('title')
                                No Account Entries
                            @endslot
                            Add a new account entry to your records.
                                <a href="#" v-on:click.prevent="setPresentEntry('debit')"
                           v-if="(accounts.length === 1 && accounts[0].entry_type === 'debit') || accounts.length > 1" class="btn btn-danger">Real Debit/Expense</a>
                                &nbsp;
                                <a href="#" v-on:click.prevent="setFutureEntry('debit')"
                           v-if="(accounts.length === 1 && accounts[0].entry_type === 'debit') || accounts.length > 1" class="btn btn-outline-danger">Future Debit/Expense</a>
                                &nbsp;
                                <a href="#" v-on:click.prevent="setPresentEntry('credit')"
                           v-if="(accounts.length === 1 && accounts[0].entry_type === 'credit') || accounts.length > 1" class="btn btn-success">Real Credit/Income</a>
                                &nbsp;
                                <a href="#" v-on:click.prevent="setFutureEntry('credit')"
                           v-if="(accounts.length === 1 && accounts[0].entry_type === 'credit') || accounts.length > 1" class="btn btn-outline-success">Future Credit/Income</a>
                            @slot('buttons')
                            @endslot
                        @endcomponent
                    </div>

                </div>
            </div>
            @include('modules-finance::modals.entries-confirm')
        </div>

    </div>
    @include('modules-finance::modals.entries-add')
    @include('modules-finance::modals.entries-add-unconfirmed')
    @include('modules-finance::modals.entries-import')

</div>

@endsection

@section('body_js')
<script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
<script>
    app.currentUser = {!! json_encode($dorcasUser) !!};

        $(function() {

	        $('.custom-datepicker').datepicker({
	            uiLibrary: 'bootstrap4',
                format: 'yyyy-mm-dd'
                /*close: function (e) {
                    vm.due_date = e.target.value;
                }*/
	        });

            /*$('#entries-add-modal').modal({
                complete: function () {
                    vmModal.allowedAccounts = [];
                }
            });*/
        });
        var vmModal = new Vue({
            el: '#entries-add-modal',
            data: {
                allowedAccounts: [],
                accounts: {!! json_encode($accounts) !!},
                hide_cash_and_bank: false,
                entry_type: '',
                entry_period: 'present',
                defaultCurrency: '',
                ui_configuration: {!! json_encode($UiConfiguration) !!},
                defamoint: 10
            },
            mounted: function () {
                if (typeof this.ui_configuration.currency !== 'undefined') {
                    this.defaultCurrency = this.ui_configuration.currency;
                } else {
                    this.defaultCurrency = 'NGN';
                }
            },
            computed: {
                filteredAccounts: function () {
                    var context = this;
                    if (this.accounts.length === 1 && (typeof this.accounts[0].sub_accounts !== 'undefined' || this.accounts[0].sub_accounts.data.length === 0 )) {
                        return [];
                    }
                    return this.accounts.filter(function (account) {
                        return typeof account.sub_accounts !== 'undefined' && account.sub_accounts.data.length > 0 &&
                            (context.allowedAccounts.length === 0 || (context.allowedAccounts.length > 0 && context.allowedAccounts.indexOf(account.id) !== -1));
                    });
                }
            },
            methods: {
                currencyChange: function(event) {
                    console.log(event.target.value)
                }
            }
        });

        var vmImportModal = new Vue({
            el: '#entries-import-modal',
            data: {
                allowedAccounts: [],
                accounts: {!! json_encode($accounts) !!},
                hide_cash_and_bank: false,
                entry_type: '',
                entry_period: 'present',
            },
            mounted: function () {

            },
            computed: {
                filteredAccounts: function () {
                    var context = this;
                    if (this.accounts.length === 1 && (typeof this.accounts[0].sub_accounts !== 'undefined' || this.accounts[0].sub_accounts.data.length === 0 )) {
                        return [];
                    }
                    return this.accounts.filter(function (account) {
                        return typeof account.sub_accounts !== 'undefined' && account.sub_accounts.data.length > 0 &&
                            (context.allowedAccounts.length === 0 || (context.allowedAccounts.length > 0 && context.allowedAccounts.indexOf(account.id) !== -1));
                    });
                }
            }
        });
        var vmDropdown = new Vue({
            el: '#sub-menu-action',
            data: {
                accounts: vmModal.accounts
            },
            methods: {
                filterAccounts: function (type) {
                    console.log('Filtering for: ' + type);
                    var filtered = vmModal.accounts.filter(function (account) {
                        return account.entry_type === type;
                    });
                    vmModal.allowedAccounts = filtered.map(function (account) {
                        return account.id;
                    });
                    vmModal.hide_cash_and_bank = type === 'debit';
                    vmModal.entry_type = type;
                },
                setFutureEntry: function (type) {
                    type = type === 'credit' || type === 'debit' ? type : '';
                    if (type.length === 0) {
                        return;
                    }
                    vmModal.entry_period = 'future';
                    this.filterAccounts(type);
                    $('#entries-add-modal').modal('show');
                },
                setPresentEntry: function (type) {
                    type = type === 'credit' || type === 'debit' ? type : '';
                    if (type.length === 0) {
                        return;
                    }
                    vmModal.entry_period = 'present';
                    this.filterAccounts(type);
                    $('#entries-add-modal').modal('show');
                },
                setDefaultEntry: function () {
                    vmModal.allowedAccounts = vmModal.accounts.map(function (account) {
                        return account.id;
                    });
                    vmModal.hide_cash_and_bank = false;
                    vmModal.entry_type = '';
                    vmModal.entry_period = 'present';
                    $('#entries-add-modal').modal('show');
                }
            }
        });
        new Vue({
            el: '#entries',
            data: {
                entriesCount: {{ $entriesCount }},
                accounts: {!! json_encode($accounts) !!},
                accounts_all: {!! json_encode($accounts_all) !!},
                entries: {!! json_encode($entries) !!},
                entry: '',
                confirmed_account: '',
                entry_processing: false
            },
            computed: {
                entryDate: function () {
                    return this.entry !== '' ? moment(this.entry.created_at).format('DD MMM, YYYY') : '' ;
                }

            },
            methods: {
                clickAction: function (event) {
		            let target = event.target;
		            if (!target.hasAttribute('data-action')) {
		                target = target.parentNode.hasAttribute('data-action') ? target.parentNode : target;
		            }
		            let action = target.getAttribute('data-action');
		            let id = target.getAttribute('data-id');
                    let index = target.getAttribute('data-index');
                    //console.log(action)
		            if (action === 'view_entry') {
		                this.viewEntry(index);
		            } else if (action === 'remove') {
		                this.delete(id);
		            } else {
		                return true;
		            }
                },
                delete: function (id) {
                    //console.log(id);
                    //var id = attributes['data-id'] || null;
                    if (id === null) {
                        return false;
                    }
                    var context = this;
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You are about to delete this accounting entry from the records.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, delete it!",
		                showLoaderOnConfirm: true,
		                preConfirm: (delete_entry) => {
		                    return axios.delete("/mfn/finance-entries/" + id)
		                        .then(function (response) {
		                            //console.log(response);
		                            $('#entries-table').bootstrapTable('removeByUniqueId', response.data.id);
		                            return swal("Deleted!", "The entry was successfully deleted.", "success");
		                        })
		                        .catch(function (error) {
		                            var message = '';
		                            console.log(error);
		                            if (error.response) {
		                                // The request was made and the server responded with a status code
		                                // that falls out of the range of 2xx
		                                //var e = error.response.data.errors[0];
		                                //message = e.title;
			                            var e = error.response;
			                            message = e.data.message;
		                            } else if (error.request) {
		                                // The request was made but no response was received
		                                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
		                                // http.ClientRequest in node.js
		                                message = 'The request was made but no response was received';
		                            } else {
		                                // Something happened in setting up the request that triggered an Error
		                                message = error.message;
		                            }
		                            return swal("Delete Failed", message, "warning");
		                        });
		                },
		                allowOutsideClick: () => !Swal.isLoading()
		            });


                },
                viewEntry:  function (index)  {
                    var entry = typeof this.entries[index] !== 'undefined' ? this.entries[index] : null;
                    if (entry === null) {
                        return false;
                    }
                    //console.log(entry);
                    this.entry = entry;
                    $('#entries-confirm-modal').modal('show');
                },
                confirmEntry: function () {
                    this.entry_processing = true;
                    var context = this;
                    axios.put("/mfn/finance-entries/" + context.entry.id, {account: context.confirmed_account})
                        .then(function (response) {
                            //console.log(response);
                            context.entry_processing = false;
                            context.entry = response.data;
                            //Materialize.toast('The entry was successfully confirmed.', 4000);
                            $('#entries-table').bootstrapTable('removeByUniqueId', response.data.id);
                            $('#entries-confirm-modal').modal('hide');
                            return swal("Success!", "The entry was successfully confirmed.", "success");
                        })
                        .catch(function (error) {
                            var message = '';
                            //console.log(error);
                            context.is_processing = false;
                            if (error.response) {
                                // The request was made and the server responded with a status code
                                // that falls out of the range of 2xx
                                /*var e = error.response.data.errors[0];
                                message = e.title;*/
                                var e = error.response;
                                message = e.data.message;
                            } else if (error.request) {
                                // The request was made but no response was received
                                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                                // http.ClientRequest in node.js
                                message = 'The request was made but no response was received';
                            } else {
                                // Something happened in setting up the request that triggered an Error
                                message = error.message;
                            }
                            //Materialize.toast('Confirmation failed: ' + message, 4000);
                            return swal("Confirmation Failed", message, "warning");
                        });
                },
                setFutureEntry: function (type) {
                    vmDropdown.setFutureEntry(type);
                },
                setPresentEntry: function (type) {
                    vmDropdown.setPresentEntry(type);
                }
            },
            mounted: function() {
            	//console.log(this.entriesCount)
                //console.log(this.accounts_all)
            }
        });

    function processRows(row, index) {
    	console.log(row.id)
            row.account_link = '<a href="/mfn/finance-entries?account=' + row.account.data.id + '">' + row.account.data.display_name + '</a>';
            row.created_at = moment(row.created_at).format('DD MMM, YYYY');
            row.buttons = '<a class="btn btn-danger btn-sm remove" data-action="remove" href="#" data-id="'+row.id+'" data-index="'+index+'">Delete</a>';
            row.generate_invoice = '<a class="btn btn-primary btn-sm generate_invoice" data-action="generate_incoice" href="/mfn/finance-entries/generate-invoice/' + row.id + '"  data-id="'+row.id+'" data-index="'+index+'">Generate Invoice</a>';
            if (typeof row.account.data !== 'undefined' && row.account.data.name == 'unconfirmed') {
                row.buttons += '<a class="btn btn-warning btn-sm view" data-id="'+row.id+'" data-index="'+index+'" data-action="view_entry" href="#">Confirm</a>'
            }
            return row;
    }
</script>
@endsection