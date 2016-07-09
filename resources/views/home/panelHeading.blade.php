
<div class="panel-heading">
    <span id="heading_org_name">
        {{$org?$org->name:''}}
    </span>
    <span>
        Dashboard
    </span>
    @if($org)

    @else
        <button id="btn-edit-org" name="btn-edit-org"
                class="btn btn-warning btn-xs btn-detail open-modal-org pull-right"
                value="" style="display:none;">
            Edit Organisation
        </button>
        <button id="btn-add-org" name="btn-add-org"
                class="btn btn-primary btn-xs pull-right"
                value="">
            Add Organisation
        </button>
    @endif


</div>
