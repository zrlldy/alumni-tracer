{{-- resources/views/errors/404.blade.php --}}
@extends('errors.layout')

@section('code', '404')
@section('title', 'Page not found')
@section('label', 'Missing page')
@section('status', 'Not Found')

@section('message')
    We couldn't find the page you're looking for. Check the address or return home to find what you need.
@endsection

@section('icon')
    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" /><path d="M14 2v6h6M9 13l6 6M15 13l-6 6" />
@endsection

