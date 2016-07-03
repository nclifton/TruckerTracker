<?php
$id = $location ? $location->_id : '';
$registration_number = $location && $location->vehicle && $location->vehicle->registration_number ? $location->vehicle->registration_number : '';
$status = $location ? $location->status : '';
$sent_at = $location ? $location->sent_at : '';
?>
<li id="location{{$id}}" class="row list_panel_line" style="{{$styleAttr}}">

    @can('view-location')
        @if ($status == 'received')
            <button class="btn btn-xs btn-detail open-modal-location-view view-button" value="{{$id}}">View</button>
        @else
            <button class="btn btn-xs btn-detail open-modal-location-view view-button" value="{{$id}}" style="display:none">View</button>
        @endif
    @endcan

    <span class="line_fluid_column">
        <span class="overflow_ellipsis description">
            {{"$registration_number $status $sent_at"}}
        </span>
    </span>
        @can('delete-location')
            <button class="btn btn-danger btn-xs btn-delete delete-location pull-right delete-button" value="{{$id}}">
            Delete
        </button>
        @endcan
</li>