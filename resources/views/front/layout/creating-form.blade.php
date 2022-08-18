@php
	$viewData = $viewData ?? base64_encode(
		json_encode([

		])
	);
@endphp

@extends('front.layout.master')

@push('stylesheet')
	<link rel="stylesheet" href="{{ mix('/css/front/creating-form.css') }}" type="text/css"/>
@endpush

@section('body-user')
	<input id="view-data" type="hidden" value="{{ $viewData }}">
	<div
		@hasSection('layout-id') id="@yield('layout-id')" @endif
		@hasSection('layout-class')class="@yield('layout-class')" @endif
	>
		<div class="container py-4">
			<div class="row">
				@hasSection('timeline')
					@yield('timeline')
				@elseif (isset($timeline))
					<div class="col-md-16 order-md-1">
						<ul class="timeline">
							@foreach($timeline as $time)
								<li class="timeline__item @if ($time['isDone']) timeline__item--current @endif @if ($time['isSuccess']) timeline__item--success @endif">
									{!! $time['content'] !!}
								</li>
							@endforeach
						</ul>
					</div>
				@endif
				<@yield('form-tag', 'form') class="col-md-32 bg-white shadow-right-sm">
					<div class="row mb-3">
						<div class="col px-4 pt-4">
							<div class="h4 text-uppercase">
								@yield('form-title')
							</div>
							@yield('form-content')
						</div>
						<div class="col-48 px-4 d-flex @yield('form-footer-class')">
							@yield('form-footer')
						</div>
					</div>
				</@yield('form-tag', 'form')>
			</div>
		</div>

		@yield('form-modal')
	</div>
@endsection