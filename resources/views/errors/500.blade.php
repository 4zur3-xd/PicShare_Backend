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
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/pic_share_logo.png') }}" alt="Pic Share logo" width="40" height="40">
                Pic Share
            </a>
        </div>
    </nav>

    <div style="text-align: center; margin-top: 25px;">
        <img src="{{ asset('images/pic_share_logo.png') }}" alt="Pic Share logo" class="img-fluid" style="max-width: 200px;">
        <h1 style="margin-top: 15px;">Pic Share</h1>
        <h2 style="color: red;">Something went wrong!</h2>
        <a href="{{ url('/') }}">Go back to Home</a>

        <hr style="width: 25%; margin: auto; margin-top: 25px; margin-bottom: 25px;">

        @if (!empty($error_info))
            <h3>Info:</h3>
            <p>{{ $error_info }}</p>
        @endif
    </div>

    <footer class="bg-body text-center p-4 fixed-bottom">
        <b>Copyright &copy; {{ date('Y') }} Pic Share - Group 17 Advanced Android Programming - CT5-ACT</b>
        <br>
        <i>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</i>
    </footer>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

</body>
</html>