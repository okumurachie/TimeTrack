<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('/css/reset.css')  }}">
    <link rel="stylesheet" href="{{ asset('/css/common.css')  }}">
    @yield('css')
</head>

<body>
    <div class="content">
        <div class="container">
            @yield('content')
        </div>
    </div>
</body>

</html>