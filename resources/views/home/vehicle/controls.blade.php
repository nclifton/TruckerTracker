<?php
$user = Auth::getUser();
?>
<div class="col-md-4">
@can('send-location')
    <button id="btn-locateVehicle"
            class="btn btn-xs btn-detail open-modal-location locate-button pull-left"
            value="" disabled="disabled">locate</button>
@endcan
</div>
<div class="col-md-8">
@if ($user->can('delete-vehicle') || $user->can('update-vehicle'))
    @can('delete-vehicle',$org)
        <button id="btn-delete-vehicle"
                class="btn btn-danger btn-xs btn-delete delete-button delete-vehicle pull-right"
                value="" disabled="disabled">Delete</button>
    @endcan
    @can('update-vehicle',$org)
        <button id="btn-edit-vehicle"
                class="btn btn-warning btn-xs btn-detail edit-button open-modal-vehicle pull-right"
                value="" disabled="disabled">Edit</button>
    @endcan
@endif
</div>
