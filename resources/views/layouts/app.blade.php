<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>Note Online - Store and share notes</title>
    <meta name="description" content="Note online is a free tool for storing and sharing your notes. No registration required." />
    <meta name="robots" content="”index," follow” />

    <meta property="og:title" content="Note Online - Store and share notes">
    <meta property="og:description" content="Note online is a free tool for storing and sharing your notes. No registration required.">
    <meta property="og:image" content="https://note.imranpollob.com/android-chrome-512x512.png">
    <meta property="og:url" content="https://note.imranpollob.com">
    <meta property="og:site_name" content="Note Online - Store and share notes">

    <meta name="twitter:title" content="Note Online - Store and share notes">
    <meta name="twitter:description" content="Note online is a free tool for storing and sharing your notes. No registration required.">
    <meta name="twitter:image" content="https://note.imranpollob.com/android-chrome-512x512.png">
    <meta name="twitter:card" content="summary">

    <!-- Styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=1.7">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        .app-navbar {
            background: linear-gradient(135deg, #ffffff 0%, #f4f9ff 60%, #fff8ec 100%);
            border-bottom: 1px solid #e9edf2;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .app-navbar .navbar-brand {
            margin-right: 0.75rem;
        }

        .app-navbar-inner {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            min-height: 46px;
        }

        .app-logo-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            text-decoration: none;
            color: #1c2f46;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .app-logo-center:hover {
            text-decoration: none;
            color: #0f2238;
        }

        .app-logo-dot {
            width: 12px;
            height: 12px;
            background: linear-gradient(135deg, #0f5da5 0%, #2ca2ff 100%);
            border-radius: 50% !important;
            box-shadow: 0 0 0 4px rgba(44, 162, 255, 0.15);
        }

        .app-logo-text {
            font-family: 'Lora', serif;
            font-size: 18px;
            line-height: 1;
        }

        .app-nav-left,
        .app-nav-right {
            display: flex;
            align-items: center;
        }

        .app-navbar .btn-new-note {
            border-width: 2px;
            font-weight: 600;
            letter-spacing: 0.3px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .app-navbar .main-nav-link {
            color: #2b2b2b;
            font-weight: 600;
            padding: 7px 12px;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }

        .app-navbar .main-nav-link:hover {
            color: #111;
            border-color: #dce8f7;
            background-color: #f5f9ff;
        }

        .app-navbar .nav-item.active .main-nav-link {
            color: #0c3a67;
            border-color: #c7dbf0;
            background-color: #eaf4ff;
        }

        .app-navbar .btn-login,
        .app-navbar .btn-logout {
            border-width: 1.5px;
        }

        .rich-editor-shell #data-editor {
            border: 1px solid #dfe5ec;
            background: #fff;
            display: flex;
            flex-direction: column;
            height: 68vh;
            max-height: 820px;
            min-height: 420px;
        }

        .rich-editor-shell #data-editor .ql-toolbar.ql-snow {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #fff;
            border: 0;
            border-bottom: 1px solid #e7ebf0;
        }

        .rich-editor-shell #data-editor .ql-container.ql-snow {
            border: 0;
            flex: 1 1 auto;
            height: auto !important;
            overflow-y: scroll;
            scrollbar-width: thin;
            scrollbar-color: #b7c5d6 #f4f7fb;
        }

        .rich-editor-shell #data-editor .ql-container.ql-snow::-webkit-scrollbar {
            width: 10px;
        }

        .rich-editor-shell #data-editor .ql-container.ql-snow::-webkit-scrollbar-track {
            background: #f4f7fb;
        }

        .rich-editor-shell #data-editor .ql-container.ql-snow::-webkit-scrollbar-thumb {
            background: #b7c5d6;
            border-radius: 8px !important;
        }

        .rich-editor-shell #data-editor .ql-editor {
            min-height: 100%;
            font-size: 15px;
            line-height: 1.5;
        }

        .rich-editor-shell #data-editor .ql-editor img {
            max-width: 100%;
            height: auto;
            cursor: default;
        }

        .rich-editor-shell #data-editor .ql-editor img.editor-image-active {
            outline: 2px solid #2c7edb;
            outline-offset: 2px;
            cursor: nwse-resize;
        }

        @media (max-width: 991px) {
            .app-navbar-inner {
                flex-wrap: wrap;
                justify-content: flex-start;
                gap: 8px;
            }

            .app-logo-center {
                position: static;
                transform: none;
                order: -1;
                width: 100%;
                justify-content: center;
                margin-bottom: 6px;
            }

            .app-nav-right .navbar-nav {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 768px) {
            .rich-editor-shell #data-editor {
                height: 56vh;
                min-height: 320px;
            }
        }
    </style>

    @yield('stylesheet')
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand navbar-light app-navbar">
            <div class="container">
                <div class="app-navbar-inner">
                    <div class="app-nav-left">
                        <a class="navbar-brand mb-0" href="{{ route('note.new') }}">
                            <button type="button" class="btn btn-outline-dark btn-sm btn-new-note"><i class="fa fa-plus"></i> NEW NOTE</button>
                        </a>
                    </div>

                    <a href="{{ route('home') }}" class="app-logo-center" aria-label="Go to homepage">
                        <span class="app-logo-dot"></span>
                        <span class="app-logo-text">Note Online</span>
                    </a>

                    <div class="app-nav-right ml-auto">
                        <ul class="navbar-nav align-items-center">
                            @guest
                            <li class="nav-item">
                                <a class="btn btn-outline-dark btn-sm mx-2 btn-login" href="{{ route('login') }}"><i class="fa fa-sign-in"></i> Login</a>
                            </li>
                            @else
                            @if(auth()->id() === 1)
                            <li class="nav-item {{ (request()->is('dashboard')) ? 'active' : '' }}">
                                <a class="nav-link main-nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            @endif

                            <li class="nav-item {{ (request()->is('notes')) ? 'active' : '' }}">
                                <a class="nav-link main-nav-link" href="{{ route('notes') }}">Notes</a>
                            </li>

                            <li class="nav-item {{ (request()->is('notebooks*')) ? 'active' : '' }}">
                                <a class="nav-link main-nav-link" href="{{ route('notebooks.index') }}">Notebooks</a>
                            </li>

                            <li class="nav-item">
                                <a class="btn btn-outline-dark btn-sm ml-2 btn-logout" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out"></i> Logout</a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </li>
                            @endguest
                        </ul>
                    </div>
                </div>
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
    <script defer src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script defer src="{{ asset('js/script.js') }}?v=1.5"></script>

    @yield('javascript')
</body>

</html>
