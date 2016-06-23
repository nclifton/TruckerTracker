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