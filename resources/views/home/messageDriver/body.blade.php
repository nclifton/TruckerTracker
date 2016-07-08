<div id="driver_conversation" class="conversation_container">
    <div class="container_row">
        <div class="message_to_panel">
            <div class="rotation_container">
                <div class="header_text"></div>
            </div>
        </div>
        <div class="conversation_panel">
            <div class="messages_container">
                <div id="conversation_message" class="container_row" style="display: none">
                    <div class="message_text"></div>
                </div>
            </div>
        </div>
        <div class="message_from_panel">
            <div class="rotation_container">
                <div class="header_text">trucker<img src="{{asset('images/Maps-Gps-Receiving-icon.png')}}" style="display:inline-block">tracker</div>
            </div>
        </div>
    </div>
</div>
<form id="messageForm" name="messageForm" class="form-horizontal" novalidate="">
    <div class="form-group error">
        <div class="col-sm-12">
            <textarea class="form-control" id="message_text"
                   name="message_text" placeholder="message" value=""></textarea>
        </div>
    </div>
</form>