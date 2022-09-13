<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
     <div class="container">
        <div class="row">
            <div class="col-md-12 mt-5">
                <button class="getauthurl btn btn-info">Add Google</button>
            </div>
        </div>
     </div>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $('.getauthurl').click(function (param) {
        $.ajax({
            type: "get",
            url: "{{route('getAuthUrl')}}",
            dataType: "json",
            success: function (res) {
                window.open(res, "Google Auth", "location=yes");
            }
        });
    })
</script>
</html>
