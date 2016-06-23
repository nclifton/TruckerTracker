<?php
$id = $message?$message->_id:'';
$first_name = ($message && $message->driver)?$message->driver->first_name:'';
$last_name = ($message && $message->driver)?$message->driver->last_name:'';
$status = $message?$message->status:'';
$sent_at = $message
        ?($message->sent_at
                ?$message->sent_at
                :($message->queued_at
                        ?$message->queued_at
                        :''
                )
        )
        :'';
$queued_at = $message?$message->queued_at:'';
$message_text_title = $message?' title='.$message->message_text.'':'';
?>
<li id="message{{$id}}" class="row" style="{{$styleAttr}}"{{$message_text_title}}>
    <span class="view-button pull-left">
        <button class="btn btn-xs btn-detail open-modal-view-message" value="{{$id}}">View</button>
    </span>
    <span class="">
        <span class="name">
            <span class="first_name">
                {{$first_name}}
            </span>
            <span class="last_name">
                {{$last_name}}
            </span>
        </span>
        <span class="status" >
            {{$status}}
        </span>
        <span class="sent_at">
            {{$sent_at}}
        </span>
    </span>
    <span class="delete-button pull-right">
        <button class="btn btn-danger btn-xs btn-delete delete-message pull-right" value="{{$id}}">
            Delete
        </button>
    </span>
</li>