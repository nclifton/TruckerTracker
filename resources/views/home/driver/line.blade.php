<li id="driver{{$driver?$driver->id:''}}" class="row" style="{{$styleAttr}}">
    <span class="message-button pull-left">
        <button class="btn btn-xs btn-detail open-modal-message" value="{{$driver?$driver->id:''}}">message</button>
    </span>
    <span class="">
        <span class="name">
            <span class="first_name">{{$driver?$driver->first_name:''}}</span>
            <span class="last_name">{{$driver?$driver->last_name:''}}</span>
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