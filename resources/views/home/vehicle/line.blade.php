<li id="vehicle{{$vehicle?$vehicle->id:''}}" class="row list_panel_line vehicle_line" style="{{$styleAttr}}"
        data="{{$vehicle?$vehicle->id:''}}">
    <span class="line_fluid_column">
        <span class="overflow_container">
            <span class="overflow_ellipsis registration_number">
                {{$vehicle?$vehicle->registration_number:''}}
            </span>
        </span>
    </span>
</li>