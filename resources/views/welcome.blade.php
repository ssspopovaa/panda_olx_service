@extends('layouts.app')

@section('content')
    <h1>Welcome to OLX Price Watcher</h1>
    <a href="{{ route('subscribe.form') }}" class="btn btn-primary">Subscribe to an Advert</a>
    <a href="{{ route('subscriptions.form') }}" class="btn btn-secondary">View Subscriptions</a>
@endsection
