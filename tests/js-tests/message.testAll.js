describe('message.js test all', function () {

    var fixtures = jasmine.getFixtures();
    var styleFixtures = jasmine.getStyleFixtures();
    var settings;
    var click;
    var modalShowEvent;
    var formResetEvent;
    var ajaxSetupParams;

    beforeAll(function () {
        fixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
        styleFixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
    });

    beforeEach(function () {
        fixtures.load('messageControls.html');
        fixtures.appendLoad('messageLine.html');
        fixtures.appendLoad('messageModal.html');
        fixtures.appendLoad('messageForm.html');
        fixtures.appendLoad('messageConversation.html');

        $('.modal-backdrop').remove();

        $('head').append('<meta name="_token" content="dummy_csrf_token" />');
        ajaxSetupParams = {
            headers: {
                'X-CSRF-TOKEN': 'dummy_csrf_token'
            }
        };

        spyOn(console,'log');

    });
    afterEach(function () {
        $('.modal-backdrop').remove();
    });

    describe('init functions',function () {

        beforeEach(function () {
            settings = Common.findElements(MessageDialogue.settings);
        });

        describe('Common.findElements',function () {

            it('should resolve all the selectors including nested',function () {

                expect(settings.deleteButton).toEqual($(settings.selectors.deleteButton));
                expect(settings.form).toEqual($(settings.selectors.form));
                expect(settings.submitButton).toEqual($(settings.selectors.submitButton));
                expect(settings.dataIdHolder).toEqual($(settings.selectors.dataIdHolder));
                expect(settings.modal).toEqual($(settings.selectors.modal));

            })

        });
        describe('onUIActions', function () {
            beforeEach(function () {
                spyOn(Common, 'setupSelect');
                spyOn(Common, 'deleteSelection');
                click = $.Event('click');
                modalShowEvent = $.Event('shown.bs.modal');
                formResetEvent = $.Event('reset');
                spyOn(click, 'preventDefault');
                spyOn(MessageDialogue,'requestConversation');
                spyOn(Common, 'resetErrorDisplay');
                spyOn(MessageDialogue,'sendMessage');

                MessageDialogue.onUIActions(settings);
            });

            it('should have delegated to the function to setup the selectable lines', function () {
                expect(Common.setupSelect).toHaveBeenCalledWith(settings.classPrefix, true);
            });

            it('should bind delete selected messages action to clicking the delete button', function () {
                settings.deleteButton.trigger(click);
                expect(Common.deleteSelection).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();
            });

            it('should bind action to request conversation to modal shown event on the message modal window', function () {
                settings.modal.trigger(modalShowEvent);
                expect(MessageDialogue.requestConversation).toHaveBeenCalledWith(settings);
            });

            it('should bind reset error display action to form reset event', function () {
                $(settings.form).trigger(formResetEvent);
                expect(Common.resetErrorDisplay).toHaveBeenCalledWith(settings.form[0]);
            });

            it('should bind send message action to clicking the dialogue submit button', function () {
                settings.submitButton.trigger(click);
                expect(MessageDialogue.sendMessage).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();
            });

        });

    });

    describe('initialised Message Dialogue',function () {

        beforeEach(function () {
            settings = MessageDialogue.init().settings;
        });

        describe('requestConversation', function () {
            var data;
            beforeEach(function () {
                spyOn(Common,'ajaxSetup');
                settings.dataIdHolder.val('0987654321');
                data = {
                    messages:[
                        {
                            message_text:'message one'
                        },
                        {
                            message_text:'message two'
                        }
                    ]
                }

            });

            describe('successful', function () {

                beforeEach(function () {

                    spyOn($,'get').and.callFake(function (url, fn) {
                        var d = $.Deferred();
                        d.resolve(data);
                        fn(data, settings);
                        return d.promise();
                    });
                    spyOn(MessageDialogue,'requestConversationSuccess');

                    MessageDialogue.requestConversation(settings);

                });
                it('should use jquery.get to request all messages sent to and received from the selected driver', function () {
                    expect(Common.ajaxSetup).toHaveBeenCalled();
                    expect($.get.calls.mostRecent().args[0]).toEqual('/driver/0987654321/conversation');
                });

                it('should, if request is successful, call method to handle success', function () {
                    expect(console.log).toHaveBeenCalledWith(data);
                    expect(MessageDialogue.requestConversationSuccess).toHaveBeenCalledWith(data);
                });
            });

            describe('fails', function () {

                beforeEach(function () {

                    spyOn($,'get').and.callFake(function (url, fn) {
                        var d = $.Deferred();
                        d.reject(data);
                        return d.promise();
                    });
                    spyOn(Common,'handleAjaxError');

                    MessageDialogue.requestConversation(settings);

                });

                it('should, if request is successful, call method to handle success', function () {
                    expect(Common.handleAjaxError).toHaveBeenCalledWith(data,settings);
                });
            });

        });

        describe('requestConversationSuccess',function () {
            var data;
            beforeEach(function () {
                data = {
                    first_name: 'Driver',
                    last_name: 'One',
                    messages:[
                        {
                            _id:            'msg0987654321',
                            message_text:   'message one'
                        },
                        {
                            _id:            'msg1234567890',
                            message_text:   'message two'
                        }
                    ]
                }

            });
            it('should setup the conversation panel', function () {

                spyOn(MessageDialogue,'addMessageToConversation');

                expect(settings.conversation.toHeader).toHaveText('some text');
                expect($('#conversation_messageX1')).toExist();

                MessageDialogue.requestConversationSuccess(data);

                expect(settings.conversation.toHeader).toHaveText('Driver One');
                expect($('#conversation_messageX1')).not.toExist();
                expect(MessageDialogue.addMessageToConversation).toHaveBeenCalledTimes(2);
                expect(MessageDialogue.addMessageToConversation.calls.argsFor(0)[0])
                    .toEqual(data.messages[0]);
                expect(MessageDialogue.addMessageToConversation.calls.argsFor(1)[0])
                    .toEqual(data.messages[1]);

            });


        });

        describe('sendMessage', function () {

            var data;
            var responseData;

            beforeEach(function () {
                spyOn(Common,'ajaxSetup');
                data = {
                    message_text: 'hello'
                };
                spyOn(settings.form,'serializeFormJSON').and.returnValue(data);
                settings.dataIdHolder.val('0987654321');
            });

            describe('jquery ajax success', function () {
                beforeEach(function () {
                    responseData = {
                        _id: '1234567890',
                        message_text: 'hello'
                    };
                    spyOn($,'ajax').and.callFake(function (options) {
                        options.success(responseData);
                    });
                    spyOn(MessageDialogue,'sendMessageSuccess');
                    MessageDialogue.sendMessage(settings);
                });

                it('should setup jquery ajax', function () {
                    expect(Common.ajaxSetup).toHaveBeenCalled();
                });

                it('should have called the serialise form data into JSON jquery extension', function () {
                    expect(settings.form.serializeFormJSON).toHaveBeenCalled();
                });

                it('should have logged the form data', function () {
                    expect(console.log).toHaveBeenCalledWith(data);
                });

                it('should provide the correct parameters for the ajax call', function () {
                    expect($.ajax.calls.mostRecent().args[0].type).toEqual('POST');
                    expect($.ajax.calls.mostRecent().args[0].url).toEqual('/driver/0987654321/message/');
                    expect($.ajax.calls.mostRecent().args[0].data).toEqual(data);
                    expect($.ajax.calls.mostRecent().args[0].dataType).toEqual('json');
                });
                it('should have called the success method', function () {
                    expect(MessageDialogue.sendMessageSuccess)
                        .toHaveBeenCalledWith(responseData,settings);
                });

            });


            it('should call the error method', function () {
                responseData = 'error';
                spyOn($,'ajax').and.callFake(function (options) {
                    options.error(responseData);
                });
                spyOn(Common,'handleAjaxError');

                MessageDialogue.sendMessage(settings);

                expect(Common.handleAjaxError).toHaveBeenCalledWith(responseData,settings);

            });

        });

        describe('handle driver message data returned from server', function () {
            var data;
            beforeEach(function () {

                data = {
                    _id: '1234567890',
                    message_text: 'hello',
                    status: 'queued',
                    queued_at: '2016-08-01T13:56:58.99',
                    driver: {
                        _id: '5647839201',
                        first_name: 'Driver',
                        last_name: 'One'
                    }
                };

            });

            describe('sendMessageSuccess', function () {
                var formResetEvent;
                beforeEach(function () {

                    spyOn(MessageDialogue,'addMessageLine');
                    spyOn(MessageDialogue,'addConversationMessage');
                    formResetEvent = spyOnEvent(settings.form,'reset');

                    MessageDialogue.sendMessageSuccess(data,settings);
                });
                it('should log the response', function () {
                    expect(console.log).toHaveBeenCalledWith(data);
                });

                it('should reset the message form', function () {
                    expect(formResetEvent).toHaveBeenTriggered();
                });

                it('should add the message to the message list panel', function () {
                    expect(MessageDialogue.addMessageLine).toHaveBeenCalledWith(data);
                });

                it('should add the message to the conversation panel if its visible', function () {
                    expect(MessageDialogue.addConversationMessage).toHaveBeenCalledWith(data);
                });

            });

            describe('addConversationMessage', function () {
                beforeEach(function () {

                    spyOn(MessageDialogue,'addMessageToConversation');
                    spyOn(MessageDialogue,'resetConversationScrollPanel');
                    spyOn(settings.modal,'css');
                });

                it('should if conversation panel is displayed, add the message to the conversation and reset the scroll panel', function () {
                    settings.modal.css.and.returnValue('block');
                    MessageDialogue.addConversationMessage(data);

                    expect(settings.modal.css).toHaveBeenCalledWith('display');
                    expect(MessageDialogue.addMessageToConversation).toHaveBeenCalledWith(data);
                    expect(MessageDialogue.resetConversationScrollPanel).toHaveBeenCalled();

                });

                it('should do not call the methods if the conversation panel is not currently displayed', function () {
                    settings.modal.css.and.returnValue('none');
                    MessageDialogue.addConversationMessage(data);

                    expect(settings.modal.css).toHaveBeenCalledWith('display');
                    expect(MessageDialogue.addMessageToConversation).not.toHaveBeenCalledWith(data);
                    expect(MessageDialogue.resetConversationScrollPanel).not.toHaveBeenCalled();

                });

            });

            describe('addMessageToConversation', function () {
                beforeEach(function () {

                    spyOn(MessageDialogue,'resetConversationScrollPanel');
                    spyOn(Common,'friendlyDatetime').and.returnValue('a-friendly-datetime');

                    MessageDialogue.addMessageToConversation(data)
                });

                it('should clone the conversation line template element and suffix message id to element id', function () {
                    expect($(settings.conversation.lineTemplate.selector + '1234567890')).toExist();
                });

                it('added message should be the last element in the messages container', function () {
                    expect($(settings.conversation.messages).children().last())
                        .toHaveId(settings.conversation.lineTemplate.selector.substr(1) + '1234567890' );
                });

                it('added message container have class for the status of the message', function () {
                    expect($(settings.conversation.lineTemplate.selector + '1234567890 .message_container'))
                        .toHaveClass(data.status);
                });

                it('added message text should the  message text', function () {
                    expect($(settings.conversation.lineTemplate.selector + '1234567890 .message_text'))
                        .toHaveText(data.message_text);
                });

                it('added message date should the message status at date time using the friendly date time format', function () {
                    expect(Common.friendlyDatetime).toHaveBeenCalledWith(data.queued_at);
                    expect($(settings.conversation.lineTemplate.selector + '1234567890 .datetime'))
                        .toHaveText('a-friendly-datetime');
                });

                it('should reset the conversation scroll panel', function () {
                    expect(MessageDialogue.resetConversationScrollPanel).toHaveBeenCalled();
                });

            });

            describe('addMessageLine', function () {

                beforeEach(function () {
                    spyOn(MessageDialogue,'updateMessageLine');
                    spyOn(MessageDialogue,'setLineText');
                    spyOn(Common,'prependListLine').and.callFake(function (data,settings,fn) {
                        fn();
                    });

                });

                it('should call the update message line method if the message is already listed', function () {
                    fixtures.appendSet('<div id="' + settings.lineTemplate.selector.substr(1) + data._id + '"></div>');

                    MessageDialogue.addMessageLine(data);

                    expect(MessageDialogue.updateMessageLine).toHaveBeenCalledWith(data);
                    expect(Common.prependListLine).not.toHaveBeenCalled();

                });

                it('should prepend new message line to the message list', function () {

                    MessageDialogue.addMessageLine(data);

                    expect(MessageDialogue.updateMessageLine).not.toHaveBeenCalled();
                    expect(Common.prependListLine).toHaveBeenCalled();
                    expect(Common.prependListLine.calls.mostRecent().args[0]).toEqual(data);
                    expect(Common.prependListLine.calls.mostRecent().args[1]).toEqual(settings);
                    expect(MessageDialogue.setLineText).toHaveBeenCalled();

                });

            });

            describe('setLineText', function () {

                it('should fill in message details for the message line', function () {

                    fixtures.appendSet('<div ' +
                        'id="message1234567890">' +
                        '<span class="first_name"></span>' +
                        '<span class="last_name"></span>' +
                        '<span class="status"></span>' +
                        '<span class="overflow_ellipsis status_at"></span>' +
                        '</div>');

                    var line = $(settings.lineTemplate.selector + '1234567890');
                    expect(line).toExist();

                    MessageDialogue.setLineText(line,data);

                    expect(line.find('.first_name')).toHaveText(data.driver.first_name);
                    expect(line.find('.last_name')).toHaveText(data.driver.last_name);
                    expect(line.find('.status')).toHaveText(data.status);
                    expect(line.find('.status_at')).toHaveText(data[data.status+'_at']);
                    expect(line).toHaveAttr('title',data.message_text);

                });

            });
        });

        describe('handle driver message update sent from server', function () {
            var data;
            beforeEach(function () {
                fixtures.appendSet(
                    '<div id="conversation_message1234567890" ' +
                    'class="container_row" ' +
                    'style="">' +
                    '<div class="message_container queued">' +
                    '<div class="message_text">Hello</div>' +
                    '<div class="datetime_container">' +
                    '<div class="datetime">a-friendly-datetime</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>');

                data = {
                    _id: '1234567890',
                    message_text: 'hello',
                    status: 'sent',
                    queued_at: '2016-08-01T13:56:58.99',
                    sent_at: '2016-08-01T13:58:45.22',
                    driver: {
                        _id: '5647839201',
                        first_name: 'Driver',
                        last_name: 'One'
                    }
                };

            });
            describe('updateConversationMessage', function () {
                beforeEach(function () {
                    spyOn(settings.modal,'css');
                    spyOn(MessageDialogue,'resetConversationScrollPanel');
                    spyOn(Common,'friendlyDatetime').and.returnValue('another-friendly-datetime');
                });
                it('should if the conversation modal is currently displayed, ' +
                    'find and update the message status and datetime', function () {
                    settings.modal.css.and.returnValue('block');

                    MessageDialogue.updateConversationMessage(data)

                    var container = $(settings.conversation.lineTemplate.selector + data._id + ' .message_container');
                    expect(container).not.toHaveClass('queued');
                    expect(container).not.toHaveClass('delivered');
                    expect(container).not.toHaveClass('received');
                    expect(container).toHaveClass('sent');
                    expect(Common.friendlyDatetime).toHaveBeenCalledWith(data.sent_at);
                    expect(container.find('.datetime')).toHaveText('another-friendly-datetime')

                    expect(MessageDialogue.resetConversationScrollPanel).toHaveBeenCalled();

                });

                it('should do none of this if the conversation display is hidden', function () {
                    settings.modal.css.and.returnValue('none');
                    MessageDialogue.updateConversationMessage(data)

                    var container = $(settings.conversation.lineTemplate.selector + data._id + ' .message_container');
                    expect(container).toHaveClass('queued');
                    expect(container).not.toHaveClass('delivered');
                    expect(container).not.toHaveClass('received');
                    expect(container).not.toHaveClass('sent');
                    expect(Common.friendlyDatetime).not.toHaveBeenCalledWith(data.sent_at);
                    expect(container.find('.datetime')).toHaveText('a-friendly-datetime')

                });

            });


            describe('updateMessageLine', function () {

                it('should find the line and change the displayed status and datetime values', function () {

                    fixtures.appendSet(
                        '<div id="message1234567890"' +
                        '       data="1234567890"> ' +
                        '   <span class="first_name">Driver</span> ' +
                        '   <span class="last_name">One</span> ' +
                        '   <span class="status">queued</span> ' +
                        '   <span class="status_at">2016-08-01T13:56:58.99</span> ' +
                        '</div>');

                    MessageDialogue.updateMessageLine(data);

                    var line =  $(settings.lineTemplate.selector + data._id );
                    expect(line.find('.status')).toHaveText(data.status);
                    expect(line.find('.status_at')).toHaveText(data[data.status + '_at']);

                });

            });

        });

        describe('resetConversationScrollPanel', function () {
            var jspapi;
            var element;
            beforeEach(function () {
                jspapi = jasmine.createSpyObj('jspapi',['reinitialise','getIsScrollableV','scrollToBottom']);
                element = jasmine.createSpyObj('element',['data']);
                element.data.and.returnValue(jspapi);
                spyOn(settings.conversation.pane,'jScrollPane').and.returnValue(element);
                spyOn(settings.conversation.fromPanel,'css');
            });

            it('should initialise jScrollPane on the conversation messages panel', function () {
                jspapi.getIsScrollableV.and.returnValue(true);

                MessageDialogue.resetConversationScrollPanel();

                expect(settings.conversation.pane.jScrollPane)
                    .toHaveBeenCalledWith(settings.conversation.jScrollPaneSettings);
                expect(element.data).toHaveBeenCalledWith('jsp');
            });
            it('should scroll to bottom if the pane is scrollable and make room for the scroll bar', function () {
                jspapi.getIsScrollableV.and.returnValue(true);

                MessageDialogue.resetConversationScrollPanel();

                expect(settings.conversation.fromPanel.css).toHaveBeenCalledWith('right',35);
                expect(jspapi.scrollToBottom).toHaveBeenCalled();
            });
            it('should adjust width to compensate for hidden scroll bar if the pane is not scrollable', function () {
                jspapi.getIsScrollableV.and.returnValue(false);

                MessageDialogue.resetConversationScrollPanel();

                expect(settings.conversation.fromPanel.css).toHaveBeenCalledWith('right',16);
            });

        });

        describe('show message driver dialogue action', function () {
            var formResetEvent;
            beforeEach(function () {
                formResetEvent = spyOnEvent(settings.form.selector,'reset');
                spyOn($.fn, 'modal');
            });

            describe('prepForMessage', function () {

                it('should prepare and show the message dialogue with the ' +
                    'properties from the selected driver line', function () {

                    MessageDialogue.prepForMessage('0987654321');

                    expect(formResetEvent).toHaveBeenTriggered();
                    expect(settings.submitButton).toHaveValue('send');
                    expect(settings.dataIdHolder).toHaveValue('0987654321');
                    expect($.fn.modal).toHaveBeenCalledWith('show');
                    expect($.fn.modal.calls.all()[0].object.selector)
                        .toEqual(settings.modal.selector);

                })
            });

        });

    });

    //TODO test delete message/s

});