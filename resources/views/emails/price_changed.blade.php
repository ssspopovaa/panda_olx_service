<p>Price changed for advert ID: {{ $advertId }}</p>
<p>Old price: {{ $old ?? 'N/A' }}</p>
<p>New price: {{ $new }}</p>
<h3>Price History</h3>
@if (!empty($priceHistory))
    <ul>
        @foreach ($priceHistory as $price)
            <li>{{ $price['price'] }} {{ $price['currency'] }} ({{ $price['changed_at'] }})</li>
        @endforeach
    </ul>
@else
    <p>No previous price changes recorded.</p>
@endif
