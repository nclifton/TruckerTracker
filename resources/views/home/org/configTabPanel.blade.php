<form id="orgConfigForm" name="orgConfigForm" class="form-horizontal" novalidate="">
    <div class="form-group">
        <label for="input_name" class="col-sm-5 control-label">Organisation Name</label>
        <div class="col-sm-7">
            <input type="text" class="form-control"
                   id="org_name" name="name"
                   placeholder="required" value="">
        </div>
    </div>

    <div class="form-group">
        <label for="input_timezone" class="col-sm-5 control-label">Timezone</label>
        <div class="col-sm-7">
            {!! $tzhelper->selectForm($org?$org->timezone:'Australia/Sydney','select',['id' => 'timezone','name' => 'timezone','class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group">
        {{ Form::label('hour12', '12 Hour Time (AM/PM)',['class'=>'control-label col-sm-5']) }}
        <div class="col-sm-7">
            {{ Form::checkbox('hour12',true, null, ['class'=>'col-sm-7 form-control']) }}
        </div>
    </div>
</form>
