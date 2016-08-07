
/**
 * Created by nclifton on 29/05/2016.
 */

var MessageDialogue ={

    settings: {
        classPrefix:    'message',
        multiSelect:    true,
        lineSelector:   '.message_line.selected',
        url:            '/driver/message',
        selectors: {
            deleteButton:   '#btn-delete-messages',
            form:           '#messageForm',
            submitButton:   '#btn-save-messageDriver',
            dataIdHolder:   '#messageDriver_id',
            modal:          '#messageDriverModal',
            lineTemplate:   '#message',
            list:           '#message_list'
        },
        conversation: {
            selectors: {
                container:          '#driver_conversation',
                messages:           '#driver_conversation .messages_container',
                toHeader:           '#driver_conversation .message_to_panel .header_text',
                pane:               '#driver_conversation .conversation_panel',
                fromPanel:          '#driver_conversation .message_from_panel',
                lineTemplate:       '#conversation_message'
            },
            jScrollPaneSettings: {
                maintainPosition: true,
                stickToBottom: true,
                horizontalGutter: 20
            }
        },
    },

    init: function () {
        this.settings = Common.findElements(this.settings);
        this.onUIActions(this.settings);
        return this;
    },

    prepForMessage: function (driverId){
        var settings = MessageDialogue.settings;
        settings.form.trigger('reset');
        settings.submitButton.val("send");
        settings.dataIdHolder.val(driverId);
        settings.modal.modal('show');
    },

    resetConversationScrollPanel: function () {
        var settings = MessageDialogue.settings.conversation;

        var element = settings.pane.jScrollPane(settings.jScrollPaneSettings);
        var api = element.data('jsp');
        api.reinitialise();
        if (api.getIsScrollableV()){
            settings.fromPanel.css('right',35);
            api.scrollToBottom();
        } else{
            settings.fromPanel.css('right',16);
        }
    },

    addMessageToConversation: function (message) {
        var settings = MessageDialogue.settings.conversation;
        var $msg = settings.lineTemplate
            .clone(false)
            .appendTo(settings.messages)
            .attr("id", "conversation_message" + message._id);
        $msg.find(".message_container")
            .addClass(message.status);
        $msg.find(".message_text")
            .text(message.message_text);
        $msg.find(".datetime")
            .text(Common.friendlyDatetime(message[message.status + '_at']));
        $msg.show();
        MessageDialogue.resetConversationScrollPanel();
    },

    requestConversationSuccess: function (data) {
        var settings = MessageDialogue.settings.conversation;
        settings.toHeader
            .empty()
            .text(data.first_name + ' ' + data.last_name);
        settings.messages.children(':visible').remove();
        $.each(data.messages, function () {
            MessageDialogue.addMessageToConversation(this);
        });
    },

    requestConversation: function (settings) {
        Common.ajaxSetup();

        var driver_id = settings.dataIdHolder.val();

        $.get('/driver/' + driver_id + '/conversation', function (data) {
            //success data
            console.log(data);
            MessageDialogue.requestConversationSuccess(data);

        }).fail(function (data) {
            Common.handleAjaxError(data,settings);
        });
    },

    updateMessageLine: function (data) {
        var settings = MessageDialogue.settings;
        var line = $(settings.lineTemplate.selector + data._id);
        line.find('span.status').text(data.status);
        line.find('span.status_at').text(data[data.status + '_at']);
    },

    setLineText: function (line, data) {
        line.find('span.first_name').text(data.driver.first_name);
        line.find('span.last_name').text(data.driver.last_name);
        line.find('span.status').text(data.status);
        line.find('span.status_at').text(data[data.status + '_at']);
        line.attr('title', data.message_text);
    },

    addMessageLine: function (data) {
        var settings = MessageDialogue.settings;
        if ($(settings.lineTemplate.selector + data._id).length){
            return MessageDialogue.updateMessageLine(data);
        }
        Common.prependListLine(data,settings,MessageDialogue.setLineText);
    },

    addConversationMessage: function (data) {
        var settings = MessageDialogue.settings;

        var css = settings.modal.css('display');
        if (css == 'block'){
            MessageDialogue.addMessageToConversation(data);
            MessageDialogue.resetConversationScrollPanel();
        }
    },

    updateConversationMessage: function (data) {
        var settings = MessageDialogue.settings;

        var css = settings.modal.css('display');
        if (css == 'block'){
            var line = $(settings.conversation.lineTemplate.selector + data._id);
            line.find(".message_container")
                .removeClass('queued sent delivered')
                .addClass(data.status);
            line.find(".datetime").text(Common.friendlyDatetime(data[data.status+'_at']));

            MessageDialogue.resetConversationScrollPanel();

        }
    },

    sendMessageSuccess: function (data,settings) {
        console.log(data);
        settings.form.trigger("reset");
        MessageDialogue.addMessageLine(data);
        MessageDialogue.addConversationMessage(data);

    },

    sendMessage: function (settings) {
        Common.ajaxSetup();

        var formData = settings.form.serializeFormJSON();

        var driver_id = settings.dataIdHolder.val();
        console.log(formData);

        $.ajax({

            type: "POST",
            url: '/driver/' + driver_id + '/message/',
            data: formData,
            dataType: 'json',
            success: function (data) {
                MessageDialogue.sendMessageSuccess(data,settings);
            },
            error: function (data) {
                Common.handleAjaxError(data, settings);
            }
        });
    },

    onUIActions: function(settings){

        Common.setupSelect(settings.classPrefix,settings.multiSelect);

        settings.deleteButton.click(function (e) {
            e.preventDefault();
            Common.deleteSelection(settings);
        });

        // This is shown on the message modal used to send messages
        // we'll populate the conversation panel when the message modal is opened.
        settings.modal.on('shown.bs.modal', function() {
            MessageDialogue.requestConversation(settings);
        });

        settings.form.on("reset", function (e) {
            Common.resetErrorDisplay(e.target);
        });

        //send message to driver
        settings.submitButton.click(function (e) {
            e.preventDefault();
            MessageDialogue.sendMessage(settings);
        });

    }



};
