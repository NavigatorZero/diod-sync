@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Главная') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                        <div class="container">
                            <div class="row pt-4">
                                <div class="container mt-5">
                                    <form action="{{route('api.post-commission')}}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        @php
                                            $time = Cache::get("last_sync", "Синхронизация была прервана");
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
