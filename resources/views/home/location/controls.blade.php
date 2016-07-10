<div class="col-md-6">
    @can('view-location')
        <button id="btn-view-locations"
                class="btn btn-xs btn-detail open-modal-location-view view-button"
                value="" disabled="disabled">View</button>
    @endcan
</div>
<div class="col-md-6">
@can('delete-location')
    <button id="btn-delete-locations"
            class="btn btn-danger btn-xs btn-delete delete-locations delete-button pull-right "
            value="" disabled="disabled">Delete</button>
@endcan
</div>
