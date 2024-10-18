@extends('layouts.app')

@section('content')
<div class="container">
  @if(!Auth::user()->isBarredFromAccessingTheApp())
  <div class="row">
    <div class="col">
      @include('product._breadcrumbs')
    </div>
  </div>
  <hr>
  @endif
  <h1 class="mt-0 mb-3">{{ $product->name }}</h1>
  <hr>
    <?php $website = App\Helpers\AppStore::findWebsiteFromSku($product->sku) ?>
    <?php $package = App\Helpers\AppStore::findPackageFromSku($product->sku) ?>
    <?php $adminDoc = App\Helpers\AppStore::findAdminDocFromSku($product->sku) ?>
    <?php $userDoc = App\Helpers\AppStore::findUserDocFromSku($product->sku) ?>
  <div class="row">
    <div class="col-md-4">
      <div class="mb-3">
          <?php $img = $product->hasImage() ? $product->getImageUrl('medium') : '/images/product-medium.jpg' ?>
        <img src="{{ $img }}" width="100%"/>
      </div>
    </div>
    <div class="col-md-8">
      <form action="{{ route('cart.add', $product) }}" method="post" class="mb-4 float-end">
        {{ csrf_field() }}
        <span class="me-2 fw-bold">
        {!! format_subscription_price($product->price) !!}
        </span>
        <button type="submit" class="btn btn-primary text-white">{{ __('Add To Cart') }}</button>
      </form>
      @unless(empty($product->propertyValues))
      <table class="table table-sm">
        <tbody>
        @if(isset($website))
        <tr>
          <th>{{ __('Website') }}</th>
          <th><a href="{{ $website }}" target="_blank">{{ $website }}</a></th>
        </tr>
        @endif
        @if(isset($adminDoc))
        <tr>
          <th>{{ __('Admin Documentation') }}</th>
          <th><a href="{{ $adminDoc }}" target="_blank">{{ $adminDoc }}</a></th>
        </tr>
        @endif
        @if(isset($userDoc))
        <tr>
          <th>{{ __('User Documentation') }}</th>
          <th><a href="{{ $userDoc }}" target="_blank">{{ $userDoc }}</a></th>
        </tr>
        @endif
        @if(isset($package))
        <tr>
          <th>{{ __('Package') }}</th>
          <th><a href="{{ $package }}" target="_blank">{{ $package }}</a></th>
        </tr>
        @endif
        @foreach($product->propertyValues->sortBy('title')->groupBy(fn($p) => $p->property->name) as $key => $values)
        <tr>
          <th>{{ __($key) }}</th>
          <td>
            @foreach($values as $value)
            {{ __($value->title) }}
            @endforeach
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
      @endunless
      @unless(empty($product->description))
      <p class="text-muted">{!! nl2br($product->description) !!}</p>
      @endunless
    </div>
  </div>
</div>
@endsection
