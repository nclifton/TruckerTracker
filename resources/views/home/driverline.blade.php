<li id="driver{{$driver?$driver->id:''}}" class="row" style="{{$styleAttr}}">
    <span class="col-md-1 col-xs-1 col-sm-1 col-lg-1">
        <button class="btn btn-xs btn-detail open-modal-message" value="{{$driver?$driver->id:''}}">message</button>
    </span>
    <span class="name col-md-3 col-xs-3 col-sm-3 col-lg-3">
        <span class="first_name">{{$driver?$driver->first_name:''}}</span>
        <span class="last_name">{{$driver?$driver->last_name:''}}</span>
    </span>
    <span class="phone-number col-md-2 col-xs-2 col-sm-2 col-lg-2" >
        {{$driver?$driver->mobile_phone_number:''}}
    </span>
    <span class="detail1 col-md-4 col-xs-4 col-sm-4 col-lg-4">
        {{$driver?$driver->drivers_licence_number:''}}
    </span>
    <span class="col-md-2 col-xs-2 col-sm-2 col-lg-2">
        <button class="btn btn-warning btn-xs btn-detail open-modal-driver pull-right" value="{{$driver?$driver->id:''}}">
            Edit
        </button>
        <button class="btn btn-danger btn-xs btn-delete delete-driver pull-right" value="{{$driver?$driver->id:''}}">
            Delete
        </button>
    </span>
</li>