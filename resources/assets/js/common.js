/**
 * Created by nclifton on 18/06/2016.
 */
(function ($) {
    $.fn.serializeFormJSON = function () {

        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
})(jQuery);


function handleStatusCode500(data) {
    var newDoc = document.open("text/html", "replace");
    newDoc.write(data.responseText);
    newDoc.close();
}
function handleStatusCode403() {
    window.alert('Permission denied');
}
function handleStatusCode401() {
    window.alert('Permission denied');
}
function handleAjaxError(data) {
    console.log('Error:', data);
    if (data.status == 500) {
        handleStatusCode500(data);
    } else if (data.status == 403) {
        handleStatusCode403();
    }
}
function timeConverter(UNIX_timestamp){
    var a = new Date(UNIX_timestamp * 1000);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var year = a.getFullYear();
    var month = months[a.getMonth()];
    var date = a.getDate();
    var hour = a.getHours();
    var min = a.getMinutes();
    var sec = a.getSeconds();
    var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
    return time;
}

function setClickableTooltip(target, content){
    $( target ).tooltip({
        show: null, // show immediately 
        position: { my: "right top", at: "left top" },
        content: content, //from params
        hide: { effect: "" }, //fadeOut
        close: function(event, ui){
            ui.tooltip.hover(
                function () {
                    $(this).stop(true).fadeTo(400, 1);
                },
                function () {
                    $(this).fadeOut("400", function(){
                        $(this).remove();
                    })
                }
            );
        }
    });
}

/**
 *     Adjust width of row elements using the line-fluid-column class.
 *     For this to work properly there must be only one per line
 **/
function remove_fluid_column_style_widths(){
    $('.row > .line_fluid_column').each(function(){
        var $fluidCol = $(this);
        if ($fluidCol.is(':visible')) {
            $fluidCol.siblings().each(function () {
                $(this).css('width', '');
            });
            $fluidCol.css('width', '');
            $fluidCol.children().each(function () {
                $(this).css('width', '');
            });
        }
    });
}

function adjust_fluid_columns () {
    remove_fluid_column_style_widths();
    $('.row > .line_fluid_column').each(function(){
        var $fluidCol = $(this);
        if ($fluidCol.is(':visible')){
            var fluidWidth = $fluidCol.closest('.row').width();
            //$fcol.closest('.row').width(colWidth);
            $fluidCol.siblings().each(function(){
                var $sib = $(this);
                if ($sib.is(':visible')) {
                    var sibWidth = $sib.width();
                    $sib.width(sibWidth);
                    fluidWidth -= sibWidth;
                }
            });
            $fluidCol.width(fluidWidth);
            var width = 0;
            var ofWidth = 0;
            // how wide can we make the overflow_container? will be fluid col width less fixed width col widths
            // colWidth is fluid col width
            $fluidCol.children().each(function(){
                var $this = $(this);
                var w = $this.width();
                var pw = parseInt($this.css("padding-right")) + parseInt($this.css("padding-left"));
                var mw = parseInt($this.css("margin-right")) + parseInt($this.css("margin-left"));
                $this.width(w + pw + mw);
                width += w + pw + mw;
                if ($this.hasClass('overflow_container')){
                    ofWidth = 0;
                    $this.children().each(function(){
                        var pw = parseInt($(this).css("padding-right")) + parseInt($(this).css("padding-left"));
                        var mw = parseInt($(this).css("margin-right")) + parseInt($(this).css("margin-left"));
                        ofWidth += $(this).width() + pw + mw;
                    });
                }
            });
            var fixedWidth = width - ofWidth;
            if (fixedWidth < fluidWidth){
                var $overflowContainer = $fluidCol.children('.overflow_container').first();
                var ofcWidth = fluidWidth - fixedWidth;
                $overflowContainer.width(ofcWidth);
                if ((ofWidth + 10) > ofcWidth) {
                    $overflowContainer.children().each(function () {
                        $(this).addClass('overflow_ellipsis_active');
                    });
                } else {
                    //$overflowContainer.width('auto');
                    $fluidCol.find('.overflow_ellipsis_active').removeClass('overflow_ellipsis_active');
                }

            }

        }
    });
}

function update_location_line(data) {
    var loc = $('#location' + data._id);
    loc.find('span.status').text(data.status);
    var status_at = data.queued_at;
    switch (data.status) {
        case 'sent':
            status_at = data.sent_at;
            break;
        case 'delivered':
            status_at = data.delivered_at;
            break;
        case 'received':
            status_at = data.received_at;
            loc.find('button.open-modal-location-view').val(data._id);
            loc.find('.view-button').show();
            break;
    }
    loc.find('span.status_at').text(status_at);
}

function delete_selected_message() {
    delete_selected('message', '/driver/message/');
}

function delete_selected_location(){
    delete_selected('location','/vehicle/location/');
}

function delete_selected(classPrefix,urlPrefix) {

    var $item = $('.' + classPrefix + '_line.selected').first()
    if ($item.length) {
        var item_id = $item.attr('data');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "DELETE",
            url: urlPrefix + item_id,
            success: function (data) {
                console.log(data);
                $("#" + classPrefix + item_id).remove();
                var selectedSelector = '.' + classPrefix + '_line' + '.selected';
                $(selectedSelector).removeClass('selected');
                enable_disable_controls(classPrefix);
                delete_selected(classPrefix,urlPrefix);
            },
            error: function (data) {
                handleAjaxError(data);
            }
        });
    }
}



