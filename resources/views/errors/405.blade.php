{{-- resources/views/errors/405.blade.php --}}
@extends('errors.layout')

@section('code', '405')
@section('title', 'Method not allowed')
@section('label', 'Request not supported')
@section('status', 'Method Not Allowed')

@section('message')
    This page doesn't support the action you tried. Go back to the previous page or return home to continue.
@endsection

@section('icon')
    <circle cx="12" cy="12" r="9" /><path d="m5.6 5.6 12.8 12.8" />
@endsection

