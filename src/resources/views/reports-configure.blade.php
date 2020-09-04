@extends('layouts.tabler')

@section('body_content_main')

@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9">
        <div class="row" id="report-configure">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label class="form-label">Choose Report to Configure</label>
                        <select name="report" id="report" class="form-control" v-model="configure.report" v-on:change="selectReport($event)" id="library_switch_category" required>
                            <option value="" disabled="">Report to Configure</option>
                            <option v-for="report in available_reports" v-if="available_reports.length>0" :value="report">@{{ report.replace('_',' ').toUpperCase() }} Report</option>
                        </select>
                    </div>
                    <div class="card">
                        <div class="card-status bg-green"></div>
                        <div class="card-header">
                            <h3 class="card-title">Labels</h3>
                        </div>
                        <div class="card-body">
                            <div class="tag" v-for="reportLabel in reportLabelSelected" :key="reportLabel.title">
                                @{{ reportLabel.title }}
                                <!-- <a href="#" v-on:click.prevent="deleteReportLabel(reportLabel)" class="tag-addon tag-danger">
                                    <i class="fe fe-trash-2" data-ignore-click="true"></i>
                                </a> -->
                            </div>
                        </div>
                    </div>

                    <div class="card" v-if="reportLoading">
                        <div class="card-body p-3 text-center">
                            <!-- <div class="text-right text-green">
                                6%
                                <i class="fe fe-chevron-up"></i>
                            </div>-->
                            <!-- <div class="h1 m-0">43</div> -->
                            <!-- <div class="spinner-grow" role="status">
                              <span class="sr-only">Loading...</span>
                            </div> -->
                            <div class="spinner-border" role="status">
                              <span class="sr-only">Loading...</span>
                            </div>
                            <div class="text-muted mb-4">Loading Accounts...</div>
                        </div>
                    </div>

                </div>
                <form class="col s12" action="" method="post" v-if="!reportLoading">
                    {{ csrf_field() }}
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
                                    <th>Labels</th>
                                    <th>Choose Account(s) to include in <em>each label</em> of the @{{ report_display_name }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="reportLabel in reportLabelSelected">
                                        <td><strong>@{{ reportLabel.title }}</strong></td>
                                        <td>                                                                          
                                            <select class="custom-select" v-model="reportLabel.accounts" v-bind:name="'label_box-' + reportLabel.id + '[]'" :multiple="isreportMultiple">
                                              <option disabled="">Select Account(s)</option>
                                              <option v-for="account in accounts_parents" v-if="account.is_visible" v-bind:value="account.id">@{{ account.display_name }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                     <!-- :selected="checkSelected(account.id,reportLabel.accounts) ? 'selected' : false" :select="checkSelected(account.id,reportLabel.accounts) ? 'selected' : false" -->
                                    <!-- <tr v-for="account in accounts" :key="account.id + 'body_row'" v-if="account.is_visible">
                                        <td><strong>@{{ account.display_name }}</strong></td>
                                        <td>
                                            <div>
                                                <label class="custom-control custom-checkbox custom-control-inline" v-for="i in max_sub_accounts" :key="account.sub_accounts.data[i - 1].id" v-if="typeof account.sub_accounts.data[i - 1] !== 'undefined' && account.sub_accounts.data[i - 1].is_visible">
                                                    <input type="checkbox" class="custom-control-input" v-bind:id="'account_box-' + account.sub_accounts.data[i - 1].id" v-bind:value="account.sub_accounts.data[i - 1].id" name="accounts[]" :checked="is_selected(account.sub_accounts.data[i - 1].id)==='yes' ? true : false"  :data-checked="is_selected(account.sub_accounts.data[i - 1].id)==='yes' ? true : false">

                                                    <span class="custom-control-label" v-bind:for="'account_box-' + account.sub_accounts.data[i - 1].id">@{{ account.sub_accounts.data[i - 1].display_name }}  @{{ is_selected(account.sub_accounts.data[i - 1].id)==='yes' ? '&#10003;' : '' }}</span>
                                                </label>

                                            </div>
                                        </td>
                                    </tr> -->
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group col-md-12 pt-3">
                            <input type="hidden" name="report" id="report" v-model="configure.report" required>
                            <input type="hidden" name="configured_report_title" id="configured_report_title" :value="report_display_name" required>
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
                accounts_children: {!! json_encode($accounts_children) !!},
                accounts_parents: {!! json_encode($accounts_parents) !!},
                configurations: {!! json_encode($configurations ?: []) !!},
                configure: {
                    report: '',
                    accounts: []
                },
                report: {!! json_encode(!empty($report) ? $report : null) !!},
                report_name: '',
                report_display_name: 'report',
                configured_account_ids: [],
                available_reports: [],
                reportLabels: {!! json_encode($reportLabels ?: []) !!},
                reportLabel: '',
                reportLabelSelected: { id: '', type:'', accounts:[], title: '' },
                reportLoading: false,
                bindingSelect: 'bfa710a6-4660-11e9-99fb-0a0842772036'
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
                isreportMultiple: function() {
                    return this.configure.report  === 'balance_sheet'  ? true : false;
                }
            },
            methods: {
                selectReport: function(event) {
                    var report_index = event.target.selectedIndex;
                    let report_text = event.target[report_index].text
                    let report_name = event.target[report_index].value

                    this.reportLabelSelected = this.reportLabels[report_name];
                    //console.log(this.reportLabelSelected)

                    this.configure.report = report_name;
                    this.report_display_name = report_text

                    if (this.configurations.length>0) {
                        let chosen_config = this.configurations.find( x => x.report_name == report_name );
                        let chosen_accounts = chosen_config.accounts.data
                        this.configure.accounts = chosen_accounts.map(function (account) { return account.id; });
                        //console.log(this.configure.accounts)

                        //reload page
                        //window.location = '{{ route("welcome-overview")."?fromsetup" }}'
                        //mfn/finance-reports-configure/ea142064-604a-11e9-b659-0a0842772036
                        this.reportLoading = true
                        window.location = '/mfn/finance-reports-configure/'+chosen_config.id;

                        $("#configured_report_title").val(chosen_config.display_name);
                    } else {
                        $("#configured_report_title").val(report_text);
                    }

                },
                account_to_array: function(accounts_string)  {
                    console.log(accounts_string)
                    console.log(typeof(accounts_string))
                    /*if (accounts_string.indexOf(",")<1) {
                        return [accounts_string]
                    } else {
                        return accounts_string.split(",");
                    }*/
                },
                checkSelected: function(accountID, reportLabelAccounts)  {
                    //console.log(reportLabelAccounts);
                    if (reportLabelAccounts.indexOf(accountID) === -1) {
                        return false
                    } else {
                        return true
                    }
                },
                is_selected: function (accountId) {
                    //console.log(accountId)
                    //console.log(this.configure.accounts.indexOf(accountId))
                    if ( this.configure.accounts.indexOf(accountId) !== -1) {
                        return "yes"
                    } else {
                        return "no"
                    }
                },
                addReportLabel: function () {
                    var context = this;
                    Swal.fire({
                            title: "New Variant Type",
                            text: "Enter the name for the variant Type:",
                            input: "text",
                            showCancelButton: true,
                            animation: "slide-from-top",
                            showLoaderOnConfirm: true,
                            inputPlaceholder: "Custom Variant Type",
                            inputValidator: (value) => {
                                if (!value) {
                                    return 'You need to write something!'
                                }
                            },
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Add Variant Type",
                            showLoaderOnConfirm: true,
                            preConfirm: (variant_type) => {
                                //console.log(variant_name);
                            return axios.post("/msl/sales-variant-type", {
                                    variant_type: variant_type
                                }).then(function (response) {
                                    //vm.fields.push({id: response.data.id, name: response.data.name});
                                    context.variantTypes = response.data;
                                    return swal("Success", "The variant type was successfully created.", "success");
                                })
                                    .catch(function (error) {
                                        var message = '';
                                        if (error.response) {
                                            // The request was made and the server responded with a status code
                                            // that falls out of the range of 2xx
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
                                        return swal("Oops!", message, "warning");
                                    });
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        });
                },
                deleteReportLabel: function (variant_name) {
                    var context = this;
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You are about to delete the \"" + variant_name + "\" Variant Type",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, delete it!",
                        showLoaderOnConfirm: true,
                        preConfirm: (delete_custom) => {
                        return axios.post("/msl/sales-variant-type-remove/", {
                            variant_name: variant_name
                        })
                            .then(function (response) {
                                //console.log(response);
                                context.variantTypes = response.data;
                                return swal("Deleted!", "The variant type was successfully deleted.", "success");
                            })
                            .catch(function (error) {
                                var message = '';
                                if (error.response) {
                                    // The request was made and the server responded with a status code
                                    // that falls out of the range of 2xx
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
                }

            },
            mounted: function () {
                //console.log(this.reportLabels)
                //console.log(this.configurations)
                //console.log(this.accounts)
                //console.log(this.accounts_parents)
                //console.log(this.accounts_children)
                if (typeof this.report !== 'undefined' && this.report !== null) {
                    this.configure.report = this.report.report_name;
                    this.configure.accounts = this.report.accounts.data.map(function (account) { return account.id; })

                    this.reportLabelSelected = this.reportLabels[this.configure.report];
                    //console.log(this.reportLabelSelected)

                    /*console.log(this.report.report_name)
                    let mchosen_config = this.configurations.find( x => x.report_name == this.report.report_name );
                    let mchosen_accounts = mchosen_config.accounts.data
                    this.configure.accounts = mchosen_accounts.map(function (account) { return account.id; });*/

                    let report_text = this.configure.report.replace('_',' ').toUpperCase() + ' Report';
                    let markSign = '&#10003;';
                    this.report_display_name = report_text; // + " (Previously selected accounts are marked " + markSign + ")";
                    $("#configured_report_title").val(this.report.display_name);
                }
                //console.log(this.configurations);
                if (this.available_reports.length==0)  {
                    this.available_reports = ['balance_sheet','income_statement'];
                } else if (this.available_reports.length > 0 && typeof this.configurations !== 'undefined') {
                    this.available_reports = this.configurations.map(function (config) { return config.report_name; });
                }
            }
        });
    </script>
@endsection