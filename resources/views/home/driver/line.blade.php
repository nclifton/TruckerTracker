<li id="driver{{$driver?$driver->id:''}}" class="row list_panel_line" style="{{$styleAttr}}">
    @can('send-message')
        <span class="message-button">
            <button class="btn btn-xs btn-detail open-modal-message message-button" value="{{$driver?$driver->id:''}}">message</button>
        </span>
    @endcan
    <span class="line_fluid_column">
        <span class="overflow_container">
            <span class="overflow_ellipsis name">
                {{$driver?$driver->first_name:''}}
                {{$driver?$driver->last_name:''}}
            </span>
        </span>
    </span>
    @can('delete-driver',$org)
        <span class="delete-button">
            <button class="btn btn-danger btn-xs btn-delete delete-button delete-driver pull-right"
                value="{{$driver?$driver->id:''}}">
                Delete
            </button>
        </span>
    @endcan
    @can('update-driver',$org)
        <span class="edit-button">
            <button class="btn btn-warning btn-xs btn-detail edit-button open-modal-driver pull-right"
              value="{{$driver?$driver->id:''}}">
                Edit
            </button>
        </span>
    @endcan
</li>