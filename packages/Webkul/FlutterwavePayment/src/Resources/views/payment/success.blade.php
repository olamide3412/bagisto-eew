@extends('shop::layouts.master')
<!--NOT Active-->
@section('page_title')
    Order Successful
@endsection

@section('content')
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow-sm">
        <h1 class="text-success mb-3">🎉 Payment Successful!</h1>
        <p>Thank you for your purchase, {{ $order->customer_first_name }}.</p>
        <p><strong>Order ID:</strong> {{ $order->increment_id }}</p>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>

        <h4 class="mt-4">Items in Your Order:</h4>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->qty_ordered }}</td>
                        <td>{{ core()->currency($item->price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="mt-3"><strong>Total:</strong> {{ core()->currency($order->grand_total) }}</p>

        <a href="{{ route('shop.home.index') }}" class="btn btn-primary mt-3">Continue Shopping</a>
    </div>
</div>
@endsection
