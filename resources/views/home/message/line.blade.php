<?php
$id = $message?$message->_id:'';
$first_name = ($message && $message->driver)?$message->driver->first_name:'';
$last_name = ($message && $message->driver)?$message->driver->last_name:'';
$status = $message?$message->status:'';
$sent_at = $message?$message->sent_at:'';
?>
<li id="message{{$id}}" class="row" style="{{$styleAttr}}">
    <span class="col-md-2 col-xs-2 col-sm-2 col-lg-2">
        <button class="btn btn-xs btn-detail open-modal-view-message" value="{{$id}}">View</button>
    </span>
    <span class="name col-md-3 col-xs-3 col-sm-3 col-lg-3">
        <span class="first_name">
            {{$first_name}}
        </span>
        <span class="last_name">
            {{$last_name}}
        </span>
    </span>
    <span class="message-status col-md-1 col-xs-1 col-sm-1 col-lg-1" >
        {{$status}}
    </span>
    <span class="message-sent_at col-md-4 col-xs-4 col-sm-4 col-lg-4">
        {{$sent_at}}
    </span>
    <span class="col-md-2 col-xs-2 col-sm-2 col-lg-2">
        <button class="btn btn-danger btn-xs btn-delete delete-message pull-right" value="{{$id}}">
            Delete
        </button>
    </span>
</li>