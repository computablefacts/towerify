@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col">
      <nav class="navbar navbar-expand-lg border-0 p-0" style="background-color:transparent;min-height:30px;">
        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav">
            <li class="nav-item dropdown" style="min-height:30px;">
              <a href="{{ route('product.index') }}">{{ __('Home') }}</a>
            </li>
            <li class="nav-item dropdown" style="min-height:30px;color:#ffaa00;">
              /
            </li>
            @foreach($taxonomies as $taxonomy)
            @if($taxonomy->rootLevelTaxons()->count() > 0)
            <li class="nav-item dropdown" style="min-height:30px;">
              <a class="nav-link dropdown-toggle p-0" href="#" id="navbarDropdown" role="button"
                 data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ $taxonomy->name }}
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                @include('product._category_level', ['taxons' => $taxonomy->rootLevelTaxons()])
              </div>
            </li>
            @endif
            @endforeach
            @if($taxon)
            <li class="nav-item dropdown" style="min-height:30px;color:#ffaa00;">
              /
            </li>
            <li class="nav-item dropdown" style="min-height:30px;">
              <a href="{{ route('product.category', [$taxon->taxonomy->slug, $taxon]) }}">{{ $taxon->name }}</a>
            </li>
            @endif
            <li class="nav-item dropdown" style="min-height:30px;color:#ffaa00;">
              /
            </li>
            <li class="nav-item dropdown" style="min-height:30px;">
              {{ $product->name }}
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </div>
  <hr>
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
        {{ format_subscription_price($product->price) }}
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
        @foreach($product->propertyValues as $propertyValue)
        <tr>
          <th>{{ $propertyValue->property->name }}</th>
          <td>{{ $propertyValue->title }}</td>
        </tr>
        @endforeach
        </tbody>
      </table>
      @endunless
      @unless(empty($product->description))
      <p class="text-primary">{!! nl2br($product->description) !!}</p>
      @endunless
    </div>
  </div>
</div>
@endsection
