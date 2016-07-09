@extends('layouts.app')

@section('pageNav')
    <li><a href="{{ url('/dash') }}">Dash</a></li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Welcome</div>

                <div class="panel-body">
                    Let's track those truckers.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
