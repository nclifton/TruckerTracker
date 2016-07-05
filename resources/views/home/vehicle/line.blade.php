<li id="vehicle{{$vehicle?$vehicle->id:''}}" class="row list_panel_line" style="{{$styleAttr}}">
    @can('send-location')
    <button class="btn btn-xs btn-detail open-modal-location locate-button pull-left" value="{{$vehicle?$vehicle->id:''}}">
        locate
    </button>
    @endcan
    <span class="line_fluid_column">
        <span class="overflow_ellipsis registration_number">
            {{$vehicle?$vehicle->registration_number:''}}
        </span>
    </span>
    <span class="edit-delete-button pull-right">
        @can('delete-vehicle',$org)
        <button class="btn btn-danger btn-xs btn-delete delete-vehicle pull-right" value="{{$vehicle?$vehicle->id:''}}">
            Delete
        </button>
        @endcan
        @can('update-vehicle',$org)
        <button class="btn btn-warning btn-xs btn-detail open-modal-vehicle pull-right" value="{{$vehicle?$vehicle->id:''}}">
            Edit
        </button>
        @endcan
    </span>
</li>