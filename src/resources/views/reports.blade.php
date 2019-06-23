@extends('layouts.tabler')
@section('body_content_header_extras')

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
	                    <a v-bind:href="'{{ route('finance-reports') }}/' + config.id" class="btn btn-success btn-sm">View Report</a>
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

	</div>

</div>


@endsection
@section('body_js')
    <script type="text/javascript">
        new Vue({
            el: '#accounting-reports',
            data: {
                configurations: {!! json_encode($configurations ?: []) !!},
                config: []
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
                createBalanceSheet: function () {

                }
            },
            mounted: function() {
                console.log(this.configurations)
            }
        });
    </script>
@endsection
