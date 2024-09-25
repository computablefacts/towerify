<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ env('APP_NAME') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #00264b;
                color: white;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .links > a {
                color: white;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .links > a:hover {
              color: #f8b502;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .container {
              display: flex;
              align-items: center;
              justify-content: center
            }

            img {
              max-width: 100px;
              max-height:100px;
            }

            .text {
              font-size: 100px;
              padding-left: 20px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class=" m-b-md">
                    <div class="container">
                        <div class="image">
                          <img src="{{ asset('images/logo.png') }}" alt="Cywise's logo">
                        </div>
                        <div class="text">
                          {{ env('APP_NAME') }}
                        </div>
                    </div>
                </div>
                <div class="links">
                    <a href="{{ route('product.index') }}">Store</a>
                    <a href="{{ config('konekt.app_shell.ui.url') }}">Admin</a>
                </div>
            </div>
        </div>
    </body>
</html>
