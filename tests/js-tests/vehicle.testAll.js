/**
 * Created by nclifton on 22/07/2016.
 */

describe('VehicleDialogue',function () {

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
        fixtures.load('vehicle_nav.html');
        fixtures.appendLoad('vehicle_controls.html');
        fixtures.appendLoad('vehicle_line.html');
        fixtures.appendLoad('vehicle_modal_form.html');
        fixtures.appendLoad('locateVehicle_modal.html');
        fixtures.appendLoad('locateVehicle_form.html');

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
            settings = Common.findElements(VehicleDialogue.settings);
        });

        describe('Common.findElements',function () {

            it('should resolve all the selectors including nested',function () {

                expect(settings.form).toEqual($(settings.selectors.form));
                expect(settings.locateButton).toEqual($(settings.selectors.locateButton));
                expect(settings.editButton).toEqual($(settings.selectors.editButton));
                expect(settings.submitButton).toEqual($(settings.selectors.submitButton));
                expect(settings.addButton).toEqual($(settings.selectors.addButton));
                expect(settings.modal).toEqual($(settings.selectors.modal));
                expect(settings.dataIdHolder).toEqual($(settings.selectors.dataIdHolder));
                expect(settings.deleteButton).toEqual($(settings.selectors.deleteButton));
                expect(settings.lineTemplate).toEqual($(settings.selectors.lineTemplate));
                expect(settings.locate.form).toEqual($(settings.locate.selectors.form));
                expect(settings.locate.submitButton).toEqual($(settings.locate.selectors.submitButton));
                expect(settings.locate.dataIdHolder).toEqual($(settings.locate.selectors.dataIdHolder));
                expect(settings.locate.modal).toEqual($(settings.locate.selectors.modal));

            })


        });

        describe('onUIActions',function () {

            beforeEach(function () {
                spyOn(Common, 'resetErrorDisplay');
                spyOn(VehicleDialogue, 'prepForLocate');
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
                spyOn(click,'preventDefault');
                spyOn(VehicleDialogue,'showModal');

                VehicleDialogue.onUIActions(settings);
            });

            it('should bind reset error display action to form reset event', function () {
                $(settings.form).trigger(reset);
                expect(Common.resetErrorDisplay).toHaveBeenCalledWith(settings.form[0]);
            });

            it('should bind show locate vehicle dialogue action to clicking locate button', function () {
                $(settings.locateButton).trigger(click);
                expect(VehicleDialogue.prepForLocate).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();
            });

            it('should bind prep for edit action to click edit vehicle button', function () {
                settings.editButton.trigger(click);
                expect(Common.prepForEdit).toHaveBeenCalled();
                expect(Common.prepForEdit.calls.mostRecent().args[0]).toBe(settings);
                expect(VehicleDialogue.showModal).toHaveBeenCalled();
                expect(click.preventDefault).toHaveBeenCalled();
            });

            it('should bind show add vehicle dialogue action to clicking the add vehicle button/link', function () {
                settings.addButton.trigger(click);
                expect(Common.prepForAdd).toHaveBeenCalled();
                expect(Common.prepForAdd.calls.mostRecent().args[0]).toBe(settings);
                expect(VehicleDialogue.showModal).toHaveBeenCalled();
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should bind delete selected vehicles action to clicking the delete button', function () {
                settings.deleteButton.trigger(click);
                expect(Common.deleteSelection).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should bind save vehicle action to clicking the submit button', function () {
                settings.submitButton.trigger(click);
                expect(Common.ajaxSaveForm)
                    .toHaveBeenCalledWith(VehicleDialogue.setLineText, settings);
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should have delegated to the function to setup the selectable lines', function () {
                expect(Common.setupSelect).toHaveBeenCalledWith(settings.classPrefix, true);
            });

        });

    });

    describe ('show locate vehicle dialogue action',function () {

        beforeEach(function () {
            settings = VehicleDialogue.init().settings;
        });

        describe ('prepForLocate',function () {

            it('should prepare and show the locate vehicle dialogue with the ' +
                'properties from the selected vehicle line',function () {
                $('#vehicle_list')
                    .append('<li id="vehicle0987654321" ' +
                        'class="row list_panel_line vehicle_line selected" ' +
                        'data="0987654321"><span class="registration_number"></span></li>');
                var spyEvent = spyOnEvent(settings.locate.form,'reset');
                spyOn($.fn,'modal');

                VehicleDialogue.prepForLocate(settings,'0987654321');

                expect(spyEvent).toHaveBeenTriggered();
                expect(settings.locate.submitButton).toHaveValue('send');
                expect(settings.locate.dataIdHolder).toHaveValue('0987654321');
                expect($.fn.modal).toHaveBeenCalledWith('show');
                expect($.fn.modal.calls.all()[0].object.selector).toEqual('#locateVehicleModal');

            })
        });


    });
    
    describe('tests that use selected vehicle lines', function () {
        
        beforeEach(function () {
            $('#vehicle_list')
                .append('<li id="vehicle0987654321" ' +
                    'class="row list_panel_line vehicle_line selected" ' +
                    'data="0987654321"><span class="registration_number"></span></li>');
        });

        describe('Common.prepForEdit',function () {
            it('should use jquery get to get the details for vehicle with id from selected line',function () {
                var data = {status: 200};
                
                spyOn($,'ajaxSetup');
                spyOn($,'get').and.callFake(function (url, fn) {
                    fn(data, settings);
                    var d = $.Deferred();
                    d.resolve(data);
                    return d.promise();
                });
                spyOn(Common, 'handleGetSuccess');

                Common.prepForEdit(settings,VehicleDialogue.showModal);

                expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);
                expect($.get.calls.mostRecent().args[0]).toEqual(settings.url + '/0987654321');
                expect(Common.handleGetSuccess).toHaveBeenCalled();
                expect(Common.handleGetSuccess.calls.mostRecent().args[0]).toBe(data);
                expect(Common.handleGetSuccess.calls.mostRecent().args[1]).toBe(settings);


            });

            it('should call handle ajax error if fails', function () {
                var data = {status: 500};
                spyOn($,'ajaxSetup');
                spyOn($,'get').and.callFake(function (url, fn) {
                    fn(data, settings);
                    var d = $.Deferred();
                    d.reject(data);
                    return d.promise();
                });
                spyOn(Common, 'handleServerError');

                Common.prepForEdit(settings,VehicleDialogue.showModal);

                expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);
                expect($.get.calls.mostRecent().args[0]).toEqual(settings.url + '/0987654321');
                expect(Common.handleServerError).toHaveBeenCalledWith(data);

            });

        });

        describe("Common.deleteSelection", function () {

            it("should use jquery ajax to request server to delete vehicle and " +
                "on success call deleteLine to remove the line from the list on the screen ", function () {
                var data = {
                    status: 200,
                    responseText:{
                        _id: '0987654321'
                    }
                };
                spyOn($,'ajaxSetup');
                spyOn($,'ajax').and.callFake(function (options) {
                    options.success(data)
                });

                Common.deleteSelection(settings);

                expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);
                expect(console.log).toHaveBeenCalledWith(data);
                expect($("#driver0987654321")).not.toExist();

            });
            
        });

        it("should use jquery ajax to request server to delete vehicle and " +
            "on failure call handle server error ", function () {
            var data = {
                status: 500,
                responseText: '<html><body><h1>Error</h1></body></html>'
            };
            spyOn($,'ajaxSetup');
            spyOn($,'ajax').and.callFake(function (options) {
                options.error(data)
            });
            spyOn(Common, 'handleServerError');

            Common.deleteSelection(settings);

            expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);
            expect(Common.handleServerError).toHaveBeenCalledWith(data);

        });

    });

    describe('Common.handleGetSuccess', function () {

        it('should populate the vehicle edit dialogue and display its modal', function () {

            var data = {
                _id: '0987654321',
                registration_number: 'FG45FH',
                mobile_phone_number: '+61298204732',
                tracker_imei_number: '123456789012345'
            };
            spyOn($.fn,'modal');

            Common.handleGetSuccess(data, settings,VehicleDialogue.showModal);

            expect(settings.dataIdHolder).toHaveValue(data._id);
            expect(settings.form.find('[name="registration_number"]')).toHaveValue('FG45FH');
            expect(settings.form.find('[name="mobile_phone_number"]')).toHaveValue('+61298204732');
            expect(settings.form.find('[name="tracker_imei_number"]')).toHaveValue('123456789012345');
            expect(settings.submitButton).toHaveValue('update');

            expect($.fn.modal).toHaveBeenCalledWith('show');
            expect($.fn.modal.calls.all()[0].object.selector).toEqual('#vehicleModal');

        });
        
    });

    describe('Common.prepForAdd', function () {

        it('should prepare and show the vehicle dialogue for adding a vehicle',function () {

            var spyEvent = spyOnEvent(settings.form,'reset');
            spyOn($.fn,'modal');

            Common.prepForAdd(settings,VehicleDialogue.showModal);

            expect(spyEvent).toHaveBeenTriggered();
            expect(settings.submitButton).toHaveValue('add');
            expect(settings.submitButton).toHaveText(settings.lang.addButtonLabel);

            expect($.fn.modal).toHaveBeenCalledWith('show');
            expect($.fn.modal.calls.all()[0].object.selector).toEqual('#vehicleModal');

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
            var data={
                status:422,
                responseJSON: {
                    registration_number: 'some error'
                }
            };
            Common.handleAjaxError(data, settings);

            expect(console.log).toHaveBeenCalledWith('Error:',data);
            expect(settings.form.find('[name="registration_number"]').siblings('span.help-block')).toExist();
            expect(settings.form.find('[name="registration_number"]').closest('div.form-group.has-error')).toExist();


        });
    });

    describe('Common.ajaxSaveForm', function () {
        var response, e, formData;
        beforeEach(function () {
            response = {};
            e = $.Event(settings.submitButton);
            spyOn(e,'preventDefault');
            spyOn($,'ajaxSetup');
            formData = {
                _id: '0987654321',
                registration_number: 'FG45FH',
                mobile_phone_number: '+61298204732',
                tracker_imei_number: '123456789012345'
            };
            spyOn($.fn,'serializeFormJSON').and.returnValue(formData);
            spyOn(Common, 'handleSaveSuccess');
            spyOn(Common, 'handleAjaxError');
        });

        describe('ajax success', function () {
            beforeEach(function () {
                spyOn($,'ajax').and.callFake(function (options) {
                    options.success(response);
                });
            });


            it('should use jquery ajaxSetup', function () {

                Common.ajaxSaveForm(DriverDialogue.setLineText, settings);

                expect($.ajaxSetup).toHaveBeenCalledWith(ajaxSetupParams);

            });

            it('should get the form data using jQuery extension serializeFormJSON ', function () {

                Common.ajaxSaveForm(VehicleDialogue.setLineText, settings);

                expect($.fn.serializeFormJSON).toHaveBeenCalled();
                expect($.fn.serializeFormJSON.calls.all()[0].object.selector).toEqual('#vehicleForm');

            });

            it('should log the form data', function () {

                Common.ajaxSaveForm(VehicleDialogue.setLineText, settings);
                expect(console.log).toHaveBeenCalledWith(formData);

            });

            it('should use jQuery ajax to send add vehicle request to server', function () {

                settings.submitButton.val('add');

                Common.ajaxSaveForm(VehicleDialogue.setLineText, settings);

                expect($.ajax).toHaveBeenCalled();
                expect($.ajax.calls.argsFor(0)[0].type).toEqual('POST');
                expect($.ajax.calls.argsFor(0)[0].url).toEqual(settings.url);
                expect($.ajax.calls.argsFor(0)[0].data).toEqual(formData);
                expect($.ajax.calls.argsFor(0)[0].dataType).toEqual('json');

            });

            it('should call the handleSaveSuccess function with add state', function () {

                settings.submitButton.val('add');

                Common.ajaxSaveForm(VehicleDialogue.setLineText, settings);

                expect(Common.handleSaveSuccess).toHaveBeenCalledWith(response, 'add', settings, VehicleDialogue.setLineText);

            });

            it('should use jQuery ajax to send update vehicle request to server', function () {
                settings.submitButton.val('update');

                Common.ajaxSaveForm(VehicleDialogue.setLineText, settings);

                expect($.ajax).toHaveBeenCalled();
                expect($.ajax.calls.argsFor(0)[0].type).toEqual('PUT');
                expect($.ajax.calls.argsFor(0)[0].url).toEqual(settings.url + '/0987654321');
                expect($.ajax.calls.argsFor(0)[0].data).toEqual(formData);
                expect($.ajax.calls.argsFor(0)[0].dataType).toEqual('json');

            });

            it('should call the handleSaveSuccess function with update state ', function () {

                settings.submitButton.val('update');

                Common.ajaxSaveForm(VehicleDialogue.setLineText, settings);

                expect(Common.handleSaveSuccess).toHaveBeenCalledWith(response, 'update', settings, VehicleDialogue.setLineText);

            });

        });

        describe('ajax error', function () {

            it('should call the handleAjaxError function when ajax error occurs', function () {
                spyOn($,'ajax').and.callFake(function (options) {
                    options.error(response);
                });
                settings.submitButton.val('add');

                Common.ajaxSaveForm(VehicleDialogue.setLineText, settings);

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
                registration_number: 'FG45FH',
                mobile_phone_number: '+61298204732',
                tracker_imei_number: '123456789012345'
            };
            spyOn($.fn, 'modal');
            spyOnEvent(settings.form,'reset');
            spyOn(Common, 'setupSelect');
            spyOn(Common, 'adjustFluidColumns');
        });

        it('should log response to console', function () {

            Common.handleSaveSuccess(response, state, settings, VehicleDialogue.setLineText);

            expect(console.log).toHaveBeenCalledWith(response);

        });

        it('should if state is add, add a new driver line to the driver list panel', function () {

            Common.handleSaveSuccess(response, state, settings, VehicleDialogue.setLineText);

            expect($('#vehicle_list #vehicle0987654321')).toExist();

        });


        it('should if state is add, should call function that establishes vehicle list lines as selectable', function () {

            Common.handleSaveSuccess(response, state, settings, VehicleDialogue.setLineText);

            expect(Common.setupSelect).toHaveBeenCalledWith(settings.classPrefix,settings.multiSelect);

        });

        it('should have put the registration number of the vehicle in the line', function () {

            Common.handleSaveSuccess(response, state, settings, VehicleDialogue.setLineText);

            expect($('#vehicle0987654321 .registration_number')).toContainText('FG45FH');

        });

        it('should if state is update, change the registration number of the vehicle on the selected line', function () {

            $('#vehicle').after('<li id="vehicle0987654321" ' +
                'class="row list_panel_line vehicle_line" ' +
                'style="" ' +
                'data="0987654321">' +
                '   <span class="registration_number">XY45ZZ</span>' +
                '</li>');
            state = 'update';

            expect($('#vehicle0987654321 .registration_number')).toContainText('XY45ZZ');

            Common.handleSaveSuccess(response, state, settings, VehicleDialogue.setLineText);

            expect($('#vehicle0987654321 .registration_number')).toContainText('FG45FH');

        });

        it('should tell the vehicle modal to hide', function () {

            Common.handleSaveSuccess(response, state, settings, VehicleDialogue.setLineText);

            expect($.fn.modal).toHaveBeenCalledWith('hide');
            expect($.fn.modal.calls.all()[0].object.selector).toEqual('#vehicleModal');

        });

        it('should trigger a form reset on the driver form', function () {
            Common.handleSaveSuccess(response, state, settings, VehicleDialogue.setLineText);
            expect('reset').toHaveBeenTriggeredOn(settings.form);
        });

        it('should call the adjustFluidColumns function', function () {
            Common.handleSaveSuccess(response, state, settings, VehicleDialogue.setLineText);
            expect(Common.adjustFluidColumns).toHaveBeenCalled();

        });

    });

    
});