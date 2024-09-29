<form class="card card-default mb-3 card-accent-secondary tw-card" action="{{
    $taxon ?
    route('product.category', [$taxon->taxonomy->slug, $taxon])
    :
    route('product.index')
}}">
    <div class="card-header">{{ __('Filters') }}
        @if($properties->map(function ($property) {
        return $property->values()->count();
        })->sum() > 0)
        <button class="btn btn-sm btn-primary float-end pt-0 pb-0">Apply</button>
        @endif
    </div>
    @if($properties->map(function ($property) {
    return $property->values()->count();
    })->sum() > 0)
    <ul class="list-group list-group-flush">
        @foreach($properties as $property)
            @include('product.index._property', ['property' => $property, 'filters' => $filters[$property->slug] ?? []])
        @endforeach
    </ul>
    @else
    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        {{ __('The filters are not available.') }}
      </li>
    </ul>
    @endif
</form>
