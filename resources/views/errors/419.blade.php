{{-- resources/views/errors/419.blade.php --}}
@extends('errors.layout')

@section('code', '419')
@section('title', 'Page expired')
@section('label', 'Session expired')
@section('status', 'Page Expired')

@section('message')
    Your session or form security token has expired. Return home, sign in if needed, then open the form again before submitting.
@endsection

@section('icon')
    <circle cx="12" cy="13" r="8" /><path d="M12 9v4l3 2M9 2h6M12 2v3" />
@endsection

