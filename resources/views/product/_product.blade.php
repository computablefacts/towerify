<article class="card mb-3">
  <div class="row">
    <div class="col-2">
      <div class="card-body p-2">
        <a
          href="@if($taxon) {{ route('product.show-with-taxon', [$product->slug, $taxon]) }} : {{ route('product.show', $product->slug) }} @endif">
          <img class="card-img"
               @if($product->hasImage())
          src="{{ $product->getThumbnailUrl() }}"
          @else
          src="/images/product.jpg"
          @endif
          alt="{{ $product->name }}" />
        </a>
      </div>
    </div>
    <div class="col-10">
      <div class="card-body p-2">
        <h5>
          <a
            href="@if($taxon) {{ route('product.show-with-taxon', [$product->slug, $taxon]) }} @else {{ route('product.show', $product->slug) }} @endif">
            {{ $product->name }}
          </a>
          <span class="float-end">
            {{ format_subscription_price($product->price) }}
          </span>
        </h5>
        <p class="card-text mb-0">
          {{ $product->description }}
        </p>
      </div>
    </div>
  </div>
</article>
