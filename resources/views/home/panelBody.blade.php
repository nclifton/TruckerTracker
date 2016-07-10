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
                        <div class="row" id="vehicle_controls">
                            @include('home.vehicle.controls')
                        </div>
                        <div class="row">
                            @include('home.listPanel',['lines'=>$org?$org->vehicles:[],'subject'=>'vehicle','add_button_label'=>'Add Vehicle'])
                        </div>
                        <div class="row" id="location_controls">
                            @include('home.location.controls')
                        </div>
                        <div class="">
                            @include('home.listPanel',array('lines'=>$org?$org->locations:[],'subject'=>'location'))
                        </div>
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
                        <div class="row" id="driver_controls">
                            @include('home.driver.controls')
                        </div>
                        <div class="row">
                            @include('home.listPanel',['lines'=>$org?$org->drivers:[],'subject'=>'driver','add_button_label'=>'Add Driver'])
                        </div>
                        <div class="row" id="message_controls">
                            @include('home.message.controls')
                        </div>
                        <div class="row">
                            @include('home.listPanel',['lines'=>$org?$org->messages:[],'subject'=>'message'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>