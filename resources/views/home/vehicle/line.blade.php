<li id="vehicle{{$vehicle?$vehicle->id:''}}" class="row list_panel_line" style="{{$styleAttr}}"
    xmlns="http://www.w3.org/1999/html">
    @can('send-location')
        <span class="locate-button">
            <button class="btn btn-xs btn-detail open-modal-location locate-button pull-left" value="{{$vehicle?$vehicle->id:''}}">
                locate
            </button>
        </span>
    @endcan
    <span class="line_fluid_column">
        <span class="overflow_container">
            <span class="overflow_ellipsis registration_number">
                {{$vehicle?$vehicle->registration_number:''}}
            </span>
        </span>
    </span>
    @can('delete-vehicle',$org)
        <span class="delete-button">
            <button class="btn btn-danger btn-xs btn-delete delete-button delete-vehicle pull-right" value="{{$vehicle?$vehicle->id:''}}">
                Delete
            </button>
        </span>
    @endcan
    @can('update-vehicle',$org)
        <span class="edit-button">
            <button class="btn btn-warning btn-xs btn-detail edit-button open-modal-vehicle pull-right" value="{{$vehicle?$vehicle->id:''}}">
                Edit
            </button>
        </span>
    @endcan
</li>