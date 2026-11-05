{{-- resources/views/errors/429.blade.php --}}
@extends('errors.layout')

@section('code', '429')
@section('title', 'Too many requests')
@section('label', 'Please slow down')
@section('status', 'Too Many Requests')

@section('message')
    You've made too many requests in a short time. Wait a moment before trying again.
@endsection

@section('icon')
    <path d="M6 3h12M6 21h12M7 3v3a5 5 0 0 0 2 4l3 2-3 2a5 5 0 0 0-2 4v3M17 3v3a5 5 0 0 1-2 4l-3 2 3 2a5 5 0 0 1 2 4v3" />
@endsection

