@extends('layout.master')

@prepend('pre-stylesheet')
    <link rel="stylesheet" href="{{ mix('/css/back/core.css') }}" type="text/css"/>
@endprepend

@prepend('stylesheet')
	<link rel="stylesheet" href="{{ mix('/css/back/app.css') }}" type="text/css"/>
@endprepend
