<div class="modal fade" id="{{str_replace('.','-',$subject)}}Modal" tabindex="-1" role="dialog"
     aria-labelledby="{{str_replace('.','-',$subject)}}ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="close">
                    <span aria-hidden="true">&times;</span>
                    <span class="hide">close</span>
                </button>
                <h4 class="modal-title" id="{{str_replace('.','-',$subject)}}ModalLabel">{{$title}}</h4>
                <div class="modal-body">
                    @include('home.'.$subject.'.body')
                </div>
                <div class="modal-footer">
                    @if(isset($save_button_label))
                    <button type="button" class="btn btn-primary" id="btn-save-{{str_replace('.','-',$subject)}}" value="add">
                        {!! $save_button_label !!}
                    </button>
                    @endif
                    <input type="hidden" id="{{str_replace('.','-',$subject)}}_id" name="id" value="{{$subject_id_value}}">
                </div>
            </div>
        </div>
    </div>
</div>