@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')
	<div class="row">
	    @include('layouts.blocks.tabler.sub-menu')

	    <div class="row col-md-9" id="finance-accounts">
            <div class="row" v-if="accounts.length > 0">
    		    <div class="col-md-12 col-lg-6" v-for="(account, index) in accounts" :key="account.id">
    		        <div class="card">
    		            <div class="card-status bg-indigo"></div>
    		            <div class="card-header">
    		                <h3 class="card-title">@{{ account.display_name }} (@{{ account.entry_type.title_case() }})</h3>
    		            </div>
                        <!-- <div class="card-body">
                            
                        </div> -->
    		            <div class="card-footer">
    		                <a href="#" v-on:click.prevent="edit_account(index)" class="btn btn-indigo btn-sm">Edit Account</a>
                            &nbsp;
    		                <a v-bind:href="'{{ route('finance-accounts') }}/' + account.id" v-if="mode === 'topmost'" class="btn btn-cyan btn-sm">View Sub Accounts</a>
                            &nbsp;
    		                <a v-bind:href="'{{ route('finance-entries') }}?account=' + account.id" class="btn btn-success btn-sm">View Entries</a>
                            &nbsp;
    		                <a href="#" v-on:click.prevent="toggleVisibility(index)" v-bind:class="{'btn-danger': account.is_visible, 'btn-info': !account.is_visible}" class="btn btn-sm">@{{ account.is_visible ? 'Hide (from Reports)' : 'Show (on Reports)' }}</a>
    		            </div>
    		        </div>
    		    </div>
            </div>
            <div class="col-md-12" v-if="accounts.length === 0">
                @component('layouts.blocks.tabler.empty-fullpage')
                    @slot('title')
                        Setup Finance
                    @endslot
                    You have not yet setup Finance, you can use the button below to do that now.
                    @slot('buttons')
                        <a href="#" v-on:click.prevent="installFinance" class="btn btn-primary">
                            Setup Finance
                        </a>
                    @endslot
                @endcomponent
            </div>
		</div>
        @include('modules-finance::modals.accounts-edit')
        @if (!empty($baseAccount))
            @include('modules-finance::modals.accounts-sub')
        @endif
	</div>
@endsection
@section('body_js')
    <script type="text/javascript">
        var vm = new Vue({
            el: '#finance-accounts',
            data: {
                accounts: {!! json_encode($accounts ?: []) !!},
                is_processing: false,
                mode: '{{ empty($mode) ? "topmost" : $mode }}',
                enableCreateSubAccountTrigger: '{{ !empty($mode) && $mode !== "topmost" ? "yes" : "no" }}',
                account_index: ''
            },
            methods: {
                edit_account: function (index) {
                    this.account_index = index;
                    $('#accounts-edit-modal').modal('show');
                },
                editAccount: function (index) {
                    var context = this;
                    var account = this.accounts.length > index ? this.accounts[index] : null;
                    if (account === null) {
                        return;
                    }
                    context.is_processing = true;
                    axios.put("/mfn/finance-accounts/" + account.id, {
                        display_name: account.display_name,
                        entry_type: account.entry_type
                    })
                        .then(function (response) {
                            //console.log(response);
                            context.is_processing = false;
                            context.accounts.splice(index, 1, response.data);
                        })
                        .catch(function (error) {
                            var message = '';
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
                            context.is_processing = false;
                            //Materialize.toast('Error: '+message, 4000);
                            swal("Error", message, "warning");
                        });
                },
                installFinance: function () {
                    this.is_processing = true;
                    var context = this;
                    axios.post("/mfn/finance-install")
                        .then(function (response) {
                            //console.log(response);
                            context.is_processing = false;
                            context.accounts = response.data.filter(function (r) { return typeof r.parent_account === 'undefined' || r.parent_account.data.length === 0; });
                        })
                        .catch(function (error) {
                            var message = '';
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
                            context.is_processing = false;
                            //Materialize.toast('Error: '+message, 4000);
                            swal("Error", message, "warning");
                        });
                },
                toggleVisibility: function (index) {
                    var context = this;
                    var account = this.accounts.length > index ? this.accounts[index] : null;
                    if (account === null) {
                        return;
                    }
                    context.is_processing = true;
                    axios.put("/mfn/finance-accounts/" + account.id, {is_visible: account.is_visible ? 0 : 1})
                        .then(function (response) {
                            //console.log(response);
                            context.is_processing = false;
                            context.accounts.splice(index, 1, response.data);
                        })
                        .catch(function (error) {
                            var message = '';
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
                            context.is_processing = false;
                            //Materialize.toast('Error: '+message, 4000);
                            swal("Error", message, "warning");
                        });
                },
                enableCreateSubAccount: function() {
                    if (this.enableCreateSubAccountTrigger==="no") {
                        $('#create_subaccount').hide()
                    }
                },
                createSubAccount: function () {
                    //console.log('email')
                    $('#accounts-sub-modal').modal('show');
                }
            },
            mounted: function() {
                this.enableCreateSubAccount();
                //console.log(this.accounts)
            },
        });
        new Vue({
            el: '#sub-menu-action',
            data: {

            },
            methods: {
                createSubAccount: function () {
                    vm.createSubAccount();
                }
            }
        })

    </script>
@endsection