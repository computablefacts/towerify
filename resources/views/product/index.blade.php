@extends('layouts.app')

@section('content')
<div class="container">
  @if(!Auth::user()->isBarredFromAccessingTheApp())
  <div class="row">
    <div class="col">
      @include('product._breadcrumbs')
    </div>
  </div>
  @endif
  @if($products->isEmpty())
  <div class="alert alert-info mt-3">
    {{ __('The store is empty.') }}
  </div>
  @else
  <div class="row mt-3">
    <div class="col">
      @if($products->isNotEmpty())
      <div class="card card-default">
        <div class="card-header">{{ $taxon ? __('Products in ') . __($taxon->name) : __('All Products') }}</div>
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
