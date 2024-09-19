<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Pic Share - Admin Page</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Georgia, 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body data-bs-theme="dark" style="background-image: url('https://laravel.com/assets/img/welcome/background.svg'); background-repeat: no-repeat; background-size: 50%;">

    <nav class="navbar bg-body">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('images/pic_share_logo.png') }}" alt="Pic Share logo" width="40" height="40">
                Pic Share
            </a>
            @if (!auth()->user())
                <li class="nav-item dropdown" style="list-style-type: none;">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Please Login!
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('login') }}">Login</a></li>
                        <li><a class="dropdown-item" href="{{ route('register') }}">Register</a></li>
                    </ul>
                </li>
            @elseif (auth()->user()->role == 'user' || auth()->user()->status == 0)
                <li class="nav-item dropdown" style="list-style-type: none;">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if (auth()->user()->url_avatar)
                            <img src="{{ auth()->user()->url_avatar }}" width="35px" height="35px" style="border-radius: 50%;">
                        @else
                            <img src="{{ asset('images/blank-avatar.jpg') }}" width="35px" height="35px" style="border-radius: 50%;">
                        @endif
                        {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                        </li>
                    </ul>
                </li>
            @else
                <li class="nav-item dropdown" style="list-style-type: none;">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if (auth()->user()->url_avatar)
                            <img src="{{ auth()->user()->url_avatar }}" width="35px" height="35px" style="border-radius: 50%;">
                        @else
                            <img src="{{ asset('images/blank-avatar.jpg') }}" width="35px" height="35px" style="border-radius: 50%;">
                        @endif
                        {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </div>
    </nav>

    <div style="text-align: center; margin-top: 25px;">
        <img src="{{ asset('images/pic_share_logo.png') }}" alt="Pic Share logo" class="img-fluid" style="max-width: 200px;">
        <h1 style="margin-top: 15px;">Pic Share</h1>
        <h2>Admin Page</h2>

        <br>

        @if (!auth()->user())
            <h3>Please login first!</h3>
            <p>Admin account required.</p>
        @elseif (auth()->user()->status == 0)
            <h3>Access Denied!</h3>
            <p>This account has been banned! Mail to abcxyz@gmail.com for information or to protest the ban.</p>
        @elseif (auth()->user()->role == 'admin')
            <h3>Welcome back, {{ auth()->user()->name }}!</h3>
        @else
            <h3>Access Denied!</h3>
            <p>Sorry, you shouldn't mean to be here! This page is for admin only.</p>
        @endif
    </div>

    <footer class="bg-body text-center p-4 fixed-bottom">
        <b>Copyright 2024 - Group 17 Advanced Android Programing - CT5-ACT</b>
        <br>
        <i>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</i>
    </footer>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

</body>
</html>