function enable_disable_controls(classPrefix) {
    var selectedSelector = '.' + classPrefix + '_line' + '.selected';
    var controlsSelector = '#' + classPrefix + '_controls button.btn-danger, button.btn-warning, button.btn-detail';
    if ($(selectedSelector).length) {
        $(controlsSelector).removeAttr('disabled');
    } else {
        $(controlsSelector).attr('disabled', 'disabled')
    }
}
function setup_select(classPrefix,onlyOne) {

    var selectableSelector = '.' + classPrefix + '_line';
    var selectedSelector = selectableSelector + '.selected';
    $(selectableSelector).off('click').click(function () {
        var selected = $(this).hasClass('selected');
        if(onlyOne){
            $(selectedSelector).removeClass('selected');
            if (!selected)
                $(this).addClass('selected');
        } else {
            $(this).toggleClass('selected');
        }
        enable_disable_controls(classPrefix);

    });
}
function add_message_line(data) {
    if ($('#message' + data._id).length){
        return update_message_line(data);
    }

    var msg = $('#message')
        .clone(false)
        .appendTo('#message_list')
        .attr("id", "message" + data._id)
        .attr('data',data._id);
    msg.find('button.open-modal-view-message').val(data._id);
    msg.find('button.delete-message').val(data._id);
    msg.find('span.first_name').text(data.driver.first_name);
    msg.find('span.last_name').text(data.driver.last_name);
    msg.find('span.status').text(data.status);
    var status_at = data.queued_at;
    switch (data.status) {
        case 'sent':
            status_at = data.sent_at;
            break;
        case 'delivered':
            status_at = data.delivered_at;
            break;
        case 'received':
            status_at = data.received_at;
            break;
    }
    msg.find('span.status_at').text(status_at);
    msg.attr('title', data.message_text);
    msg.show();
    setup_select('message');
    
}

function update_message_line(data) {
    var msg = $('#message' + data._id);
    msg.find('span.status').text(data.status);
    var status_at = data.queued_at;
    switch (data.status) {
        case 'sent':
            status_at = data.sent_at;
            break;
        case 'delivered':
            status_at = data.delivered_at;
            break;
    }
    msg.find('span.status_at').text(status_at);
}


