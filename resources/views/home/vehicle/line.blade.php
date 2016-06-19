<li id="vehicle{{$vehicle?$vehicle->id:''}}" class="row" style="{{$styleAttr}}">
    <span class="col-md-1 col-xs-1 col-sm-1 col-lg-1">
        @can('send-location')
        <button class="btn btn-xs btn-detail open-modal-location" value="{{$vehicle?$vehicle->id:''}}">
            locate
        </button>
        @endcan
    </span>
    <span class="name col-md-3 col-xs-3 col-sm-3 col-lg-3" id="span_registration_number_{{$vehicle?$vehicle->id:''}}">
        {{$vehicle?$vehicle->registration_number:''}}
    </span>
    <span class="phone-number col-md-2 col-xs-2 col-sm-2 col-lg-2" id="span_mobile_phone_number_{{$vehicle?$vehicle->id:''}}">
        {{$vehicle?$vehicle->mobile_phone_number:''}}
    </span>
    <span class="detail1 col-md-4 col-xs-4 col-sm-4 col-lg-4" id="span_tracker_imei_number_{{$vehicle?$vehicle->id:''}}">
        {{$vehicle?$vehicle->tracker_imei_number:''}}
    </span>
    <span class="col-md-2 col-xs-2 col-sm-2 col-lg-2">
        @can('update-vehicle',$org)
        <button class="btn btn-warning btn-xs btn-detail open-modal-vehicle pull-right" value="{{$vehicle?$vehicle->id:''}}">
            Edit
        </button>
        @endcan
        @can('delete-vehicle',$org)
        <button class="btn btn-danger btn-xs btn-delete delete-vehicle pull-right" value="{{$vehicle?$vehicle->id:''}}">
            Delete
        </button>
        @endcan
    </span>
</li>