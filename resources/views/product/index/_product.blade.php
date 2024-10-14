<article class="card shadow-sm mb-0">
  <div class="row">
    <div class="col-sm-2 align-self-center">
      <div class="card-body p-2">
        <a href="{{ route('product.show', $product->slug) }}">
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
    <div class="col-sm-10">
      <div class="card-body p-2">
        <h5>
          <a href="{{ route('product.show', $product->slug) }}">
            {{ $product->name }}
          </a>
          <span class="float-end">
            @if($product->price > 0)
            {{ format_subscription_price($product->price) }}
            @endif
          </span>
        </h5>
        <p class="card-text mb-0">
          {{ $product->description }}
        </p>
      </div>
    </div>
  </div>
</article>
