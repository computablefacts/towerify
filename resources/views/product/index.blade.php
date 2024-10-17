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
          </ul>
        </div>
      </nav>
    </div>
  </div>
  @if($products->isEmpty())
  <div class="alert alert-info mt-3">
    {{ __('The store is empty.') }}
  </div>
  @else
  <div class="row mt-3">
    <div class="col">
      @if($products->isNotEmpty())
      <div class="card card-default">
        <div class="card-header">{{ $taxon ? __('Products in ') . $taxon->name : __('All Products') }}</div>
        <div class="card-body">
          <div class="card-columns" style="column-count:1;">
            @foreach($products as $product)
            @include('product._product')
            @endforeach
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
  @endif
</div>
@endsection
