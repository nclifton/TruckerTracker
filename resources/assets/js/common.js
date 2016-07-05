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
                    var sibWidth = $sib.outerWidth(true);
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
    
});