function jScrollPaneSettings() {
    var contentWidth = $('messageDriveModal').innerWidth();
    return {
        maintainPosition: true,
        stickToBottom: true,
        horizontalGutter: 20
    }
}

function reset_conversation_scrollPane() {

    var $driverConversation = $('#driver_conversation');
    var $pane = $driverConversation.find('.conversation_panel');
    $pane.jScrollPane(jScrollPaneSettings());
    var api = $pane.data('jsp');
    api.reinitialise();
    var $fromPanel = $driverConversation.find('.message_from_panel').first();
    if (api.getIsScrollableV()){
        $fromPanel.css('right',35);
        api.scrollToBottom();
    } else{
        $fromPanel.css('right',16);
    }
}
function update_conversation_message(data) {
    if ($('#messageDriverModal:visible').length){
        var $conversationContainer = $('#driver_conversation');
        var $messagesContainer = $conversationContainer.find('.messages_container');
        var $msg = $messagesContainer.find('#conversation_message' + data._id);

        $msg.find(".message_text")
            .removeClass('queued sent delivered')
            .addClass(data.status);

        reset_conversation_scrollPane();

    }
}


function add_message_to_conversation($messagesContainer, msgdata) {
    var $msg = $messagesContainer.find('#conversation_message')
        .clone(false)
        .appendTo($messagesContainer).attr("id", "conversation_message" + msgdata._id);
    $msg.find(".message_text").text(msgdata.message_text).addClass(msgdata.status);
    $msg.show();


}


function add_conversation_message(data) {
    if ($('#messageDriverModal:visible').length){
        var $conversationContainer = $('#driver_conversation');
        var $messagesContainer = $conversationContainer.find('.messages_container');
        add_message_to_conversation($messagesContainer, data);
        reset_conversation_scrollPane();
    }
}

var truckerTrackerSse;

function setup_sse(){
    if (!truckerTrackerSse){
        if (!organisation_id){
            console.log('No Organisation ID - No SSE!');
            return;
        }
        truckerTrackerSse = $.SSE('/sub/'+organisation_id, {
            events: {
                MessageUpdate: function(e) {
                    console.log(e);
                    update_message_line($.parseJSON(e.data));
                    update_conversation_message($.parseJSON(e.data))
                    adjust_fluid_columns();
                },
                MessageReceived: function(e) {
                    console.log(e);
                    add_message_line($.parseJSON(e.data));
                    add_conversation_message($.parseJSON(e.data))
                    adjust_fluid_columns();
                },
                LocationUpdate: function(e) {
                    console.log(e);
                    update_location_line($.parseJSON(e.data));
                    adjust_fluid_columns();
                },
                LocationReceived: function(e) {
                    console.log(e);
                    update_location_line($.parseJSON(e.data));
                    adjust_fluid_columns();
                }
            }
        });

        truckerTrackerSse.start();

    }
}


/**
 * show hide event
 */
(function ($) {
    $.each(['show', 'hide'], function (i, ev) {
        var el = $.fn[ev];
        $.fn[ev] = function () {
            this.trigger(ev);
            return el.apply(this, arguments);
        };
    });
})(jQuery);


$(document).ready(function () {
    var resizeTimer;
    var showTimer;

    adjust_fluid_columns();

    $(window).on('resize',function(e) {
        remove_fluid_column_style_widths();
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            adjust_fluid_columns();
            if ($('#messageDriverModal:visible').length) {
                reset_conversation_scrollPane();
            }
        }, 250);
    });

    $('.list_panel_line').on('show', function() {

        remove_fluid_column_style_widths();

        clearTimeout(showTimer);
        showTimer = setTimeout(function() {
            adjust_fluid_columns();

        }, 250);
    });
    
    if (subscribe_sse){
        setup_sse();
    }

    $('#locate_vehicles_collapsible,#message_drivers_collapsible').on('shown.bs.collapse', function(){
        remove_fluid_column_style_widths();
        adjust_fluid_columns();
    })

});