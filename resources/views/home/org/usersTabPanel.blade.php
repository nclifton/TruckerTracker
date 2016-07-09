@can('edit-users',$org)
    <div class="row">
        <div class="col-md-12">
            <button href="#user" id="btn-add-user" name="btn-add-user"
                    class="btn btn-primary btn-xs pull-right">
                <i class="fa fa-btn fa-user"></i>Register New User
            </button>
        </div>
    </div>
@endcan
<div class="row">
        @include('home.listPanel',['lines'=>$org?$org->addedUsers()->get():[],'subject'=>'user'])
</div>


