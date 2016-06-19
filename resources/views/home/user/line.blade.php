<li id="user{{$user?$user->id:''}}" class="row" style="{{$styleAttr}}">
    <span class="col-md-1 col-xs-1 col-sm-1 col-lg-1 edit-btn">
    @can('edit-users',$org)
        <button class="btn btn-warning btn-xs btn-detail open-modal-user pull-left"
                        value="{{$user?$user->id:''}}">
            Edit
        </button>
            @endcan
    </span>
    <span class="name col-md-4 col-xs-4 col-sm-4 col-lg-4">
        {{$user?$user->name:''}}
    </span>
    <span class="email col-md-6 col-xs-6 col-sm-6 col-lg-6">
        {{$user?$user->email:''}}
    </span>
    <span class="col-md-1 col-xs-1 col-sm-1 col-lg-1">
        <button class="btn btn-danger btn-xs btn-delete delete-user pull-right" value="">
            Delete
        </button>
    </span>
</li>