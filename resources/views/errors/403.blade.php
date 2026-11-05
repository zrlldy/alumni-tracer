{{-- resources/views/errors/403.blade.php --}}
@extends('errors.layout')

@section('code', '403')
@section('title', 'Access denied')
@section('label', 'Restricted access')
@section('status', 'Forbidden')

@section('message')
    You don't have permission to view this page.
    If you think this is a mistake, contact your administrator.
@endsection

@section('icon')
    <rect x="5" y="10" width="14" height="11" rx="3" />
    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
    <path d="M12 14v3" />
@endsection
