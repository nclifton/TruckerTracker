/**
 * Created by nclifton on 22/07/2016.
 */

describe('DriverDialogue', function () {

    var fixtures = jasmine.getFixtures();
    var styleFixtures = jasmine.getStyleFixtures();
    var reset;
    var click;
    var ajaxSetupParams;

    var settings;

    beforeAll(function () {
        fixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
        styleFixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
    });


    beforeEach(function () {
        fixtures.load('driver.html');
        fixtures.appendLoad('driverModal.html');

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

    describe('init functions', function () {

        beforeEach(function () {
            settings = Common.findElements(DriverDialogue.settings);
        });

        describe('Common.findElements', function () {

            it('should resolve all the selectors including nested', function () {

                expect(settings.form).toEqual($(settings.selectors.form));
                expect(settings.messageButton).toEqual($(settings.selectors.messageButton));
                expect(settings.editButton).toEqual($(settings.selectors.editButton));
                expect(settings.submitButton).toEqual($(settings.selectors.submitButton));
                expect(settings.addButton).toEqual($(settings.selectors.addButton));
                expect(settings.modal).toEqual($(settings.selectors.modal));
                expect(settings.dataIdHolder).toEqual($(settings.selectors.dataIdHolder));
                expect(settings.deleteButton).toEqual($(settings.selectors.deleteButton));
                expect(settings.lineTemplate).toEqual($(settings.selectors.lineTemplate));

            })

        });

        describe('onUIActions', function () {

            beforeEach(function () {
                spyOn(Common, 'resetErrorDisplay');
                spyOn(DriverDialogue, 'prepForMessage');
                spyOn(DriverDialogue, 'showModal');
                spyOn(Common, 'prepForEdit').and.callFake(function (settings,fn) {
                    fn();
                });
                spyOn(Common, 'prepForAdd').and.callFake(function (settings,fn) {
                    fn();
                });
                spyOn(Common, 'deleteSelection');
                spyOn(Common, 'ajaxSaveForm');
                spyOn(Common, 'setupSelect');
                reset = $.Event('reset');
                click = $.Event('click');
                spyOn(click, 'preventDefault');

                DriverDialogue.onUIActions(settings);
            });

            it('should bind reset error display action to form reset event', function () {
                $(settings.form).trigger(reset);
                expect(Common.resetErrorDisplay).toHaveBeenCalledWith(settings.form[0]);
            });

            it('should bind show message driver dialogue action to click message driver button', function () {
                $(settings.messageButton).trigger(click);
                expect(DriverDialogue.prepForMessage).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();
            });

            it('should bind prep for edit action to click edit driver button', function () {
                settings.editButton.trigger(click);
                expect(Common.prepForEdit).toHaveBeenCalled();
                expect(Common.prepForEdit.calls.mostRecent().args[0]).toBe(settings);
                expect(DriverDialogue.showModal).toHaveBeenCalled();
                expect(click.preventDefault).toHaveBeenCalled();
            });

            it('should bind prep for add driver action to clicking the add driver button/link', function () {
                settings.addButton.trigger(click);
                expect(Common.prepForAdd).toHaveBeenCalled();
                expect(Common.prepForAdd.calls.mostRecent().args[0]).toBe(settings);
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should bind delete selected drivers action to clicking the delete button', function () {
                settings.deleteButton.trigger(click);
                expect(Common.deleteSelection).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should bind save driver action to clicking the submit button', function () {
                settings.submitButton.trigger(click);
                expect(Common.ajaxSaveForm).toHaveBeenCalledWith(DriverDialogue.setLineText, settings);
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should have delegated to the function to setup the selectable lines', function () {
                expect(Common.setupSelect).toHaveBeenCalledWith(settings.classPrefix, true);
            });

        });

    });

    describe('show message driver dialogue action', function () {

        beforeEach(function () {
            settings = DriverDialogue.init().settings;
            spyOn(MessageDialogue,'prepForMessage');
        });

        describe('prepForMessage', function () {

            it('should prepare and show the message dialogue with the ' +
                'properties from the selected driver line', function () {
                $('#driver_list')
                    .append('<li id="driver0987654321" ' +
                        'class="row list_panel_line driver_line selected" ' +
                        'data="0987654321"><span class="name"></span></li>');

                DriverDialogue.prepForMessage(settings);

                expect(MessageDialogue.prepForMessage).toHaveBeenCalledWith('0987654321');

              })
        });


    });

    describe('tests that use selected driver lines', function () {

        beforeEach(function () {
            $('#driver_list')
                .append('<li id="driver0987654321" ' +
                    'class="row list_panel_line driver_line selected" ' +
                    'data="0987654321"><span class="name"></span></li>');
        });

        describe('Common.prepForEdit', function () {

            it('should use jquery get to get the details for driver with id from selected line', function () {
                var data = {status: 200};

                spyOn($, 'ajaxSetup');
                spyOn($, 'get').and.callFake(function (url, fn) {
                    fn(data, settings);
                    var d = $.Deferred();
                    d.resolve(data);
                    return d.promise();
                });
                spyOn(Common, 'handleGetSuccess');

                Common.prepForEdit(settings);

                expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);
                expect($.get.calls.mostRecent().args[0]).toEqual(settings.url + '/0987654321');
                expect(Common.handleGetSuccess).toHaveBeenCalled();

            });

            it('should call handle ajax error if fails', function () {
                var data = {status: 500};
                spyOn($, 'ajaxSetup');
                spyOn($, 'get').and.callFake(function (url, fn) {
                    fn(data, settings);
                    var d = $.Deferred();
                    d.reject(data);
                    return d.promise();
                });
                spyOn(Common, 'handleServerError');
                spyOn(DriverDialogue, 'showModal');

                Common.prepForEdit(settings,DriverDialogue.showModal);

                expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);
                expect($.get.calls.mostRecent().args[0]).toEqual(settings.url + '/0987654321');
                expect(Common.handleServerError).toHaveBeenCalledWith(data);


            });

        });

        describe("Common.deleteSelection", function () {

            it("should use jquery ajax to request server to delete driver and " +
                "on success call deleteLine to remove the line from the list on the screen ", function () {
                var data = {
                    status: 200,
                    responseText: {
                        _id: '0987654321'
                    }
                };
                spyOn($, 'ajaxSetup');
                spyOn($, 'ajax').and.callFake(function (options) {
                    options.success(data)
                });

                Common.deleteSelection(settings);

                expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);
                expect(console.log).toHaveBeenCalledWith(data);
                expect($("#driver0987654321")).not.toExist();


            });

        });

        it("should use jquery ajax to request server to delete driver and " +
            "on failure call handle server error ", function () {
            var data = {
                status: 500,
                responseText: '<html><body><h1>Error</h1></body></html>'
            };
            spyOn($, 'ajaxSetup');
            spyOn($, 'ajax').and.callFake(function (options) {
                options.error(data)
            });
            spyOn(Common, 'handleServerError');

            Common.deleteSelection(settings);

            expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);
            expect(Common.handleServerError).toHaveBeenCalledWith(data);

        });

    });

    describe('Common.handleGetSuccess', function () {

        it('should populate the driver edit dialogue and display its modal', function () {

            var data = {
                _id: '0987654321',
                first_name: 'Driver',
                last_name: 'One',
                mobile_phone_number: '+61298204732',
                drivers_licence_number: '6551XF'
            };
            spyOn($.fn, 'modal');
            spyOn(Common, 'handleServerError');
            spyOn(DriverDialogue,'showModal');

            Common.handleGetSuccess(data, settings,DriverDialogue.showModal);

            expect(settings.dataIdHolder).toHaveValue(data._id);
            expect(settings.form.find('[name="first_name"]')).toHaveValue('Driver');
            expect(settings.form.find('[name="last_name"]')).toHaveValue('One');
            expect(settings.form.find('[name="mobile_phone_number"]')).toHaveValue('+61298204732');
            expect(settings.form.find('[name="drivers_licence_number"]')).toHaveValue('6551XF');
            expect(settings.submitButton).toHaveValue('update');

            expect(DriverDialogue.showModal).toHaveBeenCalledWith(settings);

        });

    });

    describe('Common.prepForAdd', function () {

        it('should prepare and show the driver dialogue for adding a driver', function () {

            var spyEvent = spyOnEvent(settings.form, 'reset');
            spyOn($.fn, 'modal');
            spyOn(DriverDialogue,'showModal');

            Common.prepForAdd(settings,DriverDialogue.showModal);

            expect(spyEvent).toHaveBeenTriggered();
            expect(settings.submitButton).toHaveValue('add');
            expect(settings.submitButton).toHaveText(settings.lang.addButtonLabel);

            expect(DriverDialogue.showModal).toHaveBeenCalledWith(settings);

            //expect($.fn.modal).toHaveBeenCalledWith('show');
            //expect($.fn.modal.calls.all()[0].object.selector).toEqual('#driverModal');

        });

    });

    describe("Common.handleAjaxError", function () {

        it("should handle 500 errors with the handleServerError function", function () {
            var data = {status: 500}
            spyOn(Common, 'handleServerError');

            Common.handleAjaxError(data);

            expect(Common.handleServerError).toHaveBeenCalledWith(data);

        });

        it("should handle 403 errors with the handleStatusCode403 function", function () {
            var data = {status: 403}
            spyOn(Common, 'handlePermissionDenied');

            Common.handleAjaxError(data);

            expect(Common.handlePermissionDenied).toHaveBeenCalled();

        });

        it("should handle 422 errors with a clear error display and then manipulate the DOM to display the error", function () {
            var data = {
                status: 422,
                responseJSON: {
                    first_name: 'some error'
                }
            };

            Common.handleAjaxError(data, settings);

            expect(console.log).toHaveBeenCalledWith('Error:', data);
            expect(settings.form.find('[name="first_name"]').siblings('span.help-block')).toExist();
            expect(settings.form.find('[name="first_name"]').closest('div.form-group.has-error')).toExist();


        });
    });

    describe('Common.ajaxSaveForm', function () {
        var response, e, formData;
        beforeEach(function () {
            response = {};
            e = $.Event(settings.submitButton);
            spyOn(e, 'preventDefault');
            spyOn($, 'ajaxSetup');
            formData = {
                first_name: 'Driver',
                last_name: 'One',
                mobile_phone_number: '+6198204732',
                drivers_licence_number: '6951XG'
            };
            spyOn($.fn, 'serializeFormJSON').and.returnValue(formData);

            spyOn(Common, 'handleSaveSuccess');
            spyOn(Common, 'handleAjaxError');
        });

        describe('ajax success', function () {
            beforeEach(function () {
                spyOn($, 'ajax').and.callFake(function (options) {
                    options.success(response);
                });

            });


            it('should use jquery ajaxSetup', function () {

                Common.ajaxSaveForm(DriverDialogue.setLineText, settings);

                expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);

            });

            it('should get the form data using jQuery extension serializeFormJSON ', function () {

                Common.ajaxSaveForm(DriverDialogue.setLineText, settings);

                expect($.fn.serializeFormJSON).toHaveBeenCalled();
                expect($.fn.serializeFormJSON.calls.all()[0].object.selector).toEqual('#driverForm');

            });

            it('should log the form data', function () {

                Common.ajaxSaveForm(DriverDialogue.setLineText, settings);
                expect(console.log).toHaveBeenCalledWith(formData);

            });

            it('should use jQuery ajax to send add driver request to server', function () {

                settings.submitButton.val('add');

                Common.ajaxSaveForm(DriverDialogue.setLineText, settings);

                expect($.ajax).toHaveBeenCalled();
                expect($.ajax.calls.argsFor(0)[0].type).toEqual('POST');
                expect($.ajax.calls.argsFor(0)[0].url).toEqual(settings.url);
                expect($.ajax.calls.argsFor(0)[0].data).toEqual(formData);
                expect($.ajax.calls.argsFor(0)[0].dataType).toEqual('json');

            });

            it('should call the handleSaveSuccess function with add state', function () {

                settings.submitButton.val('add');

                Common.ajaxSaveForm(DriverDialogue.setLineText, settings);

                expect(Common.handleSaveSuccess).toHaveBeenCalledWith(response, 'add', settings, DriverDialogue.setLineText);

            });

            it('should use jQuery ajax to send update driver request to server', function () {

                settings.submitButton.val('update');

                Common.ajaxSaveForm(DriverDialogue.setLineText, settings);

                expect($.ajax).toHaveBeenCalled();
                expect($.ajax.calls.argsFor(0)[0].type).toEqual('PUT');
                expect($.ajax.calls.argsFor(0)[0].url).toEqual(settings.url + '/0987654321');
                expect($.ajax.calls.argsFor(0)[0].data).toEqual(formData);
                expect($.ajax.calls.argsFor(0)[0].dataType).toEqual('json');

            });

            it('should call the handleSaveSuccess function with update state ', function () {

                settings.submitButton.val('update');

                Common.ajaxSaveForm(DriverDialogue.setLineText, settings);

                expect(Common.handleSaveSuccess).toHaveBeenCalledWith(response, 'update', settings, DriverDialogue.setLineText);

            });

        });

        describe('ajax error', function () {

            it('should call the handleAjaxError function when ajax error occurs', function () {
                spyOn($, 'ajax').and.callFake(function (options) {
                    options.error(response);
                });
                settings.submitButton.val('add');

                Common.ajaxSaveForm(DriverDialogue.setLineText, settings);

                expect(Common.handleAjaxError).toHaveBeenCalledWith(response, settings);

            });
        });

    });

    describe('Common.handleSaveSuccess', function () {
        var state;
        var response;
        beforeEach(function () {
            state = 'add';
            response = {
                _id: '0987654321',
                first_name: 'Driver',
                last_name: 'One',
                mobile_phone_number: '+6198204732',
                drivers_licence_number: '6951XG'
            };
            spyOn($.fn, 'modal');
            spyOnEvent(settings.form, 'reset');
            spyOn(Common, 'setupSelect');
            spyOn(Common, 'adjustFluidColumns');
        });


        it('should log response to console', function () {

            Common.handleSaveSuccess(response, state, settings, DriverDialogue.setLineText);

            expect(console.log).toHaveBeenCalledWith(response);

        });

        it('should if state is add, add a new driver line to the driver list panel', function () {

            Common.handleSaveSuccess(response, state, settings, DriverDialogue.setLineText);

            expect($('#driver_list #driver0987654321')).toExist();

        });


        it('should if state is add, should call function that establishes driver list lines as selectable', function () {

            Common.handleSaveSuccess(response, state, settings, DriverDialogue.setLineText);

            expect(Common.setupSelect).toHaveBeenCalledWith(settings.classPrefix,settings.multiSelect);

        });

        it('should have put the name of the driver in the line', function () {

            Common.handleSaveSuccess(response, state, settings, DriverDialogue.setLineText);

            expect($('#driver0987654321 .name')).toContainText('Driver One');

        });

        it('should if state is update, change the name of the driver on the selected line', function () {

            $('#driver').after('<li id="driver0987654321" ' +
                'class="row list_panel_line driver_line" ' +
                'style="" ' +
                'data="0987654321">' +
                '   <span class="name">Some Driver</span>' +
                '</li>');
            state = 'update';

            expect($('#driver0987654321 .name')).toContainText('Some Driver');

            Common.handleSaveSuccess(response, state, settings, DriverDialogue.setLineText);

            expect($('#driver0987654321 .name')).toContainText('Driver One');

        });

        it('should tell the driver modal to hide', function () {

            Common.handleSaveSuccess(response, state, settings, DriverDialogue.setLineText);

            expect($.fn.modal).toHaveBeenCalledWith('hide');
            expect($.fn.modal.calls.all()[0].object.selector).toEqual('#driverModal');

        });

        it('should trigger a form reset on the driver form', function () {
            Common.handleSaveSuccess(response, state, settings, DriverDialogue.setLineText);
            expect('reset').toHaveBeenTriggeredOn(settings.form);
        });

        it('should call the adjustFluidColumns function', function () {
            Common.handleSaveSuccess(response, state, settings, DriverDialogue.setLineText);
            expect(Common.adjustFluidColumns).toHaveBeenCalled();

        });

    });


});