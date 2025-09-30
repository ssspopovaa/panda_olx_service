@extends('layouts.app')

@section('content')
    <h1>Your Subscriptions</h1>
    @if (!isset($subscriptions))
        <form method="POST" action="{{ route('subscriptions.list') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Enter Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">View Subscriptions</button>
        </form>
    @else
        @if (empty($subscriptions))
            <p>No subscriptions found.</p>
        @else
            <table class="table">
                <thead>
                <tr>
                    <th>Advert URL</th>
                    <th>Current Price</th>
                    <th>Price History</th>
                    <th>Verified</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($subscriptions as $sub)
                    <tr>
                        <td>{{ $sub['advert']['url'] ?? 'N/A' }}</td>
                        <td>{{ $sub['advert']['last_price'] ? $sub['advert']['last_price'] . ' ' . $sub['advert']['currency'] : 'N/A' }}</td>
                        <td>
                            @if (!empty($sub['price_history']))
                                <ul>
                                    @foreach ($sub['price_history'] as $price)
                                        <li>{{ $price['price'] }} {{ $price['currency'] }} ({{ $price['changed_at'] }})</li>
                                    @endforeach
                                </ul>
                            @else
                                No price changes recorded.
                            @endif
                        </td>
                        <td>{{ $sub['verified'] ? 'Yes' : 'No' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
        <a href="{{ route('subscriptions.form') }}" class="btn btn-secondary">Back</a>
    @endif
@endsection
