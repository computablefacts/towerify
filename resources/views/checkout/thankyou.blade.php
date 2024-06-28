@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('product.index') }}">All Products</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cart.show') }}">Cart</a></li>
    <li class="breadcrumb-item">Checkout</li>
    <li class="breadcrumb-item">Order Complete</li>

@stop

@section('content')
    <div class="container mt-3">
        <h1>Wonderful {{ $order->getBillpayer()->firstname }}!</h1>
        <hr>
        <div class="alert alert-success">
          Your order has been registered with number <strong>{{ $order->getNumber() }}</strong>.
        </div>

        @if(!empty($paymentRequest->getHtmlSnippet()))
          <h3>Payment</h3>
          {!! $paymentRequest->getHtmlSnippet(); !!}
        @endif

        @unless($paymentRequest->willRedirect())
            @include('checkout._final_success_text')
        @endunless

    </div>
@endsection
