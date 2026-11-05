{{-- resources/views/errors/503.blade.php --}}
@extends('errors.layout')

@section('code', '503')
@section('title', 'Service unavailable')
@section('label', 'Temporarily unavailable')
@section('status', 'Service Unavailable')

@section('message')
    The service is temporarily unavailable. Please try again later.
@endsection

@section('icon')
    <rect x="3" y="3" width="18" height="7" rx="2" /><rect x="3" y="14" width="18" height="7" rx="2" /><path d="M7 6.5h.01M7 17.5h.01M12 6.5h5M12 17.5h5" />
@endsection

