@extends('layouts.tabler')

@section('body_content_main')

@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9">
        <div class="row" id="report-configure">
            <div class="col-md-12">
                <form class="col s12" action="" method="post">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">Choose Report to Configure</label>
                            <select name="report" id="report" class="form-control" v-model="configure.report" v-on:change="selectReport($event)" id="library_switch_category" required>
                                <option value="" disabled="">Report to Configure</option>
                                <option v-for="report in available_reports" v-if="available_reports.length>0" :value="report">@{{ report.replace('_',' ').toUpperCase() }} Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" id="report-configure-area" v-if="report_selected">
                        <div class="col-md-12 table-responsive">
                            <table class="table card-table table-vcenter text-nowrap bootstrap-table"
                                   data-unique-id="id"
                                   data-id-field="id"
                                   data-page-list="[10,25,50,100,200,300,500]"
                                   data-sort-class="sortable"
                                   id="report-configure-table">
                                <thead>
                                <tr>
                                    <th>Journal</th>
                                    <th>Choose Account(s) to include in the @{{ report_display_name }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="account in accounts" :key="account.id + 'body_row'" v-if="account.is_visible">
                                        <td><strong>@{{ account.display_name }}</strong></td>
                                        <td>
                                            <div>
                                                <label class="custom-control custom-checkbox custom-control-inline" v-for="i in max_sub_accounts" :key="account.sub_accounts.data[i - 1].id" v-if="typeof account.sub_accounts.data[i - 1] !== 'undefined' && account.sub_accounts.data[i - 1].is_visible">
                                                    <input type="checkbox" class="custom-control-input" v-bind:id="'account_box-' + account.sub_accounts.data[i - 1].id" v-bind:value="account.sub_accounts.data[i - 1].id" name="accounts[]" :checked="is_selected(account.sub_accounts.data[i - 1].id)==='yes' ? 'checked' : false">

                                                    <span class="custom-control-label" v-bind:for="'account_box-' + account.sub_accounts.data[i - 1].id">@{{ account.sub_accounts.data[i - 1].display_name }}</span>
                                                </label>

                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group col-md-12 pt-3">
                            <input type="hidden" name="configured_report_title" id="configured_report_title" value="" required>
                            <button class="btn btn-primary btn-block" type="submit" name="action" value="configure">
                                Save @{{ report_display_name }} Configuration
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>

</div>

@endsection

@section('body_js')
    <script type="text/javascript">
        new Vue({
            el: '#report-configure',
            data: {
                accounts: {!! json_encode($accounts) !!},
                configurations: {!! json_encode($configurations ?: []) !!},
                configure: {
                    report: '',
                    accounts: []
                },
                report: {!! json_encode(!empty($report) ? $report : null) !!},
                report_name: '',
                report_display_name: 'report',
                configured_account_ids: [],
                available_reports: []
            },
            computed: {
                max_sub_accounts: function () {
                    var longest = 0;
                    for (var i = 0; i < this.accounts.length; i++) {
                        longest = longest < this.accounts[i].sub_accounts.data.length ? this.accounts[i].sub_accounts.data.length : longest;
                    }
                    return longest;
                },
                chosen_configuration: function () {
                    if (this.configure.report ==  "")  {

                    } else {

                    }
                },
                report_selected: function() {
                    return this.configure.report  !== ''  ? true : false;
                },
                report_display_name_com: {
                    get: function () {
                        return this.report_display_name
                    },
                    set: function (newValue) {
                        this.report_display_name = newValue
                    }
                }
            },
            methods: {
                selectReport: function(event) {
                    var report_index = event.target.selectedIndex;
                    let report_text = event.target[report_index].text
                    let report_name = event.target[report_index].value

                    let chosen_config = this.configurations.find( x => x.report_name == report_name ); //get the config  with selected value

                    //get  accounts under  that config
                    let chosen_accounts = chosen_config.accounts.data

                    this.configure.report = report_name;
                    this.configure.accounts = chosen_accounts.map(function (account) { return account.id; });

                    console.log(this.configure.accounts)

                    this.report_display_name = report_text

                    $("#configured_report_title").val(chosen_config.display_name);
                    //console.log($("#configured_report_title").val())
                    //console.log(this.configure.accounts.find( account  => account.id === 'bfa53b96-4660-11e9-929e-0a0842772036'))

                    //check if there is  an  array intersect => if 
                    //account.id
                },
                is_selected: function (accountId) {
                    //console.log(accountId)
                    //console.log(this.configure.accounts.indexOf(accountId))
                    if ( this.configure.accounts.indexOf(accountId) !== -1) {
                        return "yes"
                    } else {
                        return "no"
                    }
                }
            },
            mounted: function () {
                //console.log(this.configurations)
                if (typeof this.report !== 'undefined' && this.report !== null) {
                    this.configure.report = this.report.report_name;
                    this.configure.accounts = this.report.accounts.data.map(function (account) { return account.id; })

                    let report_text = this.configure.report.replace('_',' ').toUpperCase() + ' Report';
                    this.report_display_name = report_text
                    $("#configured_report_title").val(this.report.display_name);

                    //let chosen_config = this.configurations.find( x => x.report_name == this.configure.report );
                    //let chosen_accounts = chosen_config.accounts.data
                    //this.configure.accounts = chosen_accounts.map(function (account) { return account.id; });
                    //console.log(this.configure.accounts)
                }
                if (typeof this.configurations !== 'undefined') {
                    this.available_reports = this.configurations.map(function (config) { return config.report_name; });
                }
            }
        });
    </script>
@endsection