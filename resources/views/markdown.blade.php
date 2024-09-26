@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card card-default">
        <div class="card-body terms-of-service">
          {!! $terms !!}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
