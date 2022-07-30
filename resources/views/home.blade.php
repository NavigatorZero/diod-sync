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
                                <div class="col-8 container">
                                    <form action="{{route('api.post-commission')}}" method="post"
                                          enctype="multipart/form-data">
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
                                                <button type="submit" name="submit" id="submit"
                                                        class="btn btn-primary btn-block">
                                                    Загрузить коммиссию
                                                </button>
                                                <input type="file" name="excel_commission" class="custom-file-input m-1"
                                                       id="chooseFile">
                                                @if (isset($file_msg))
                                                    <div class="alert alert-success">
                                                        <strong>{{ $file_msg }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        Последняя синхронизация завершилась в : {{$time}}
                                    </div>
                                    <div class="row justify-content-around mt-3">
                                        <div class="col-6">
                                            <a href="{{route('api.get-price')}}">
                                                <button class="btn btn-success">
                                                    Выгрузить цены
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="{{route('api.get-stocks')}}">
                                                <button class="btn btn-success">
                                                    Выгрузить остатки
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 container">

                                    <div class="d-flex">
                                        @if($json->is_sync_in_progress === true)
                                            <div class="alert alert-warning">
                                                <small>Выполняется процесс синхронизации..</small>
                                            </div>
                                        @else
                                            <div class="alert alert-primary">
                                                <small>Синхронизация не выполняется</small>
                                            </div>
                                        @endif
                                    </div>

                                    <form action="{{route('api.post-sync-settings')}}" method="post">
                                        @csrf
                                        <label for="customRange2" class="form-label">Время первой синхронизации</label>

                                        <div class="d-flex">
                                            <input type="range" id="rangeInput" name="first_sync_input"
                                                   class="form-range" min="0" max="23"
                                                   value="{{(int)$json->first_sync}}"
                                                   oninput="first_sync.value=first_sync_input.value">

                                            <output id="amount" name="first_sync" class="ms-3"
                                                    for="rangeInput">{{(int)$json->first_sync}}
                                            </output>
                                            :00
                                        </div>

                                        <label for="customRange2" class="form-label">Время второй синхронизации</label>

                                        <div class="d-flex">
                                            <input type="range" id="rangeInput" name="second_sync_input"
                                                   class="form-range" min="0" max="23"
                                                   value="{{(int)$json->second_sync}}"
                                                   oninput="second_sync.value=second_sync_input.value">

                                            <output id="amount" name="second_sync" class="ms-3"
                                                    for="rangeInput">{{(int)$json->second_sync}}
                                            </output>
                                            :00
                                        </div>

                                        <div class="text-center p-4">
                                            <button type="submit" name="submit_sync" id="submit_sync"
                                                    class="btn btn-success">Cохранить настройки
                                            </button>
                                        </div>
                                        @if (isset($msg))
                                            <div class="alert alert-success">
                                                <strong>{{ $msg }}</strong>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
