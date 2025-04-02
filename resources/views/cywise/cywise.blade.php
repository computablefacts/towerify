<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }}</title>

  <!-- favicons -->
  @include('layouts._favicons')

  <!-- FastBootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/fastbootstrap@2.2.0/dist/css/fastbootstrap.min.css" rel="stylesheet"
        integrity="sha256-V6lu+OdYNKTKTsVFBuQsyIlDiRWiOmtC8VQ8Lzdm2i4=" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
          crossorigin="anonymous"></script>

  <style>

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    h1 {
      font-size: 24px;
      margin-top: 20px;
    }

    p {
      font-size: 14px;
      color: #555;
    }

    input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #FFA500;
      color: white;
      border: none;
      cursor: pointer;
      font-size: 16px;
    }

    button:hover {
      background-color: #e08e00;
    }

    .button-group {
      display: flex;
      justify-content: space-between;
    }

    .back-button {
      padding: 10px 20px;
      cursor: pointer;
      background-color: white;
      color: #FFA500;
      border: 2px solid #FFA500;
    }

    .back-button:hover {
      background-color: #e08e00;
      color: white;
      border: 2px solid #e08e00;
    }

    .next-button-300p {
      padding: 10px 20px;
      border: none;
      cursor: pointer;
      background-color: #FFA500;
      color: white;
      width: 300%;
      margin-left: 20px;
    }

    .next-button-300p:hover {
      background-color: #e08e00;
    }

    .next-button-100p {
      background-color: #FFA500;
      color: white;
      width: 100%;
      margin-bottom: 20px;
    }

    .next-button-100p:hover {
      background-color: #e08e00;
    }

  </style>
</head>
<body>
<div style="width:550px;">

  @include('cywise._breadcrumbs')

  @if($step == 1)
  @include('cywise._step-1')
  @elseif($step == 2)
  @include('cywise._step-2')
  @elseif($step == 3)
  @include('cywise._step-3')
  @elseif($step == 4)
  @include('cywise._step-4')
  @elseif($step == 5)
  @include('cywise._step-5')
  @endif

</div>
</body>
</html>
