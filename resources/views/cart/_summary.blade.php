<table class="table table-borderless table-condensed">
  <thead>
  <tr>
    <th class="ps-0">{{ __('Products') }} :</th>
  </tr>
  </thead>
  <tbody>
  @foreach(Cart::getItems() as $item)
  <tr>
    <td class="pt-0 pb-0">{{ $item->quantity }} x {{ $item->product->getName() }}</td>
  </tr>
  @endforeach
  </tbody>
</table>
@if($euVat)
<table class="table table-borderless table-condensed">
  <thead>
  <tr>
    <th class="ps-0">{{ __('EU VAT') }} :</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td class="pt-0 pb-0">{{ format_price($euVat) }}</td>
  </tr>
  </tbody>
</table>
@endif
<table class="table table-borderless table-condensed">
  <thead>
  <tr>
    <th class="ps-0">{{ __('Total') }} :</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td class="pt-0 pb-0">{{ format_subscription_price(Cart::total(), isset($euVat)) }}</td>
  </tr>
  </tbody>
</table>
