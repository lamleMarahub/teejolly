<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TeeJolly') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.9.0/css/all.css">

    <!-- Scripts -->

    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'TeeJolly') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    @if (Auth::check())
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        <!--<li class="nav-item">-->
                        <!--    <a class="nav-link" href="{{ url('dashboard') }}">dashboard</a>-->
                        <!--</li>-->
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('mockup') }}">mockups</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('design') }}">designs</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('collection') }}">collections</a>
                        </li>

                        @if(Auth::user()->isSeller())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('orders') }}">amazon</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('etsy') }}">shops</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('etsy/orders') }}">orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('etsy/statistic') }}">statistics</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('blacklist') }}">blacklist</a>
                        </li>
                        @endif
                        <!--@if(Auth::user()->isAdmin())-->
                        <!--<li class="nav-item dropdown">-->
                        <!--    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">amazon</a>-->
                        <!--    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">-->
                        <!--        <a class="dropdown-item" href="{{ url('orders') }}">orders</a>-->
                        <!--        <a class="dropdown-item" href="{{ url('order/statistic') }}">statistics</a>-->
                        <!--    </div>-->
                        <!--</li>-->
                        <!--@endif-->
                    </ul>
                    @endif

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('login') }}</a>
                            </li>

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ mb_convert_case(Auth::user()->name, MB_CASE_LOWER, "UTF-8") }} <span class="caret"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ url('user/profile') }}" >my account</a>
                                    @if(in_array(Auth::user()->id,[1]))
                                    <a class="dropdown-item" href="{{ url('users') }}" >users</a>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                        document.getElementById('logout-form').submit();">
                                        {{ __('logout') }}
                                    </a>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

        <div id="main-alert" class="text-center w-100">
            <span class="mx-auto alert alert-success" role="alert">
                This is a info alert—check it out!
            </span>
        </div>

        <div id="loading" class="text-center w-100">
            <span class="mx-auto alert alert-primary" role="alert">
            <i class="fa fa-spinner fa-spin fa-fw"></i> loading...
            </span>
        </div>

        <main class="py-4">
            @yield('content')
        </main>

        <button onclick="topFunction()" id="back-to-top" title="Go to top" class="btn btn-primary"><i class="fa fa-arrow-up" aria-hidden="true"></i> Top</button>
        <script type="text/javascript">
            // When the user scrolls down 20px from the top of the document, show the button
            window.onscroll = function() {scrollFunction()};

            function scrollFunction() {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                    document.getElementById("back-to-top").style.display = "block";
                } else {
                    document.getElementById("back-to-top").style.display = "none";
                }
            }

            // When the user clicks on the button, scroll to the top of the document
            function topFunction() {
                document.body.scrollTop = 0; // For Chrome, Safari and Opera
                document.documentElement.scrollTop = 0; // For IE and Firefox
            }

            function showAlert(message, delayTimeInSecond=1) {
                $('#main-alert > span').html(message);
                //$('#main-alert').fadeIn(300).next().faceOut(300);
                $('#main-alert').fadeIn("fast", function() { $(this).delay(delayTimeInSecond*1000).fadeOut("slow"); });
            }

            function showLoading() {
                $('#loading').fadeIn("fast");
            }
            function hideLoading() {
                $('#loading').fadeOut("fast");
            }
        </script>
    </div>
    @if (isset($message))
        <script language="javascript">showAlert("{{ $message }}");</script>
    @endif
</body>
</html>
