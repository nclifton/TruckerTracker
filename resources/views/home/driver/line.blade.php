<li id="driver{{$driver?$driver->id:''}}" class="row list_panel_line" style="{{$styleAttr}}">
    <button class="btn btn-xs btn-detail open-modal-message message-button" value="{{$driver?$driver->id:''}}">message</button>
    <span class="line_fluid_column">
        <span class="overflow_ellipsis description">
            {{$driver?$driver->first_name:''}}
            {{$driver?$driver->last_name:''}}
        </span>
    </span>
    <span class="edit-delete-button pull-right">
        @can('delete-driver',$org)
            <button class="btn btn-danger btn-xs btn-delete delete-driver pull-right"
                    value="{{$driver?$driver->id:''}}">
                Delete
            </button>
        @endcan
        @can('update-driver',$org)
            <button class="btn btn-warning btn-xs btn-detail open-modal-driver pull-right"
                  value="{{$driver?$driver->id:''}}">
                Edit
            </button>
        @endcan
    </span>
</li>