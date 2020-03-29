<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>Notepad Online - Free store and share</title>
    <meta name="description" content="Notepad online is a free tool for storing and sharing your notes. No registration required."/>
    <meta name="robots" content="”index," follow”/>

    <meta property="og:title" content="Notepad Online - Free store and share">
    <meta property="og:description" content="Notepad online is a free tool for storing and sharing your notes. No registration required.">
    <meta property="og:image" content="https://notepad.imranpollob.com/android-chrome-512x512.png">
    <meta property="og:url" content="https://notepad.imranpollob.com">
    <meta property="og:site_name" content="Notepad Online - Free store and share">

    <meta name="twitter:title" content="Notepad Online - Free store and share">
    <meta name="twitter:description" content="Notepad online is a free tool for storing and sharing your notes. No registration required.">
    <meta name="twitter:image" content="https://notepad.imranpollob.com/android-chrome-512x512.png">
    <meta name="twitter:card" content="summary">

    <!-- Styles -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,600;1,400&display=swap" rel="stylesheet">

    @yield('stylesheet')
</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <button class="btn btn-outline-primary">New Note</button>
            </a>

            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">

            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="btn btn-success" href="{{ route('login') }}">{{ __('Login') }} <i class="fa fa-sign-in"></i></a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('notes') }}">My Notes</a>
                    </li>

                    <li class="nav-item">
                        <a class=" btn btn-success" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }} <i class="fa fa-sign-out"></i>
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                              style="display: none;">
                            @csrf
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </nav>

    <main class="container py-4">
        @include('flash-message')

        @yield('content')
    </main>

    <input type="hidden" id="hiddenInput">
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script defer src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.min.js"></script>
<script defer src="{{ asset('js/script.js') }}"></script>

@yield('javascript')
</body>
</html>
