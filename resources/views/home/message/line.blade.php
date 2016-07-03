<?php
if ($message){
    $id = $message->_id;
    $description = $message->driver->first_name . ' ' . $message->driver->last_name. ' ' . $message->status . ' ';
    switch ($message->status){
        case 'queued':
            $description .= $message->queued_at;
            break;
        case 'sent':
            $description .= $message->sent_at;
            break;
        case 'delivered':
            $description .= $message->delivered_at;
            break;
        case 'received':
            $description .= $message->received_at;
            break;
    }
    $message_text_title = ' title='.$message->message_text.'';
} else {
    $id = '';
    $description = '';
    $message_text_title = '';
}
?>
<li id="message{{$id}}" class="row list_panel_line" style="{{$styleAttr}}"{{$message_text_title}}>
    <button class="btn btn-xs btn-detail open-modal-view-message view-button pull-left"  value="{{$id}}">View</button>
    <span class="line_fluid_column">
        <span class="overflow_ellipsis description">
            {{"$description"}}
        </span>
    </span>
    <button class="btn btn-danger btn-xs btn-delete delete-message pull-right delete-button" value="{{$id}}">
        Delete
    </button>
</li>