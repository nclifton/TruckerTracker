<?php
$id = '';
$status = '';
$registration_number = '';
$status_at = '';
if ($location) {
    $id = $location->_id;
    $registration_number = $location->vehicle ? $location->vehicle->registration_number : '';
    $status = $location->status;
    switch ($status){
        case 'queued':
            $status_at = $location->queued_at;
            break;
        case 'sent':
            $status_at = $location->sent_at;
            break;
        case 'delivered':
            $status_at = $location->send_at;
            break;
        case 'received':
            $status_at = $location->received_at;
            break;
    }
}
?>
<li id="location{{$id}}" class="row list_panel_line" style="{{$styleAttr}}">

    @can('view-location')

        @if ($status == 'received')
            <span class="view-button">
                <button class="btn btn-xs btn-detail open-modal-location-view view-button" value="{{$id}}">View</button>
            </span>
        @else
            <span class="view-button" style="display:none">
                <button class="btn btn-xs btn-detail open-modal-location-view view-button" value="{{$id}}">View</button>
            </span>
        @endif
    @endcan

    <span class="line_fluid_column">
        <span class="registration_number">{!! $registration_number !!}</span>
        <span class="status">{!! $status !!}</span>
        <span class="overflow_container">
            <span class="overflow_ellipsis status_at">{!! $status_at !!}</span>
        </span>
    </span>
    @can('delete-location')
    <span class="pull-right">
    <button class="btn btn-danger btn-xs btn-delete delete-location pull-right delete-button" value="{{$id}}">
        Delete
    </button>
    </span>
    @endcan
</li>