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

hoverForMoreOptions =
{
    speed: 60.0,		// Measured in pixels-per-second
    loop: true,		// Scroll to the end and stop, or loop continuously?
    gap: 20,		// When looping, insert this many pixels of blank space
    target: false,		// Hover on this CSS selector instead of the text line itself
    removeTitle: false,	// By default, remove the title attribute, as a tooltip is redundant
    snapback: true,		// Animate when de-activating, as opposed to instantly reverting
    addStyles: true,	// Auto-add CSS; leave this on unless you need to override default styles
    alwaysOn: false,	// If you're insane, you can turn this into a <marquee> tag. (Please don't.)

    // In case you want to alter the events which activate and de-activate the effect:
    startEvent: "mouseenter",
    stopEvent: "mouseleave"
};

/**
 *     Adjust width of row elements using the line-fluid-column class.
 *     For this to work properly there must be only one per line
 **/
function adjust_fluid_columns () {

    $('.row > .line_fluid_column').each(function(){
        var $fcol = $(this);
        if ($fcol.is(':visible')){
            var colWidth = $fcol.closest('.row').innerWidth() - 6;
            $fcol.siblings().each(function(){
                var $sib = $(this);
                if ($sib.is(':visible')) {
                    var sibWidth = $sib.outerWidth(true) + 1;
                    colWidth -= sibWidth;
                }
            });
            var width = 0;
            var fixedWidth = 0
            $fcol.children().each(function(){
                var $this = $(this);
                var w = $this.outerWidth(true) + 9 ;
                width += w;
                if ( ! $this.hasClass('overflow_container')){
                    fixedWidth += w ;
                }
            });
            var $overflowContainer = $fcol.children('.overflow_container').first();
            if (width > colWidth) {
                $overflowContainer.width(colWidth - fixedWidth);
                $overflowContainer.children().each(function(){
                    $(this).addClass('overflow_ellipsis_active');
                });
            } else {
                $overflowContainer.width('auto');
                $fcol.find('.overflow_ellipsis_active').removeClass('overflow_ellipsis_active');
            }
            $fcol.width(colWidth);
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

//delete message and remove it from list
function setup_delete_message() {
    $('button.delete-message').click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        e.preventDefault();

        var message_id = $(this).val();

        $.ajax({

            type: "DELETE",
            url: '/driver/message/' + message_id,
            success: function (data) {
                console.log(data);
                $("#message" + message_id).remove();
            },
            error: function (data) {
                handleAjaxError(data);
            }
        });
    });
}

//delete location and remove it from list
function setup_delete_location() {
    $('button.delete-location').click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        e.preventDefault();
        var location_id = $(this).val();
        $.ajax({
            type: "DELETE",
            url: '/vehicle/location/' + location_id,
            success: function (data) {
                console.log(data);
                $("#location" + location_id).remove();
            },
            error: function (data) {
                handleAjaxError(data);
            }
        });
    });
}

function add_message_line(data) {
    if ($('#message' + data._id).length){
        return update_message_line(data);
    }

    var msg = $('#message').clone(false).appendTo('#message_list').attr("id", "message" + data._id);
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
    msg[0].scrollIntoView();
    setup_delete_message()
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

function setup_sse(){
    if (!sse){
        if (!organisation_id){
            console.log('No Organisation ID - No SSE!');
            return;
        }
        var sse = $.SSE('/sub/'+organisation_id, {
            onOpen: function(e) {
                console.log("SSE Open");
                console.log(e);
            },
            onEnd: function(e) {
                console.log("SSE Closed");
                console.log(e);
            },
            onError: function(e) {
                console.log("SSE Error");
                console.log(e);
            },
            onMessage: function(e){
                console.log("SSE Message");
                console.log(e);
            },
            events: {
                MessageUpdate: function(e) {
                    console.log(e);
                    update_message_line($.parseJSON(e.data));
                    adjust_fluid_columns();
                },
                MessageReceived: function(e) {
                    console.log(e);
                    add_message_line($.parseJSON(e.data));
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
        sse.start();

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
    var sse;

    adjust_fluid_columns();

    $(window).on('resize',function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            adjust_fluid_columns();
        }, 250);
    });

    $('.list_panel_line').on('show', function() {
        clearTimeout(showTimer);
        showTimer = setTimeout(function() {
            adjust_fluid_columns();
        }, 250);
    });
    
    if (subscribe_sse){
        setup_sse();
    }

});