<nav class="navbar navbar-expand-lg border-0 p-0" style="background-color:transparent;min-height:30px;">
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav">
      <li class="nav-item dropdown" style="min-height:30px;color:#ffaa00;">
        /
      </li>
      @foreach($taxonomies as $taxonomy)
      @if($taxonomy->rootLevelTaxons()->count() > 0)
      <li class="nav-item dropdown" style="min-height:30px;">
        <a class="nav-link dropdown-toggle p-0" href="#" id="navbarDropdown" role="button"
           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          @if($taxonomy->name === App\Models\Taxonomy::ROOT)
          {{ __('Home') }}
          @else
          {{ __($taxonomy->name) }}
          @endif
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          @include('product._category_level', ['taxons' => $taxonomy->rootLevelTaxons()])
        </div>
      </li>
      @endif
      @endforeach
      @if($taxon)
      @if($taxon->parent)
      <li class="nav-item dropdown" style="min-height:30px;color:#ffaa00;">
        /
      </li>
      <li class="nav-item dropdown" style="min-height:30px;">
        <a href="{{ route('product.category', [$taxon->parent->taxonomy->slug, $taxon->parent]) }}">
          {{ __($taxon->parent->name) }}
        </a>
      </li>
      @endif
      <li class="nav-item dropdown" style="min-height:30px;color:#ffaa00;">
        /
      </li>
      <li class="nav-item dropdown" style="min-height:30px;">
        <a href="{{ route('product.category', [$taxon->taxonomy->slug, $taxon]) }}">
          {{ __($taxon->name) }}
        </a>
      </li>
      @endif
      @if(isset($product))
      <li class="nav-item dropdown" style="min-height:30px;color:#ffaa00;">
        /
      </li>
      <li class="nav-item dropdown" style="min-height:30px;">
        {{ $product->name }}
      </li>
      @endif
    </ul>
  </div>
  <div class="text-end">
    <a href="{{ route('cart.show') }}">{{ __('Cart') }} ({{ Cart::itemCount() }})</a>
  </div>
</nav>