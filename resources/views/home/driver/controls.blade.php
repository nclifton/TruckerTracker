<?php
$user = Auth::getUser();
?>
<div class="col-md-4">
@can('send-message')
    <button id="btn-messageDriver"
            class="btn btn-xs btn-detail open-modal-message message-button"
            value="" disabled="disabled">message</button>
@endcan
</div>
<div class="col-md-8">
@if ($user->can('delete-driver') || $user->can('update-driver'))
    @can('delete-driver')
        <button id="btn-delete-driver"
                class="btn btn-danger btn-xs btn-delete delete-button delete-driver pull-right"
                value="" disabled="disabled">Delete</button>
    @endcan
    @can('update-driver')
        <button id="btn-edit-driver"
                class="btn btn-warning btn-xs btn-detail edit-button pull-right open-modal-driver"
                value="" disabled="disabled">Edit</button>
    @endcan
@endif
</div>
