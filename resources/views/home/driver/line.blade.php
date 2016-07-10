<li id="driver{{$driver?$driver->id:''}}" class="row list_panel_line driver_line" style="{{$styleAttr}}"
        data="{{$driver?$driver->id:''}}">
    <span class="line_fluid_column">
        <span class="overflow_container">
            <span class="overflow_ellipsis name">
                {{$driver?$driver->first_name:''}}
                {{$driver?$driver->last_name:''}}
            </span>
        </span>
    </span>
</li>