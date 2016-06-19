<form id="userForm" name="userForm" class="form-horizontal" novalidate="">
    <div class="form-group">
        <label for="user_name" class="col-md-4 control-label">Name</label>
        <div class="col-md-6">
            <input id="user_name" type="text" class="form-control" name="name" value="{{ old('name') }}">
        </div>
    </div>
    <div class="form-group">
        <label for="email" class="col-md-4 control-label">E-Mail Address</label>
        <div class="col-md-6">
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">
        </div>
    </div>
    <div class="form-group">
        <label for="password" class="col-md-4 control-label">Password</label>
        <div class="col-md-6">
            <input id="password" type="password" class="form-control" name="password">
        </div>
    </div>
    <div class="form-group">
        <label for="password_confirm" class="col-md-4 control-label">Confirm Password</label>
        <div class="col-md-6">
            <input id="password_confirm" type="password" class="form-control" name="password_confirmation">
        </div>
    </div>
</form>