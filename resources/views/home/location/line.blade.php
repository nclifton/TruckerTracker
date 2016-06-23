<?php
$id = $location ? $location->_id : '';
$registration_number = $location && $location->vehicle && $location->vehicle->registration_number ? $location->vehicle->registration_number : '';
$status = $location ? $location->status : '';
$sent_at = $location ? $location->sent_at : '';
?>
<li id="location{{$id}}" class="row" style="{{$styleAttr}}">
    <span class="view-button pull-left">
        @if($user->can('view-location') && ($status == 'received') )
            <button class="btn btn-xs btn-detail open-modal-location-view   " value="{{$id}}">View</button>
        @endif
    </span>
    <span class="">
        <span class="registration_number">
            {{$registration_number}}
        </span>
        <span class="status">
            {{$status}}
        </span>
        <span class="sent_at">
            {{$sent_at}}
        </span>
    </span>
    <span class="delete-button pull-right">
        @can('delete-location')
            <button class="btn btn-danger btn-xs btn-delete delete-location pull-right" value="{{$id}}">
            Delete
        </button>
        @endcan
    </span>
</li>