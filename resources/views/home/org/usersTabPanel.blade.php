<div class="container-fluid">
    <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
        <div class="row">
            @can('edit-users',$org)
                <button href="#user" id="btn-add-user" name="btn-add-user"
                        class="btn btn-primary btn-xs pull-right">
                    <i class="fa fa-btn fa-user"></i>Register New User
                </button>
            @endcan
        </div>
        <div class="row">
            @include('home.listPanel',['lines'=>$org?$org->users:[],'subject'=>'user'])
        </div>
    </div>

</div>

