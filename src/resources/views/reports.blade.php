@extends('layouts.tabler')
@section('head_css')
<link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9" id="accounting-reports">

        <div class="row col-md-12 row-cards row-deck" v-if="configurations.length > 0">
		    <div class="col-md-6" v-for="(config, index) in configurations" :index="index" :config="config" :key="config.id">
		        <div class="card">
		            <div class="card-status bg-blue"></div>
		            <div class="card-header">
		                <h3 class="card-title">@{{ config.display_name }}</h3>
		            </div>
		            <div class="card-body">
		                Configured Report
		            </div>
	                <div class="card-footer">
	                    <a href="#" v-on:click.prevent="viewAccount(index)" class="btn btn-primary btn-sm">View Accounts</a>
	                    <a v-bind:href="'{{ route('finance-reports-configure') }}/' + config.id" class="btn btn-secondary btn-sm">Edit Configuration</a>
	                    <a href="#" v-on:click.prevent="prepareReport(index)" class="btn btn-success btn-sm">Generate Report</a>
	                </div>
		        </div>
		    </div>
		</div>

        <div class="col-md-12" v-if="configurations.length === 0">
            @component('layouts.blocks.tabler.empty-fullpage')
                @slot('title')
                    No Reports Configured
                @endslot
                The Hub makes it easy to create Accounting Reports
                @slot('buttons')
                    <a href="{{ route('finance-reports-configure') }}" class="btn btn-primary btn-sm">Configure A Report</a>
                @endslot
            @endcomponent
        </div>

        @include('modules-finance::modals.reports-config')
        @include('modules-finance::modals.reports-generation')

	</div>

</div>


@endsection
@section('body_js')
    <script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(function() {
            $('.custom-datepicker').datepicker({
                uiLibrary: 'bootstrap4',
                format: 'yyyy-mm-dd'
            });
        });

        new Vue({
            el: '#accounting-reports',
            data: {
                configurations: {!! json_encode($configurations ?: []) !!},
                config: { accounts: { data: [] }, report_date_text: '', report_post_url: '' },
                report_processing: false,
                report_generated: false
            },
            methods: {
                viewAccount: function (index) {
                    let config = typeof this.configurations[index] !== 'undefined' ? this.configurations[index] : null;
                    if (config === null) {
                        return;
                    }
                    this.config = config;
                    $('#reports-config-modal').modal('show');
                },
                getReportDateText: function(report_name) {
                    if (report_name=="balance_sheet") {
                        return "Year Ending"
                    } else if (report_name=="income_statement") {
                        return "As At "
                    }
                },
                getReportPostUrl: function(report_name) {
                    if (report_name=="balance_sheet") {
                        return 'https://api.dorcas.ng/finance/reports/balance_sheet';
                    } else if (report_name=="income_statement") {
                        return 'https://api.dorcas.ng/finance/reports/income_statement';
                    }
                },
                prepareReport: function (index) {
                    let config = typeof this.configurations[index] !== 'undefined' ? this.configurations[index] : null;
                    if (config === null) {
                        return;
                    }
                    this.config = config;
                    this.config.report_date_text = this.getReportDateText(config.report_name);
                    this.config.report_post_url = this.getReportPostUrl(config.report_name);
                    $('#reports-generation-modal').modal('show');
                },
                generateReport: function () {
                    let context = this;
                    this.report_processing =  true;
                    let postConfig = {
                        headers: { 'Authorization': "Bearer " + "{{ !empty($reportToken) ? $reportToken : '' }}", }
                    };
                    var postBody = {
                        report_id: this.config.id,
                        report_date: $('#report_date').val()
                    }
                    //console.log(postBody)
                    axios.post(this.config.report_post_url,
                        postBody,
                        postConfig
                        )
                    .then(function (response) {
                        console.log(response);
                        context.report_generated = true;
                        return swal("Success!", "Report successfully generated", "success");
                    })
                        .catch(function (error) {
                            var message = '';
                            if (error.response) {
                                var e = error.response;
                                message = e.data.message;
                            } else if (error.request) {
                                message = 'The request was made but no response was received';
                            } else {
                                message = error.message;
                            }
                            context.report_processing = false;
                            swal("Error", message, "warning");
                        });
                },
            },
            mounted: function() {
                //console.log(this.configurations)
            }
        });
    </script>
@endsection