@extends('layouts.app')

@section('pageNav')
    @include('home.pageNav')
@endsection

@section('content')
    @can('view-location',$org)
        @include('home.modal',['subject'=>'location.view','title'=>'Vehicle Location','subject_id_value'=>''])
    @endcan
    @can('add-driver',$org)
        @include('home.modal',['subject'=>'driver','title'=>'Driver Editor','subject_id_value'=>'','save_button_label'=>'Save Changes'])
    @endcan
    @include('home.modal',['subject'=>'messageDriver','title'=>'Message Driver','subject_id_value'=>'','save_button_label'=>'Send'])
    @can('add-vehicle',$org)
        @include('home.modal',['subject'=>'vehicle','title'=>'Vehicle Editor','subject_id_value'=>'','save_button_label'=>'Save Changes'])
    @endcan
    @include('home.modal',['subject'=>'locateVehicle','title'=>'Locate Vehicle','subject_id_value'=>'','save_button_label'=>'Send'])
    @if (!$org || $user->can('view-organisation',$org))
        @include('home.modal',['subject'=>'org','title'=>'Organisation Editor','subject_id_value'=>$org?$org->_id:'','save_button_label'=>$org?'Save Changes':'Add Organisation'])
        @include('home.modal',['subject'=>'user','title'=>'Register Organisation User','subject_id_value'=>'','save_button_label'=>'<i class="fa fa-btn fa-user"></i>Register'])
    @endif

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    @include('home.panelHeading')
                    @include('home.panelBody')
                </div>
            </div>
        </div>
    </div>
    <meta name="_token" content="{!! csrf_token() !!}"/>
    <script src="{{ elixir('js/home.js') }}"></script>
    <script>
        $(document).ready(function () {
            @if($org)
                    Common.settings.subscribe_sse = true;
            Common.settings.orgId = '{{$org->_id}}';
            Common.settings.time_format_hour12 = '{{$org->hour12}}';
            Common.settings.default_locale_code = 'en-AU';
            @else
                    Common.settings.subscribe_sse = false;
            @endif
            Common.init();
            OrganisationDialogue.init();
            UserDialogue.init();
            DriverDialogue.init();
            VehicleDialogue.init();
            MessageDialogue.init();
            LocationDialogue.init();
        });

    </script>

@endsection
