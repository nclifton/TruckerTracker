<div class="panel-body">
    <div class="container-fluid">
        <div class="row panel-group" id="accordion">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#locate_vehicles_collapsible">Locate Vehicles</a>
                    </h4>
                </div>
                <div id="locate_vehicles_collapsible" class="panel-collapse collapse">
                    <div class="container-fluid">
                        @include('home.listPanel',['lines'=>$org?$org->vehicles:[],'subject'=>'vehicle','add_button_label'=>'Add Vehicle'])
                        @include('home.listPanel',['lines'=>$org?$org->locations()->orderBy('created_at','desc')->get():[],'subject'=>'location'])
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#message_drivers_collapsible">Message Drivers</a>
                    </h4>
                </div>
                <div id="message_drivers_collapsible" class=" panel-collapse collapse">
                    <div class="container-fluid">
                        @include('home.listPanel',['lines'=>$org?$org->drivers:[],'subject'=>'driver','add_button_label'=>'Add Driver'])
                        @include('home.listPanel',['lines'=>$org?$org->messages()->orderBy('created_at','desc')->get():[],'subject'=>'message'])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>