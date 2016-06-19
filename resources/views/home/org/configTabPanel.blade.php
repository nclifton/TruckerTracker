<form id="orgForm" name="orgForm" class="form-horizontal" novalidate="">
    <div class="form-group">
        <label for="input_name" class="col-sm-5 control-label">Organisation Name</label>
        <div class="col-sm-7">
            <input type="text" class="form-control"
                   id="org_name" name="name"
                   placeholder="required" value="">
        </div>
    </div>
    <div class="form-group">
        <label for="input_twilio_account_sid" class="col-sm-5 control-label">Twilio Account SID</label>
        <div class="col-sm-7">
            <input type="text" class="form-control"
                   id="twilio_account_sid" name="twilio_account_sid"
                   value="">
        </div>
    </div>
    <div class="form-group">
        <label for="input_twilio_auth_token" class="col-sm-5 control-label">Twilio Authentication Token</label>
        <div class="col-sm-7">
            <input type="text" class="form-control"
                   id="twilio_auth_token" name="twilio_auth_token"
                   value="">
        </div>
    </div>
    <div class="form-group">
        <label for="input_twilio_phone_number" class="col-sm-5 control-label">Twilio Phone Number</label>
        <div class="col-sm-7">
            <input type="text" class="form-control"
                   id="twilio_phone_number" name="twilio_phone_number"
                   value="">
        </div>
    </div>
    <div class="form-group">
        <label for="input_timezone" class="col-sm-5 control-label">Timezone</label>
        <div class="col-sm-7">
            {!! $tzhelper->selectForm($org?$org->timezone:'Australia/Sydney','select',['id' => 'timezone','name' => 'timezone','class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group">
        <label for="input_datetime_format" class="col-sm-5 control-label">Date Time Format</label>
        <div class="col-sm-7">
            <select type="text" class="form-control"
                    id="datetime_format" name="datetime_format">
                <option value="H:i:s d/m/y">HH:mm:ss dd/mm/yy</option>
                <option value="H:i:s D d/m/y">HH:mm:ss Day dd/mm/yy</option>
                <option value="h:i:s A d/m/y">hh:mm:ss AM/PM dd/mm/yy</option>
                <option value="h:i:s A D d/m/y">hh:mm:ss AM/PM Day dd/mm/yy</option>
                <option value="H:i:s m/d/y">HH:mm:ss mm/dd/yy</option>
                <option value="H:i:s D m/d/y">HH:mm:ss Day mm/dd/yy</option>
                <option value="h:i:s A m/d/y">hh:mm:ss AM/PM mm/dd/yy</option>
                <option value="h:i:s A D m/d/y">hh:mm:ss AM/PM Day mm/dd/yy</option>
            </select>
        </div>
    </div>
</form>
