@extends('layouts.app')

@section('content')
    <h1>Subscribe to OLX Advert</h1>
    <form method="POST" action="{{ route('subscribe') }}">
        @csrf
        <div class="mb-3">
            <label for="url" class="form-label">OLX URL</label>
            <input type="url" class="form-control" id="url" name="url" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Subscribe</button>
    </form>
@endsection
