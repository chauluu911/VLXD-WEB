@extends('layout.base')

@section('body')
	<div class="header">
	</div>
    <div class="fluid-container bg-white">
    	@section('body-header')
        @show
        @section('body-content')
        @show
    </div>
    <footer class="footer container-fluid">
    </footer>
@endsection
