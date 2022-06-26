<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Boostrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2"
            crossorigin="anonymous"></script>

    <!-- Styles -->
</head>
<body class="antialiased">
<div class="container">
    <div class="row pt-4">


        <div class="container mt-5">
            <form action="{{route('api.post-commission')}}" method="post" enctype="multipart/form-data">
                @csrf
                <h3 class="text-center mb-5">Sync helper</h3>
                @php
                    $time = Cache::get("last_sync");
                @endphp
                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        <strong>{{ $message }}</strong>
                    </div>
                @endif
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="custom-file">
                    <div class="d-flex">
                        <button type="submit" name="submit" id="submit" class="btn btn-primary btn-block">
                            Загрузить коммиссию
                        </button>
                        <input type="file" name="excel_commission" class="custom-file-input m-1" id="chooseFile">
                    </div>
                </div>
            </form>
            <div class="m-3">
                Последняя синхронизация завершилась в : {{$time}}
            </div>
            <div class="row">

                <div class="col-2">
                    <a href="{{route('api.get-price')}}">
                        <button class=" btn btn-success">
                            Выгрузить цены
                        </button>
                    </a>
                </div>
                <div class="col-2">
                    <a href="{{route('api.get-stocks')}}">
                        <button class=" btn btn-success">
                            Выгрузить остатки
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
