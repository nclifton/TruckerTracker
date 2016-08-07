<?php
$id = '';
$first_name = '';
$last_name = '';
$status = '';
$status_at = '';
$message_text_title = '';
if ($message){
    $id = $message->_id;
    if ($message->driver){
        $first_name = $message->driver->first_name;
        $last_name = $message->driver->last_name;
        $status = $message->status;
    } else {
        $first_name = 'unknown';
        $last_name = 'driver';
    }
    switch ($message->status){
        case 'queued':
            $status_at = $message->queued_at;
            break;
        case 'sent':
            $status_at = $message->sent_at;
            break;
        case 'delivered':
            $status_at = $message->delivered_at;
            break;
        case 'received':
            $status_at = $message->received_at;
            break;
    }
    $message_text_title = ' title="'. $message->message_text.'"';
}
?>
<li id="message{{$id}}" class="row list_panel_line message_line cursor-pointer" style="{{$styleAttr}}"{!! $message_text_title !!}
    data="{{$id}}">
    <span class="line_fluid_column">
        <span class="name">
            <span class="first_name">{!! $first_name !!}</span>
            <span class="last_name">{!! $last_name !!}</span>
        </span>
        <span class="status">{!! $status !!}</span>
        <span class="overflow_container">
            <span class="overflow_ellipsis status_at">{!! $status_at !!}</span>
        </span>
    </span>
</li>