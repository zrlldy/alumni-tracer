{{-- resources/views/errors/500.blade.php --}}
@extends('errors.layout')

@section('code', '500')
@section('title', 'Something went wrong')
@section('label', 'Server error')
@section('status', 'Internal Server Error')

@section('message')
    We couldn't complete your request because of a server error. Try again later. If the problem continues, contact your administrator.
@endsection

@section('icon')
    <path d="m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.1l-8-14a2 2 0 0 0-3.4 0Z" /><path d="M12 9v4M12 17h.01" />
@endsection

