@can('edit-users',$org)
<div class="col-xs-4">
    <button id="btn-add-user" name="btn-add-user"
            class="btn btn-primary  pull-left">
        <i class="fa fa-btn fa-user"></i>Register New User</button>
</div>
<div class="col-xs-8">
    <button id="btn-edit-user"
            class="btn btn-warning  btn-detail open-modal-user pull-right"
            value="" disabled="disabled">Edit</button>
    <button id="btn-delete-user"
            class="btn btn-danger  btn-delete delete-user pull-right"
            value="" disabled="disabled">Delete</button>
</div>
@endcan
