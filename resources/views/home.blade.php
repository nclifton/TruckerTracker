@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <span id="heading_organisation_name">
                            {{$organisation?$organisation->name:''}}
                        </span>
                        <span>
                            Dashboard
                        </span>
                        @if($organisation)
                            <button id="btn-edit-organisation" name="btn-edit-organisation"
                                    class="btn btn-warning btn-xs btn-detail open-modal-organisation pull-right"
                                    value="{{$organisation->_id}}">
                                Edit Organisation
                            </button>
                        @else
                            <button id="btn-edit-organisation" name="btn-edit-organisation"
                                    class="btn btn-warning btn-xs btn-detail open-modal-organisation pull-right"
                                    value="" style="display:none;">
                                Edit Organisation
                            </button>
                            <button id="btn-add-organisation" name="btn-add-organisation"
                                    class="btn btn-primary btn-xs pull-right"
                                    value="">
                                Add Organisation
                            </button>
                        @endif
                    </div>
                    <div class="panel-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-6 col-xs-12 col-sm-6 col-lg-6">
                                    <div class="container-fluid">
                                        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                                            <div class="row">
                                                @if($organisation)
                                                    <button id="btn-add-driver" name="btn-add-driver"
                                                            class="btn btn-primary btn-xs pull-right">
                                                        Add New Driver
                                                    </button>
                                                @else
                                                    <button id="btn-add-driver" name="btn-add-driver"
                                                            class="btn btn-primary btn-xs pull-right"
                                                            disabled="disabled">
                                                        Add New Driver
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="row">
                                                @include('home.listPanel',array('lines'=>$organisation->drivers,'subject'=>'driver'))
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                                            <div class="row">
                                                @include('home.listPanel',array('lines'=>$organisation->messages,'subject'=>'message'))
                                             </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xs-12 col-sm-6 col-lg-6">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                                                <div class="row">
                                                    @if ($organisation)
                                                        <button id="btn-add-vehicle" name="btn-add-vehicle"
                                                                class="btn btn-primary btn-xs pull-right">
                                                            Add New Vehicle
                                                        </button>
                                                    @else
                                                        <button id="btn-add-vehicle" name="btn-add-vehicle"
                                                                class="btn btn-primary btn-xs pull-right"
                                                                disabled="disabled">
                                                            Add New Vehicle
                                                        </button>
                                                    @endif
                                                </div>
                                                <div class="row">
                                                    @include('home.listPanel',array('lines'=>$organisation->vehicles,'subject'=>'vehicle'))
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                                                <div class="row">
                                                    @include('home.listPanel',array('lines'=>$organisation->locations,'subject'=>'location'))
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('home.modalForm',['subject'=>'driver','title'=>'Driver Editor','subject_id_value'=>'','save_button_label'=>'Save Changes'])
                @include('home.modalForm',['subject'=>'vehicle','title'=>'Vehicle Editor','subject_id_value'=>'','save_button_label'=>'Save Changes'])
                @include('home.modalForm',['subject'=>'organisation','title'=>'Organisation Editor','subject_id_value'=>$organisation?$organisation->_id:'','save_button_label'=>$organisation?'Save Changes':'Add Organisation'])
                @include('home.modalForm',['subject'=>'message','title'=>'Message Driver','subject_id_value'=>'','save_button_label'=>'Send'])
                @include('home.modalForm',['subject'=>'location','title'=>'Locate Vehicle','subject_id_value'=>'','save_button_label'=>'Send'])

            </div>
        </div>
    </div>
    <meta name="_token" content="{!! csrf_token() !!}"/>
    <script src="{{ elixir('js/home.js') }}"></script>
@endsection
