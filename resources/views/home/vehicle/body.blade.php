<form id="vehicleForm" name="vehicleForm" class="form-horizontal" novalidate="">
    <div class="form-group error">
        <label for="registration_number" class="col-sm-3 control-label">Registration
            number</label>
        <div class="col-sm-9">
            <input type="text" class="form-control text-uppercase"
                   id="registration_number" name="registration_number"
                   placeholder="required" value="">
        </div>
    </div>
    <div class="form-group error">
        <label for="vehicle_mobile_phone_number"
               class="col-sm-3 control-label">Mobile phone number</label>
        <div class="col-sm-9">
            <input type="text" class="form-control"
                   id="vehicle_mobile_phone_number" name="mobile_phone_number"
                   placeholder="required" value="">
        </div>
    </div>
    <div class="form-group">
        <label for="tracker_imei_number" class="col-sm-3 control-label">Tracker
            IMEI number</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="tracker_imei_number"
                   name="tracker_imei_number" placeholder="optional" value="">
        </div>
    </div>
</form>