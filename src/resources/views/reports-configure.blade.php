@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
    
@endsection
@section('body_js')
    
@endsection








@extends('layouts.app')
@section('body_main_content_body')
    @include('blocks.ui-response-alert')
    <div class="container">
        <div class="row section" id="configure-reports">
            <div class="col s12">
                <form class="col s12" action="" method="post">
                    {{ csrf_field() }}
                    <div class="row mb-4">
                        <div class="col s12 m12">
                            <table>
                                <tbody>
                                <tr v-for="account in accounts" :key="account.id + 'body_row'" v-if="account.is_visible">
                                    <td><strong>@{{ account.display_name }}</strong></td>
                                    <td v-for="i in max_sub_accounts" :key="account.sub_accounts.data[i - 1].id"
                                        v-if="typeof account.sub_accounts.data[i - 1] !== 'undefined' && account.sub_accounts.data[i - 1].is_visible">
                                        <input type="checkbox" class="filled-in" v-bind:id="'account_box-' + account.sub_accounts.data[i - 1].id"
                                               v-model="configure.accounts" v-bind:value="account.sub_accounts.data[i - 1].id" name="accounts[]" />
                                        <label v-bind:for="'account_box-' + account.sub_accounts.data[i - 1].id">@{{ account.sub_accounts.data[i - 1].display_name }}</label>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="input-field col s12 m6">
                            <select name="report" id="report" class="browser-default" v-model="configure.report" required>
                                <option value="" disabled="">Report to Configure</option>
                                <option value="balance_sheet">Balance Sheet Report</option>
                                <option value="income_statement">Income Statement Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <button class="btn cyan waves-effect waves-light left" type="submit" name="action" value="configure">
                                Save Configuration
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('body_js')
    <script type="text/javascript">
        new Vue({
            el: '#configure-reports',
            data: {
                accounts: {!! json_encode($accounts) !!},
                configure: {
                    report: '',
                    accounts: []
                },
                report: {!! json_encode(!empty($report) ? $report : null) !!}
            },
            computed: {
                max_sub_accounts: function () {
                    var longest = 0;
                    for (var i = 0; i < this.accounts.length; i++) {
                        longest = longest < this.accounts[i].sub_accounts.data.length ? this.accounts[i].sub_accounts.data.length : longest;
                    }
                    return longest;
                }
            },
            methods: {
                createBalanceSheet: function () {

                }
            },
            mounted: function () {
                if (typeof this.report !== 'undefined' && this.report !== null) {
                    this.configure.report = this.report.report_name;
                    this.configure.accounts = this.report.accounts.data.map(function (account) { return account.id; });
                }
            }
        });
    </script>
@endsection