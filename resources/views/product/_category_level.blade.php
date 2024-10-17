@foreach($taxons as $taxon)
<?php $count = (new \Vanilo\Foundation\Search\ProductSearch())->withinTaxon($taxon)->getResults()->count() ?>
@if($count > 0)
<div class="ps-{{$taxon->level}}">
  @if($taxon->children->count())
  <div class="dropdown-divider"></div>
  {{--<h6 class="dropdown-header">{{ $taxon->name }}</h6>--}}
  <a href="{{ route('product.category', [$taxon->taxonomy->slug, $taxon]) }}" class="dropdown-item dropdown-header">
    {{ $taxon->name }} ({{ $count }})
  </a>
  @include('product._category_level', ['taxons' => $taxon->children])
  <div class="dropdown-divider"></div>
  @else
  <a class="dropdown-item" href="{{ route('product.category', [$taxon->taxonomy->slug, $taxon]) }}">
    {{ $taxon->name }} ({{ $count }})
  </a>
  @endif
</div>
@endif
@endforeach
