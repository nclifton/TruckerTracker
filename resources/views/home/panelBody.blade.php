<div class="panel-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-xs-12 col-sm-6 col-lg-6">
                <div class="container-fluid">
                    <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                        @include('home.listPanelWithAddButton',['lines'=>$org?$org->drivers:[],'subject'=>'driver','add_button_label'=>'Add New Driver'])
                    </div>
                    <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                        <div class="row">
                            @include('home.listPanel',['lines'=>$org?$org->messages:[],'subject'=>'message'])
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xs-12 col-sm-6 col-lg-6">
                <div class="row">
                    <div class="container-fluid">
                        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                            @include('home.listPanelWithAddButton',['lines'=>$org?$org->vehicles:[],'subject'=>'vehicle','add_button_label'=>'Add New Vehicle'])
                        </div>
                        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                            <div class="row">
                                @include('home.listPanel',array('lines'=>$org?$org->locations:[],'subject'=>'location'))
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>