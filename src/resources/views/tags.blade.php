@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection

@section('body_content_main')

@include('layouts.blocks.tabler.alert')

<div class="row">

	@include('layouts.blocks.tabler.sub-menu')
	<div class="col-md-9">
		<div class="row">

			<div class="col-md-6 col-lg-4">
				<div class="card">
					<div class="card-status bg-blue"></div>
					<div class="card-header">
						<h3 class="card-title">Report Tags</h3>
						<div class="card-options">
							<a href="{{ route('finance-reports-tags') }}" class="btn btn-primary btn-sm">Manage</a>
						</div>
					</div>
					<div class="card-body">
						Manage the <em>account labelling system</em> are used in your <strong>Reports</strong>
					</div>
				</div>
			</div>
			
		</div>
	
	</div>

</div>

@endsection
@section('body_js')
    
@endsection
