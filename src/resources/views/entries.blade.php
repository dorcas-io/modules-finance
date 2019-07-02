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
                    <table class="table card-table table-vcenter text-nowrap bootstrap-table"
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
                           v-if="entriesCount > 0"
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
                    @include('modules-finance::modals.entries-add')
                    @include('modules-finance::modals.entries-import')
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

@section('body_js')
<script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
<script>
    app.currentUser = {!! json_encode($dorcasUser) !!};

        $(function() {
            /*$('input[type=checkbox].check-all').on('change', function () {
                var className = $(this).parent('div').first().data('item-class') || '';
                if (className.length > 0) {
                    $('input[type=checkbox].'+className).prop('checked', $(this).prop('checked'));
                }
            });*/
            /*$('.custom-datepicker').pickadate({
                today: 'Today',
                clear: 'Clear',
                close: 'Ok',
                closeOnSelect: true,
                format: 'yyyy-mm-dd',
                container: 'body',
                max: true
            });*/

	        $('.custom-datepicker').datepicker({
	            uiLibrary: 'bootstrap4',
                format: 'yyyy-mm-dd'
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
                ui_configuration: {!! json_encode($UiConfiguration) !!}
            },
            mounted: function () {
                if (typeof this.ui_configuration.currency !== 'undefined') {
                    this.defaultCurrency = this.ui_configuration.currency;
                } else {
                    this.defaultCurrency = 'NGN';
                }
                //console.log(this.ui_configuration)
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
            methods:  {

                onChange: function(event) {
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
                }
            }
        });
        new Vue({
            el: '#entries',
            data: {
                entriesCount: {{ $entriesCount }},
                accounts: {!! json_encode($accounts) !!}
            },
            methods: {
                clickAction: function (event) {
                    //console.log(event.target);
                    /*var target = event.target.tagName.toLowerCase() === 'i' ? event.target.parentNode : event.target;
                    var attrs = Hub.utilities.getElementAttributes(target);
                    // get the attributes
                    var classList = target.classList;
                    if (classList.contains('view')) {
                        return true;
                    } else if (classList.contains('remove')) {
                        this.delete(attrs);
                    }*/

		            let target = event.target;
		            if (!target.hasAttribute('data-action')) {
		                target = target.parentNode.hasAttribute('data-action') ? target.parentNode : target;
		            }
		            //console.log(target, target.getAttribute('data-action'));
		            let action = target.getAttribute('data-action');
		            //let name = target.getAttribute('data-name');
		            let id = target.getAttribute('data-id');
		            //let index = parseInt(target.getAttribute('data-index'), 10);
		            /*if (isNaN(index)) {
		                console.log('Index is not set.');
		                return;
		            }*/
		            //console.log(action)
		            if (action === 'view') {
		                return true;
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
                setFutureEntry: function (type) {
                    vmDropdown.setFutureEntry(type);
                },
                setPresentEntry: function (type) {
                    vmDropdown.setPresentEntry(type);
                }
            },
            mounted: function() {
            	//console.log(this.entriesCount)
                //console.log(this.accounts)
            }
        });


    function processRows(row, index) {
    	//console.log(row)
            row.account_link = '<a href="/mfn/finance-entries?account=' + row.account.data.id + '">' + row.account.data.display_name + '</a>';
            row.created_at = moment(row.created_at).format('DD MMM, YYYY');
            row.buttons = '<a class="btn btn-danger btn-sm remove" data-action="remove" href="#" data-id="'+row.id+'">Delete</a>';
            if (typeof row.account.data !== 'undefined' && row.account.data.name == 'unconfirmed') {
                row.buttons += '<a class="btn btn-warning btn-sm view" data-action="view" href="/mfn/finance-entries/' + row.id + '" >Confirm</a>'
            }
            return row;
    }
</script>
@endsection