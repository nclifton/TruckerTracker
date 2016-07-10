<div class="col-md-offset-10 col-md-2">
    @can('delete-message')
        <button id="btn-delete-messages"
                class="btn btn-danger btn-xs btn-delete delete-messages delete-button pull-right "
                value="" disabled="disabled">Delete</button>
    @endcan
</div>
