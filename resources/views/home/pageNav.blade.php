@if($org)
    @can('view-organisation',$org)
        <li>
            <a id="btn-edit-org" name="btn-edit-org" href="#" class="open-modal-org" data="{{ $org->_id}}">
                Edit Organisation
            </a>
        </li>
    @endcan
    @can('add-vehicle',$org)
        <li>
            <a id="btn-add-vehicle" name="btn-add-vehicle" href="#">
                Add Vehicle
            </a>
        </li>
    @endcan
    @can('add-driver',$org)
        <li>
            <a id="btn-add-driver" name="btn-add-driver" href="#">
                Add Driver
            </a>
        </li>
    @endcan
@else
    <li>
        <a id="btn-add-org" name="btn-add-org" href="#" class="open-modal-org" data="">
            Add Organisation
        </a>
    </li>
    <li>
        <a id="btn-add-vehicle" name="btn-add-vehicle" href="#" style="display:none" >
            Add Vehicle
        </a>
    </li>
    <li>
        <a id="btn-add-driver" name="btn-add-driver" href="#" style="display:none" >
            Add Driver
        </a>
    </li>
@endif
