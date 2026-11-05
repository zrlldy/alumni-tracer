{{-- resources/views/errors/402.blade.php --}}
@extends('errors.layout')

@section('code', '402')
@section('title', 'Payment required')
@section('label', 'Payment required')
@section('status', 'Payment Required')

@section('message')
    A payment is required to access this resource. Contact your administrator for help with access or billing.
@endsection

@section('icon')
    <rect x="3" y="5" width="18" height="14" rx="3" /><path d="M3 10h18M7 15h3" />
@endsection

