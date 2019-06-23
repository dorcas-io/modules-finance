@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
    
@endsection
@section('body_js')
    
@endsection






@extends('layouts.app')
@section('body_main_content_body')
    @include('blocks.page-messages')
    @include('blocks.ui-response-alert')
    <div class="container">
        <div class="row section" id="finance-entry-confirm-box">
            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">@{{ entry.source_type.title_case() }}</span>
                        <p>
                            <strong>Amount: </strong>@{{ entry.currency + ' ' + entry.amount.formatted }}<br>
                            <strong>Type: </strong>@{{ entry.entry_type.title_case() }}<br>
                            <strong>Source Info: </strong>@{{ entry.source_info }}<br>
                            <strong>Memo: </strong>@{{ entry.memo }}<br>
                            <strong>Account: </strong>@{{ entry.account.data.display_name }}<br>
                        </p>
                    </div>
                    <div class="card-action">
                        <a href="#" class="activator black-text">Confirm Transaction</a>
                    </div>
                    <div class="card-reveal">
                        <span class="card-title grey-text text-darken-4">Confirm Entry<i class="material-icons right">close</i></span>
                        <form method="post" action="" v-on:submit.prevent="confirmEntry">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="input-field col s12 m7">
                                    <select name="account" id="account" v-model="confirmed_account" class="browser-default">
                                        <option value="" disabled>Select Confirmed Account</option>
                                        <optgroup v-for="account in accounts" :key="account.id" v-bind:label="account.display_name + ' (' + account.entry_type.title_case() + ')'"
                                                  v-if="typeof account.sub_accounts !== 'undefined' && account.sub_accounts.data.length > 0 && account.entry_type === entry.entry_type">
                                            <option v-for="sub_account in account.sub_accounts.data"
                                                    :key="sub_account.id" v-bind:value="sub_account.id">@{{ sub_account.display_name }}</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="col s12 m5 mt-2">
                                    <button class="btn waves-effect waves-light" type="submit" name="action"
                                            v-if="!is_processing">Confirm</button>
                                    <div class="progress" v-if="is_processing">
                                        <div class="indeterminate"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('body_js')
    <script type="text/javascript">
        $(function() {
            $('.custom-datepicker').pickadate({
                today: 'Today',
                clear: 'Clear',
                close: 'Ok',
                closeOnSelect: false,
                onClose: function() {
                    console.log('onClose');
                    vm.setup.from_date = this.get('select', 'dd-mmm-yyyy');
                }
            });
        });
        new Vue({
            el: '#finance-entry-confirm-box',
            data: {
                entry: {!! json_encode($entry) !!},
                accounts: {!! json_encode($accounts) !!},
                confirmed_account: '',
                is_processing: false
            },
            methods: {
                confirmEntry: function () {
                    this.is_processing = true;
                    var context = this;
                    axios.put("/xhr/finance/entries/" + context.entry.id, {account: context.confirmed_account})
                        .then(function (response) {
                            console.log(response);
                            context.is_processing = false;
                            context.entry = response.data;
                            Materialize.toast('The entry was successfully confirmed.', 4000);
                        })
                        .catch(function (error) {
                            var message = '';
                            console.log(error);
                            context.is_processing = false;
                            if (error.response) {
                                // The request was made and the server responded with a status code
                                // that falls out of the range of 2xx
                                var e = error.response.data.errors[0];
                                message = e.title;
                            } else if (error.request) {
                                // The request was made but no response was received
                                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                                // http.ClientRequest in node.js
                                message = 'The request was made but no response was received';
                            } else {
                                // Something happened in setting up the request that triggered an Error
                                message = error.message;
                            }
                            Materialize.toast('Confirmation failed: ' + message, 4000);
                        });
                }
            }
        });
    </script>
@endsection