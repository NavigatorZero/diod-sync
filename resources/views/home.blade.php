@extends('layouts.app')

@section('content')
    <div class="container">
        @if (isset($msg) || isset($file_msg))
            <div class="alert alert-success" role="alert">
                    {{ $msg ?? $file_msg}}
                </div>
            </div>
        @endif

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
                                                        class="btn btn-primary btn-block btn-main">
                                                    Загрузить коммиссию
                                                </button>
                                                <input type="file" name="excel_commission" class="custom-file-input m-1"
                                                       id="chooseFile">
                                            </div>
                                        </div>
                                    </form>
                                    <form action="{{route('api.post-stocks')}}" method="post" class="pt-3"
                                          enctype="multipart/form-data">
                                        @csrf
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
                                                        class="btn btn-primary btn-block btn-supreme">
                                                    Загрузить остатки
                                                </button>
                                                <input type="file" name="excel_stocks" class="custom-file-input m-1"
                                                       id="chooseFile_1">
                                            </div>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        Последняя синхронизация завершилась в : {{$json->last_sync ?? ''}}
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
                                        <div class="d-flex flex-column pt-2">
                                            <div class="d-flex">
                                                <input type="checkbox"
                                                       class="form-check"
                                                       name="is_second_sync_input"
                                                       {{ $json->is_second_sync === true ? 'checked': '' }}
                                                       id="isSecondSync"
                                                >
                                                <span class="form-label ps-2">Дополнительная синхронизация</span>
                                            </div>
                                            <div class="d-flex">
                                                <input type="range" id="rangeInput_2" name="second_sync_input"
                                                       {{ $json->is_second_sync === true ? '': 'disabled' }}
                                                       class="form-range" min="0" max="23"
                                                       value="{{(int)$json->second_sync}}"
                                                       oninput="second_sync.value=second_sync_input.value">

                                                <output id="range_2"
                                                         name="second_sync" class="ms-3"
                                                        for="rangeInput">{{(int)$json->second_sync}}
                                                </output>
                                                :00
                                            </div>
                                        </div>

                                        <div class="text-center p-4">
                                            <button type="submit" name="submit_sync" id="submit_sync"
                                                    class="btn btn-success">Cохранить настройки
                                            </button>
                                        </div>
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

@section('scripts')
    <script>
        $( document ).ready(function() {
            const checkbox = $('#isSecondSync').first();
            const output = $('#rangeInput_2').first();
            checkbox.click(()=> {
                console.log('here', output);
                    output.prop("disabled", !checkbox.is(':checked'));

            })

        });
    </script>
@endsection
