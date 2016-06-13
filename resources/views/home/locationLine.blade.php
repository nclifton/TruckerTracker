<?php
$id = $location?$location->_id:'';
$registration_number = $location?$location->vehicle->registration_number:'';
$status = $location?$location->status:'';
$sent_at = $location?$location->sent_at:'';
?>
<li id="location{{$id}}" class="row" style="{{$styleAttr}}">
    <span class="col-md-2 col-xs-2 col-sm-2 col-lg-2">
        <button class="btn btn-xs btn-detail open-modal-view-location" value="{{$id}}">View</button>
    </span>
    <span class="registration_number col-md-3 col-xs-3 col-sm-3 col-lg-3">
        {{$registration_number}}
    </span>
    <span class="location-status col-md-1 col-xs-1 col-sm-1 col-lg-1">
        {{$status}}
    </span>
    <span class="location-sent_at col-md-4 col-xs-4 col-sm-4 col-lg-4">
        {{$sent_at}}
    </span>
    <span class="col-md-2 col-xs-2 col-sm-2 col-lg-2">
        <button class="btn btn-danger btn-xs btn-delete delete-location pull-right" value="{{$id}}">
            Delete
        </button>
    </span>
</li>