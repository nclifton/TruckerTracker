<li id="user{{$user?$user->id:''}}" class="row list_panel_line" style="{{$styleAttr}}">
    @can('edit-users',$org)
        <span class="edit-button">
            <button class="btn btn-warning btn-xs btn-detail open-modal-user pull-left" value="{{$user?$user->id:''}}">
                Edit
            </button>
        </span>
    @endcan
    <span class="line_fluid_column">
        <span class="overflow_container">
            <span class="overflow_ellipsis name_email">
                {{$user?$user->name:''}} {{$user?$user->email:''}}
            </span>
        </span>
    </span>
    @can('edit-users',$org)
        <span class="delete-button">
            <button class="btn btn-danger btn-xs btn-delete delete-user pull-right" value="">
                Delete
            </button>
        </span>
    @endcan
</li>