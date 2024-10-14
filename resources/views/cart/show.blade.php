@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('product.index') }}">{{ __('All Products') }}</a></li>
    <li class="breadcrumb-item">{{ __('Cart') }}</li>
@stop

@section('content')
    <style>
        .product-image {
            height: 45px;
        }
    </style>
    <div class="container">
        <h1>{{ __('Cart') }}</h1>
        <hr>

        @if(Cart::isEmpty())
            <div class="alert alert-info">
                {{ __('Your cart is empty') }}
            </div>
        @else
        <div class="row">
            <div class="col-md-8">
                <div class="card bg-light">
                    <div class="card-header">{{ __('Cart Items') }}</div>

                    <div class="card-body">
                        <div class="rounded bg-white">
                            <table class="table table-borderless" style="border: 1px solid #becdcf;">
                                <thead>
                                <tr>
                                    <th colspan="2">{{ __('Product Name') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                    <th>{{ __('Total') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $defaultThumbnail = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC8AAAAjCAMAAAAzO6PlAAAAt1BMVEX///+tq6u2tLSXlZW0srKenJ2LiYqzsLGZmJiNi4yqqKmvra2cmpqRj5CmpKSPjY6koqKUk5O4traopqagnp+Ih4exr6/Mycq5t7fOzMy7ubmioKCGhIWTkZLEwsK9u7vCv8DV09PS0NDQzs7Jx8fHxcWBgIF/fX58envX1dXZ19eDgoNwb3C/vb3U0dH7+vt1dHXh4eHe29vb2dl4d3j39/dpaGrx8fHo5+djYmPt7e309PRbW1zggo2RAAAC0UlEQVQ4y22T23qCMBCEA6LWA1qlchIBBaViPdVabe37P1dnN4GU72su9OafnZkNER9FsYs6Y7v11H55dvqDruknaZx72WG53Zbl5Z3O5VJut8vDapWJ12ITzTrjRZNfe8FqWQkUnAWB54lis4tmPc27lh/umV8doICgrOF8vSZ+1unZFW+41pT4PMgyFiia4ThNBY1H/AnzI8Wn69yTAhyaDTqO030SCsJ7DV4V9oIAgkOD9k0RMb/QPBf27pkUAKckTE9NyxXAEV/zvKDtURzfSQBaDqfZVndgiBmNR93WsOaTu6BzDzyKklfDQfcdgfEyfs3HDyHPdempLKE/peHOaC6AN3lkqc9ZFa3xtuDxNe9YlEWfb6/G+87zvP0k+UXFx1fRPMfDPgzVdOAtgTg1Pyo1qDMlqOoafeDDlt3gje9/+NseazeQ5mXYWozBc37e5/wfg3PIe+9zGruj5hM/BO+kzQLXwOc0BpedjGfM0z4p0BwfaPf+dz1p6KvxHIf4vwWcbNA3VsdqN5ck3Jd+V/LUttfk0feR9o3kxvgjT8LgKm6Ja6jtLMDje1CBkGcJrDSMwZmL+gn9v1su7kry4w6+N31jw3b4QGjTMLxbYJkxfK45x+f8TxPwkRJMIEAk54zcHiy6FvW4hxbx3Jf22RMRPxjlQJ09YGd3YN0h3E5NrMcd0HVRoImN9ygdtMDCLd+CB35iX/PSYCI2m91OWti2FMxHF3mzpjnFE2SeDdCgJYqigAQe9bPBtcVXFMVLNnm+MmCBeMWBQj97+o6e3YuFl2+Bl4EqQVucPj4gKMhCvXskwsPBS3PBk0ElIIV4O51OUEgBSksD4mGgBRQJFiPxhqMEqoI2IB4C1YEV4vMTAlhAIBNpgwEaT32fLZTCEF9fULADJdIGzMPA91lRScCzQBtUgVQBPwxDLbHEz48W7CJeEQWSBZhPEkgq0S8ch3VdK2koBAAAAABJRU5ErkJggg=='; ?>
                                @foreach(Cart::getItems() as $item)
                                    <tr>
                                        <td width="55"><img src="{{ $item->product->getThumbnailUrl() ?: $defaultThumbnail }}" class="product-image"/></td>
                                        <td>
                                            <a href="{{ route('product.show', $item->product->masterProduct ? $item->product->masterProduct->slug : $item->product->slug) }}">
                                                {{ $item->product->getName() }}
                                            </a></td>
                                        <td>{{ format_price($item->price) }}</td>
                                        <td>
                                            <form class="form-inline" action="{{ route('cart.update', $item) }}" method="POST" id="cart-qty-form--{{ $item->id }}">
                                                @csrf
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <button class="btn btn-outline-secondary" type="button" data-itemid="{{ $item->id }}" data-role="cart-qty-changer" data-direction="-">-</button>
                                                    </div>
                                                    <input type="text" name="qty" value="{{ $item->quantity }}" class="form-control" id="cart-qty-input--{{ $item->id }}" />
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" type="button" data-itemid="{{ $item->id }}" data-role="cart-qty-changer" data-direction="+">+</button>
                                                    </div>
                                                </div>

                                            </form>
                                        </td>
                                        <td>{{ format_price($item->total) }}</td>
                                        <td>
                                            <form action="{{ route('cart.remove', $item) }}"
                                                  style="display: inline-block" method="post">
                                                {{ csrf_field() }}
                                                <button dusk="cart-delete-{{ $item->getBuyable()->id }}" class="btn btn-link btn-sm"><span class="text-danger">&xotime;</span></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="4"></th>
                                    <th>
                                        {{ format_subscription_price(Cart::total(), isset($euVat)) }}
                                    </th>
                                    <th></th>
                                </tr>
                                </tfoot>

                            </table>
                        </div>

                        <p>
                            <a href="{{ route('product.index') }}" class="btn-lg ps-0">{{ __('Continue Shopping') }}</a>
                        </p>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-white">
                    <div class="card-header">{{ __('Summary') }}</div>
                    <div class="card-body">
                        @include('cart._summary')
                        <a href="{{ route('checkout.show') }}" class="btn btn-block btn-primary">{{ __('Proceed To Checkout') }}</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    $('document').ready(function () {
        $('button[data-role="cart-qty-changer"]').click(function () {
            itemid = $(this).data('itemid');
            $form = $('#cart-qty-form--' + itemid);
            $input = $('#cart-qty-input--' + itemid);
            currentQty = parseInt($input.val());
            qty = currentQty;
            if ('+' == $(this).data('direction')) {
                qty = currentQty + 1;
            } else if ('-' == $(this).data('direction')) {
                qty = currentQty - 1;
            } else {
                return; //do nothing if forged
            }

            $input.val(qty);
            $form.submit();
        });
    });
</script>
@endpush
