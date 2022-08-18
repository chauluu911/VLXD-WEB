@extends('front.layout.master')

@push('stylesheet')
	<link rel="stylesheet" href="{{ mix('/css/front/chat.css') }}" type="text/css"/>
@endpush

@section('title')
    Tin nhắn
@endsection

@section('main-id', 'chat')

@section('body-user')
	<div class="container my-3 div-chat">
        <div class="font-weight-bold" style="font-size: 20px;">TIN NHẮN</div>
		<chat class="mt-3 mb-5" user-id="{{$id}}" first-message="{{$firstMessage}}"></chat>
	</div>
@endsection

@push('body-scripts')
	<script src="{{ mix('/js/front/chat/chat.js') }}"></script>
@endpush
