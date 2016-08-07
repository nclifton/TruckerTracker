/**
 * Created by nclifton on 18/06/2016.
 */
var Common = {
    settings: {
        subscribe_sse: false,
        orgId: false,
        sseUrl: '/sub',
        time_format_hour12: false
    },
    sse: false,

    init: function(){
        this.onUIActions(this.settings);
        this.setupSse(this.settings);
    },

    ajaxSetup: function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
    },

    findElements: function (settings) {
        var findSelectors = function (object) {
            for (var name in object) {
                if (name == 'selectors') {
                    for (var elementName in object['selectors']) {
                        object[elementName] = $(object.selectors[elementName]);
                    }
                } else if (object[name] !== null && typeof object[name] === 'object' && 'selectors' in object[name]) {
                    findSelectors(object[name]);
                }
            }
        };
        findSelectors(settings);
        return settings;
    },

    prepForAdd: function (settings,showModal) {
        settings.submitButton.val('add').text(settings.lang.addButtonLabel);
        settings.form.trigger('reset');
        showModal(settings);
    },

    prepForEdit: function (settings,showModal) {
        $(settings.lineSelector).first().each(function (i, selected) {
            var dataId = $(selected).attr('data');
            Common.ajaxSetup();
            $.get(settings.url + '/' + dataId, function (data) {
                Common.handleGetSuccess(data,settings,showModal);
              }).fail(function (data) {
                Common.handleServerError(data);
            });
        });
    },

    fillForm: function (data, settings) {
        for (var key in data) {
            if (key == '_id') {
                settings.dataIdHolder.val(data[key]);
            } else {
                settings.form.find('[name="' + key + '"]').val(data[key]);
            }
        }
    },

    handleGetSuccess: function (data, settings, showModal) {
        console.log(data);
        settings.form.trigger('reset');
        Common.fillForm(data, settings);
        settings.submitButton.val("update");
        showModal(settings);
    },

    deleteSelection: function (settings) {

        $(settings.lineSelector).each(function (i, selected) {
            var dataId = $(selected).attr('data');
            Common.ajaxSetup();
            $.ajax({
                type: "DELETE",
                url: settings.url + '/' + dataId,
                success: function (data) {
                    console.log(data);
                    $("#"+ settings.classPrefix + dataId).remove();
                    Common.enableDisableControls(settings.classPrefix);
                },
                error: function (data) {
                    Common.handleServerError(data);
                }
            });
        });
    },


    handleServerError: function(data) {
        document.open("text/html", "replace");
        document.write(data.responseText);
        document.close();
    },

    handlePermissionDenied: function() {
        window.alert('Permission Denied');
    },

    handleAjaxError: function(data, settings){
        console.log('Error:', data);
        switch (data.status) {
            case 500:
                Common.handleServerError(data);
                break;
            case 401:
            case 403:
                Common.handlePermissionDenied();
                break;
            case 422:
                Common.handleFormError(data, settings);
        }
    },

    handleFormError: function (data, settings) {
        Common.resetErrorDisplay(settings.form.selector);
        $.each(data.responseJSON, function (index, value) {
            settings.form.find('[name="' + index + '"]').each(function (i, input) {
                $(input)
                    .after('<span class="help-block"><strong>' + value + '</strong></span>')
                    .closest('div.form-group').addClass('has-error');
            });
        });
    },

    resetErrorDisplay: function (form) {
        $(form).find('.help-block').remove();
        $(form).find('.form-group').removeClass('has-error');
    },

    ajaxSaveForm: function (setLineText, settings) {
        Common.ajaxSetup();

        //determine the http verb to use [add=POST], [update=PUT]
        var type = "POST"; //for creating new resource
        var url = settings.url;
        var state = settings.submitButton.val();
        if (state == "update") {
            type = "PUT"; //for updating existing resource
            url += '/' + settings.dataIdHolder.val();
        }

        var formData = settings.form.serializeFormJSON();
        console.log(formData);

        $.ajax({
            type: type,
            url: url,
            data: formData,
            dataType: 'json',
            success: function (data) {
                Common.handleSaveSuccess(data, state, settings, setLineText);
            },
            error: function (data) {
                Common.handleAjaxError(data, settings);
            }
        });
    },


    handleSaveSuccess: function (data, state, settings, setLineText) {
        console.log(data);
        if (state == "add") {
            settings.submitButton.text(settings.lang.saveButtonLabel);
            settings.lineTemplate
                .clone(false)
                .appendTo('#' + settings.classPrefix + '_list')
                .attr("id", settings.classPrefix + data._id)
                .attr("data", data._id)
                .show();
            Common.setupSelect(settings.classPrefix, settings.multiSelect);

        }
        setLineText(settings, data);
        settings.modal.modal('hide');
        settings.form.trigger("reset");
        Common.adjustFluidColumns();
    },

    prependListLine: function (data, settings, setLineText) {
        console.log(data);

        settings.lineTemplate
            .clone(false)
            .insertAfter(settings.lineTemplate)
            .attr("id", settings.lineTemplate.selector.substr(1) + data._id)
            .attr("data", data._id);
        var line = $(settings.lineTemplate.selector + data._id);
        setLineText(line, data);
        line.show();
        line[0].scrollIntoView();

        Common.setupSelect(settings.classPrefix, settings.multiSelect);
        Common.adjustFluidColumns();
    },

    setClickableTooltip: function(selector, content){
        $( selector ).tooltip({
            show: null, // show immediately
            position: { my: "right top", at: "left top" },
            content: content, //from params
            hide: { effect: "" }, //fadeOut
            close: function(event, ui){
                console.log('here');
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
    },


    /**
     *     Adjust width of row elements using the line-fluid-column class.
     *     For this to work properly there must be only one per line
     **/
    removeFluidColumnStyleWidth: function(){
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
    },

    adjustFluidColumns: function(){
        Common.removeFluidColumnStyleWidth();
        $('.row > .line_fluid_column').each(function(){
            var $fluidCol = $(this);
            if ($fluidCol.is(':visible')){
                var fluidWidth = $fluidCol.closest('.row').width();

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
    },

    enableDisableControls: function(classPrefix){
        var selectedSelector = '.' + classPrefix + '_line' + '.selected';
        var controlsSelector =
            '#' + classPrefix + '_controls button.btn-danger, ' +
            '#' + classPrefix + '_controls button.btn-warning, ' +
            '#' + classPrefix + '_controls button.btn-detail';
        if ($(selectedSelector).length) {
            $(controlsSelector).removeAttr('disabled');
        } else {
            $(controlsSelector).attr('disabled', 'disabled')
        }
    },

    setupSelect: function(arg1,multiSelect){
        var classPrefix = (typeof arg1 === 'object') ? arg1.classPrefix : arg1;
        var selectableSelector = '.' + classPrefix + '_line';
        var selectedSelector = selectableSelector + '.selected';
        $(selectableSelector).off('click').click(function () {
            var selected = $(this).hasClass('selected');
            if(multiSelect) {
                $(this).toggleClass('selected');
            } else {
                $(selectedSelector).removeClass('selected');
                if (!selected)
                    $(this).addClass('selected');
             }
            Common.enableDisableControls(classPrefix);
        });
    },

    friendlyDatetime: function(iso8601TimeStr){
        var date = new Date(iso8601TimeStr);
        var timeStr = date.toLocaleTimeString();
        var relDate = relativeDate(date);
        return timeStr + ' (' + relDate + ')';
    },




    handleSseMessageUpdate: function (e) {
        console.log(e);
        MessageDialogue.updateMessageLine($.parseJSON(e.data));
        MessageDialogue.updateConversationMessage($.parseJSON(e.data))
        Common.adjustFluidColumns();
    },

    handleSseMessageReceived: function (e) {
        console.log(e);
        MessageDialogue.addMessageLine($.parseJSON(e.data));
        MessageDialogue.addConversationMessage($.parseJSON(e.data))
        Common.adjustFluidColumns();
    },

    handleSseLocationUpdate: function (e) {
        console.log(e);
        LocationDialogue.updateLocationLine($.parseJSON(e.data));
        Common.adjustFluidColumns();
    },

    handleSseLocationReceived: function (e) {
        console.log(e);
        LocationDialogue.updateLocationLine($.parseJSON(e.data));
        Common.adjustFluidColumns();
    },

    setupSse: function(){

        if (!Common.sse){
            if (!Common.settings.orgId){
                console.log('Sorry No Organisation ID, No SSE!');
                return;
            }
            Common.sse = $.SSE(Common.settings.sseUrl + '/' + Common.settings.orgId, {
                events: {
                    MessageUpdate: function(e) {
                        Common.handleSseMessageUpdate(e);
                    },
                    MessageReceived: function(e) {
                        Common.handleSseMessageReceived(e);
                    },
                    LocationUpdate: function(e) {
                        Common.handleSseLocationUpdate(e);
                    },
                    LocationReceived: function(e) {
                        Common.handleSseLocationReceived(e);
                    }
                }
            });
            console.log('SSE starting');
            Common.sse.start();

        }

    },



    onUIActions: function (settings) {
        Common.adjustFluidColumns();

        var resizeTimer;
        var showTimer;
        $(window).on('resize',function() {
            Common.removeFluidColumnStyleWidth();
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                Common.adjustFluidColumns();
                if ($('#messageDriverModal:visible').length) {
                    MessageDialogue.resetConversationScrollPanel();
                }
            }, 250);
        });

        $('.list_panel_line').on('show', function() {

            Common.removeFluidColumnStyleWidth();

            clearTimeout(showTimer);
            showTimer = setTimeout(function() {
                Common.adjustFluidColumns();

            }, 250);
        });

        if (Common.settings.subscribe_sse){
            Common.setupSse();
        }

        $('#locate_vehicles_collapsible,#message_drivers_collapsible')
            .on('shown.bs.collapse', function(){
            Common.removeFluidColumnStyleWidth();
            Common.adjustFluidColumns();
        });

    }
};
