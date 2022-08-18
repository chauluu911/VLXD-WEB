@extends('front.layout.master')

@section('title', 'Giới thiệu')

@section('body-class', 'bg-white')

@section('body-user')
	<div class="container py-5">
		<div class="py-3">
			<h2 class="text-primary3 mb-3">
				{!! $title !!}
			</h2>
			<hr/>
			<div class="ck-edt-content mt-4">
				{!! $content !!}
			</div>
		</div>
	</div>
@endsection