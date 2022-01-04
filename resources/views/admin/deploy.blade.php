@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Application version
                            </div>
                        </div>
                        <div class="Block__body text-center">
                            <form action="#" class="Form form-horizontal">
                                <div class="form-group">
                                    <label class="col-md-4 control-label">Current version</label>

                                    <div class="col-md-6">
                                        <input class="form-control" type="text" readonly value="{{ $data['currentVersion'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label">Latest version</label>

                                    <div class="col-md-6">
                                        <input class="form-control" type="text" readonly value="{{ $data['latestVersion'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label">Environment</label>

                                    <div class="col-md-6">
                                        <input class="form-control" type="text" readonly value="{{ app()->environment() }}">
                                    </div>
                                </div>
                            </form>

                            <br>

                            @if ($data['currentVersion'] === $data['latestVersion'])
                                <div class="text-muted">You are using the latest version. Great!</div>
                            @elseif (app()->environment(['staging', 'production']))
                                <div>
                                    <a href="{{ route('app.deploy.authorized') }}" class="Button Button--danger">
                                        Update application
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if (isset($data['output']))
                        <div class="Block">
                            <div class="Block__header">
                                <div class="Block__heading">
                                    Latest deployment log
                                </div>
                            </div>
                            <div class="Block__body">
                                <pre style="margin: 0;">{!! $data['output'] !!}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
