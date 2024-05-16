@extends('layouts.app')

@section('categories-menu')
    <nav class="navbar navbar-expand-lg tw-products">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse tw-categories" id="categoriesMenu">
            <ul class="navbar-nav">
              @foreach($taxonomies as $taxonomy)
                @if($taxonomy->rootLevelTaxons()->count() > 0)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle p-0" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ $taxonomy->name }}
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        @include('product.index._category_level', ['taxons' => $taxonomy->rootLevelTaxons()])
                    </div>
                </li>
                @endif
            @endforeach
            </ul>
        </div>
    </nav>
@stop

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('product.index') }}">All Products</a></li>
    @if($taxon)
        @include('product._breadcrumbs')
    @endif
@stop

@section('content')
    <div class="container">
        @if($taxon && $taxon->hasImage())
            <div style="background-image: url('{{ $taxon->getImageUrl('header') }}'); height: 150px;"
                 class="mb-2">
                <h1 class="p-3 text-light" style="text-shadow: #333 0 0 11px">{{ $taxon->name }}</h1>
            </div>
        @endif
        <div class="row mt-3">

            <div class="col-md-3">
                @include('product.index._filters', ['properties' => $properties, 'filters' => $filters])
            </div>

            <div class="col-md-9">
                @if($taxon && $products->isEmpty() && $taxon->children->count())
                    <div class="card card-default mb-4">
                        <div class="card-header">{{ $taxon->name }} Subcategories</div>

                        <div class="card-body">
                            <div class="row">
                            @foreach($taxon->children as $child)
                                <div class="col-12 col-sm-6 col-md-4 mb-4">
                                    @include('product.index._category', ['taxon' => $child])
                                </div>
                            @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if(!$products->isEmpty())
                <div class="card card-default card-accent-secondary tw-card">
                    <div class="card-header">{{ $taxon ?  'Products in ' . $taxon->name : 'All Products' }}</div>

                    <div class="card-body">
                        <div class="card-columns" style="column-count:1;">

                            @foreach($products as $product)
                                <div class="card mb-3">
                                    @include('product.index._product')
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
