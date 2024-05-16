<?php $website = App\Helpers\AppStore::findWebsiteFromSku($product->sku) ?>
<?php $package = App\Helpers\AppStore::findPackageFromSku($product->sku) ?>
<?php $adminDoc = App\Helpers\AppStore::findAdminDocFromSku($product->sku) ?>
<?php $userDoc = App\Helpers\AppStore::findUserDocFromSku($product->sku) ?>
<div class="row">
    <div class="col-md-6">
        <div class="mb-2">
            <?php $img = $product->hasImage() ? $product->getImageUrl('medium') : '/images/product-medium.jpg' ?>
            <img src="{{ $img  }}" id="product-image" />
        </div>

        <div class="thumbnail-container">
            @foreach($product->getMedia() as $media)
                <div class="thumbnail me-1">
                    <img class="mw-100" src="{{ $media->getUrl('thumbnail') }}"
                         onclick="document.getElementById('product-image').setAttribute('src', '{{ $media->getUrl("medium") }}')"
                    />
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-6">
        <form action="{{ route('cart.add', $product) }}" method="post" class="mb-4 float-end">
            {{ csrf_field() }}

            <span class="me-2 fw-bold text-primary btn-lg">
              @if($product->price > 0)
              {{ format_subscription_price($product->price) }}
              @endif
            </span>
            <button type="submit" class="btn btn-success btn-lg">{{ __('Deploy') }}</button>
        </form>

        @unless(empty($product->propertyValues))
            <table class="table table-sm mb-0">
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
        @else
            <hr class="d-none">
        @endunless

        @unless(empty($product->description))
            <hr class="mt-0">
            <p class="text-primary">{!!  nl2br($product->description) !!}</p>
            <hr>
        @endunless
    </div>
</div>
