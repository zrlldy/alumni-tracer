{{-- resources/views/errors/401.blade.php --}}
@extends('errors.layout')

@section('code', '401')
@section('title', 'Sign in required')
@section('label', 'Authentication required')
@section('status', 'Unauthorized')

@section('message')
    You need to sign in to view this page. Return home and sign in with your account to continue.
@endsection

@section('icon')
    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" /><path d="m10 8 4 4-4 4M14 12H3" />
@endsection

