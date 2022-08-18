@extends('front.layout.master')

@section('body-user')
	<div id="{{ $id }}">
		<div class="container">
			<div class="row mt-3">
				<div class="col-md-32">
					@section('left-content')
					@show
					@section('bottom-content')
					@show
				</div>
				<div class="col-md-16">
					@section('right-content')
					@show
				</div>
			</div>
		</div>
	</div>
@endsection