<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>Paste Online - store and share notes</title>
    <meta name="description" content="Paste online is a free tool for storing and sharing your notes. No registration required." />
    <meta name="robots" content="”index," follow” />

    <meta property="og:title" content="Paste Online - Free store and share">
    <meta property="og:description" content="Paste online is a free tool for storing and sharing your notes. No registration required.">
    <meta property="og:image" content="https://paste.imranpollob.com/android-chrome-512x512.png">
    <meta property="og:url" content="https://paste.imranpollob.com">
    <meta property="og:site_name" content="Paste Online - Free store and share">

    <meta name="twitter:title" content="Paste Online - Free store and share">
    <meta name="twitter:description" content="Paste online is a free tool for storing and sharing your notes. No registration required.">
    <meta name="twitter:image" content="https://paste.imranpollob.com/android-chrome-512x512.png">
    <meta name="twitter:card" content="summary">

    <!-- Styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=1.7">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">


    @yield('stylesheet')
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <button class="btn btn-outline-primary btn-sm b-3"><i class="fa fa-plus"></i> <b>NEW PASTE</b></button>
                </a>

                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto align-items-center">
                    <!-- Authentication Links -->
                    @guest
                    <li class="nav-item">
                        <a class="btn btn-outline-success btn-sm" href="{{ route('login') }}">{{ __('Login') }} <i class="fa fa-sign-in"></i></a>
                    </li>
                    @else
                    @if(auth()->id() === 1)
                    <li class="nav-item {{ (request()->is('dashboard')) ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    @endif

                    <li class="nav-item {{ (request()->is('notes')) ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('notes') }}">My Notes</a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-outline-success btn-sm" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }} <i class="fa fa-sign-out"></i>
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
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

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script defer src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script defer src="{{ asset('js/script.js') }}?v=1.4"></script>

    @yield('javascript')
</body>

</html>