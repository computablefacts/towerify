@foreach($taxons as $taxon)
<?php $count = (new \Vanilo\Foundation\Search\ProductSearch())->withinTaxon($taxon)->getResults()->count() ?>
<div class="ps-{{ $taxon->level }}">
  @if($taxon->children->count())
  {{--<h6 class="dropdown-header">{{ __($taxon->name) }}</h6>--}}
  <a href="{{ route('product.category', [$taxon->taxonomy->slug, $taxon]) }}" class="dropdown-item dropdown-header">
    {{ __($taxon->name) }} ({{ $count }})
  </a>
  @include('product._category_level', ['taxons' => $taxon->children])
  @else
  <a class="dropdown-item" href="{{ route('product.category', [$taxon->taxonomy->slug, $taxon]) }}">
    {{ __($taxon->name) }} ({{ $count }})
  </a>
  @endif
</div>
@endforeach
