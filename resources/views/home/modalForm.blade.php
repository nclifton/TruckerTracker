<div class="modal fade" id="{{$subject}}Modal" tabindex="-1" role="dialog"
     aria-labelledby="{{$subject}}ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="close">
                    <span aria-hidden="true">&times;</span>
                    <span class="hide">close</span>
                </button>
                <h4 class="modal-title" id="{{$subject}}ModalLabel">{{$title}}</h4>
                <div class="modal-body">
                    @include('home.'.$subject.'Form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btn-save-{{$subject}}" value="add">
                        {{$save_button_label}}
                    </button>
                    <input type="hidden" id="{{$subject}}_id" name="id" value="{{$subject_id_value}}">
                </div>
            </div>
        </div>
    </div>
</div>