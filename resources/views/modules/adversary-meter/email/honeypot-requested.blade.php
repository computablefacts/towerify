@component('mail::message')

@component('mail::table')
| {{ __('Attributes') }} | {{ __('Values') }} |
| ------------- | ------------- |
@foreach($params as $key => $value)
| {{ $key }} | {{ $value }} |
@endforeach
@endcomponent

@endcomponent
