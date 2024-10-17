@extends('layouts.app')

@section('content')
<div class="container mt-3">
  <h1>{{ __('Wonderful') }} {{ $order->getBillpayer()->firstname }}!</h1>
  <hr>
  <div class="alert alert-success">
    {{ __('Your order has been registered with number') }} <strong>{{ $order->getNumber() }}</strong>.
  </div>
  @if(!empty($paymentRequest->getHtmlSnippet()))
  <h3>{{ __('Payment') }}</h3>
  {!! $paymentRequest->getHtmlSnippet(); !!}
  @endif
  @unless($paymentRequest->willRedirect())
  @include('checkout._final_success_text')
  @endunless
</div>
@endsection
