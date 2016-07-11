@extends('layouts.app')

@section('pageNav')
        @if($org)
            @can('view-organisation',$org)
                <li>
                    <a id="btn-edit-org" name="btn-edit-org" href="#" class="open-modal-org" data="{{ $org->_id}}">
                        Edit Organisation
                    </a>
                </li>
            @endcan
        @else
            <li>
                <a id="btn-add-org" name="btn-add-org" href="#" class="open-modal-org" data="">
                    Add Organisation
                </a>
            </li>
        @endif

        @can('add-vehicle',$org)
            <li>
                <a id="btn-add-vehicle" name="btn-add-vehicle" href="#" {{$org?:'disabled="disabled"'}}>
                    Add Vehicle
                </a>
            </li>
        @endcan
        @can('add-driver',$org)
            <li>
                <a id="btn-add-driver" name="btn-add-driver" href="#" {{$org?:'disabled="disabled"'}}>
                    Add Driver
                </a>
            </li>
        @endcan

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
    @include('home.modal',['subject'=>'location','title'=>'Locate Vehicle','subject_id_value'=>'','save_button_label'=>'Send'])
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
    <script>
        @if($org && $user->can('view-location') )
            subscribe_sse = true;
            organisation_id = '{{$org->_id}}';
        @else
            subscribe_sse = false;
        @endif
    </script>
    <script src="{{ elixir('js/home.js') }}"></script>

@endsection
