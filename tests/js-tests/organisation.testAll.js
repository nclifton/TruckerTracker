describe('organisationDialogue test all', function () {

    var fixtures = jasmine.getFixtures();
    var styleFixtures = jasmine.getStyleFixtures();
    var settings;

    beforeAll(function () {
        fixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
        styleFixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
    });

    beforeEach(function () {
        fixtures.load('organisationControls.html');
        fixtures.appendLoad('organisationModal.html');
        fixtures.appendLoad('organisationTabs.html');
        fixtures.appendLoad('organisationConfigForm.html');
        fixtures.appendLoad('organisationTwilioForm.html');


    });
    afterEach(function () {
        $('.modal-backdrop').remove();
    });

    describe('function init', function () {

        it('should call a set of methods to initialise the module', function () {

            var settings;

            spyOn(OrganisationDialogue, 'onUIActions');
            spyOn(OrganisationDialogue, 'prepForAdd');

            settings = OrganisationDialogue.init().settings;

            expect(OrganisationDialogue.onUIActions).toHaveBeenCalledWith(settings);
            expect(OrganisationDialogue.prepForAdd).toHaveBeenCalledWith(settings);

        })

    });

    describe('functions used in init', function () {

        describe('function findElements', function () {
            it('should fill settings with jquery arrays for the key dialogue elements', function () {

                fixtures.appendSet('<a id="btn-edit-org" ' +
                    'name="btn-edit-org" ' +
                    'href="#" class="open-modal-org" ' +
                    'data="">Edit Organisation </a>');

                var settings = OrganisationDialogue.settings;

                Common.findElements(settings);

                expect(settings.orgId).toExist();
                expect(settings.addButton).toExist();
                expect(settings.editButton).toExist();
                expect(settings.submitButton).toExist();
                expect(settings.addDriverButton).toExist();
                expect(settings.addVehicleButton).toExist();
                expect(settings.configForm).toExist();
                expect(settings.twilioForm).toExist();
                expect(settings.modal).toExist();
                expect(settings.usersTabLink).toExist();
                expect(settings.orgNameHeading).toExist();

            })
        });

        describe('functions in init that depend on findElements', function () {

            beforeEach(function () {
                settings = Common.findElements(OrganisationDialogue.settings);
                spyOn(OrganisationDialogue, 'prepForAdd');
                spyOn(OrganisationDialogue, 'prepForEdit');
                spyOn(Common, 'resetErrorDisplay');
                spyOn(Common, 'adjustFluidColumns');
                spyOn(OrganisationDialogue, 'saveOrg');

            });

            describe('onUIActions', function () {

                it('should setup add button click event handler', function () {

                    OrganisationDialogue.onUIActions(settings);

                    settings.addButton.click();

                    expect(OrganisationDialogue.prepForAdd).toHaveBeenCalledWith(settings);


                });

                it('should setup click event handler on the edit button if it exists', function () {

                    appendSetFixtures('<a id="btn-edit-org" name="btn-edit-org" href="#" ' +
                        'class="open-modal-org" data="1234567890">Edit Organisation</a>');
                    settings.editButton = $(settings.selectors.editButton);

                    OrganisationDialogue.onUIActions(settings);

                    settings.editButton.click();

                    expect(OrganisationDialogue.prepForEdit)
                        .toHaveBeenCalledWith(settings.editButton[0], settings);

                });

                it('should setup config form reset event handler', function () {

                    OrganisationDialogue.onUIActions(settings);

                    settings.configForm.trigger('reset');

                    expect(Common.resetErrorDisplay)
                        .toHaveBeenCalledWith(settings.configForm[0]);

                });

                it('should setup twilio form reset event handler', function () {

                    OrganisationDialogue.onUIActions(settings);

                    settings.twilioForm.trigger('reset');

                    expect(Common.resetErrorDisplay)
                        .toHaveBeenCalledWith(settings.twilioForm[0]);

                });

                it('should setup tab open event handler', function () {

                    OrganisationDialogue.onUIActions(settings);

                    settings.usersTabLink.trigger('shown.bs.tab');

                    expect(Common.adjustFluidColumns).toHaveBeenCalled();

                });

                it('should setup click event handler on the save button', function () {

                    OrganisationDialogue.onUIActions(settings);

                    settings.submitButton.click();

                    expect(OrganisationDialogue.saveOrg)
                        .toHaveBeenCalledWith(settings);

                });
            });
        });

        describe('prepForAdd', function () {

            beforeEach(function () {
                jasmine.clock().install();
                settings = Common.findElements(OrganisationDialogue.settings);
            });
            afterEach(function () {
                jasmine.clock().uninstall();
            });

            it('prepares and opens the organisation dialogue modal window for adding the organisation', function () {

                spyOnEvent(settings.configForm, 'reset');
                spyOnEvent(settings.twilioForm, 'reset');
                spyOn(settings.modal, 'modal');

                OrganisationDialogue.prepForAdd(settings);

                expect(settings.submitButton).toHaveValue('add');
                expect(settings.submitButton).toHaveText('Add Organisation');
                expect('reset').toHaveBeenTriggeredOn(settings.configForm);
                expect('reset').toHaveBeenTriggeredOn(settings.twilioForm);
                jasmine.clock().tick(500);
                expect(settings.modal.modal).toHaveBeenCalledWith('show');

            })

        });

    });

    describe('after init tests', function () {
        var settings;
        beforeEach(function () {
            settings = OrganisationDialogue.init().settings;
        });

        describe('get organisation id from a button or a link', function () {
            it('should get the id from a button using the value attribute', function () {
                setFixtures('<button id="dummyOrgId" value="1234567890">');
                var orgId = OrganisationDialogue.getOrgIdFromElement('#dummyOrgId');
                expect(orgId).toEqual('1234567890');
            });
            it('should get the id from a link using the data attribute', function () {
                setFixtures('<a id="dummyOrgId" data="1234567890">link</a>');
                var orgId = OrganisationDialogue.getOrgIdFromElement('#dummyOrgId');
                expect(orgId).toEqual('1234567890');
            })
        });

        describe('prepForEdit', function () {
            beforeEach(function () {
                spyOn(OrganisationDialogue, 'getOrgIdFromElement').and.callFake(function (button) {
                    return '1234567890';
                });
            });

            it('should make ajax (get) request to correct url and call getOrgSuccess on success', function () {
                var data = {status: 200};
                spyOn($, 'get').and.callFake(function (url, fn) {
                    fn(data, settings);
                    var d = $.Deferred();
                    d.resolve(data);
                    return d.promise();
                });
                spyOn(OrganisationDialogue, 'getOrgSuccess');

                OrganisationDialogue.prepForEdit('#btn-edit-org', settings);

                expect(OrganisationDialogue.getOrgIdFromElement).toHaveBeenCalledWith('#btn-edit-org');
                expect($.get.calls.mostRecent().args[0]).toEqual(settings.url + '/1234567890');
                expect(OrganisationDialogue.getOrgSuccess).toHaveBeenCalledWith(data, settings);
            });

            it('should make ajax (get) request to correct url and call Common.handleServerError in fail', function () {
                var data = {status: 500};
                spyOn($, 'get').and.callFake(function (url, fn) {
                    var d = $.Deferred();
                    d.reject(data);
                    return d.promise();
                });
                spyOn(Common, 'handleServerError');

                OrganisationDialogue.prepForEdit('#btn-edit-org', settings);

                expect(OrganisationDialogue.getOrgIdFromElement)
                    .toHaveBeenCalledWith('#btn-edit-org');
                expect($.get.calls.mostRecent().args[0]).toEqual(settings.url + '/1234567890');
                expect(Common.handleServerError).toHaveBeenCalledWith(data);

            })

        });

        describe('handleAjaxError', function () {
            it('should when data status 422, reset the two org forms and does stuff to the DOM ' +
                'to display error information against the input fields with the errors information ' +
                'contained in the data', function () {

                var data = {
                    status: 422,
                    responseJSON: {
                        prop2: "an error message"
                    }
                };
                spyOn(Common, 'resetErrorDisplay');

                OrganisationDialogue.handleAjaxError(data, settings);

                expect(Common.resetErrorDisplay)
                    .toHaveBeenCalledWith(settings.configForm[0]);
                expect(Common.resetErrorDisplay)
                    .toHaveBeenCalledWith(settings.twilioForm[0]);

                expect(settings.configForm.find('#formGrp2 .help-block')).toContainText('an error message');
                expect(settings.configForm.find('#formGrp2')).toHaveClass('has-error');

            });

            it('should when data status 422 resets the two org forms and does stuff to the DOM ' +
                'to display error and if the containing tab panel is not active, make it active', function () {

                var data = {
                    status: 422,
                    responseJSON: {
                        prop4: "an error message"
                    }
                };
                spyOn(Common, 'resetErrorDisplay');

                OrganisationDialogue.handleAjaxError(data, settings);

                expect(Common.resetErrorDisplay)
                    .toHaveBeenCalledWith(settings.configForm[0]);
                expect(Common.resetErrorDisplay)
                    .toHaveBeenCalledWith(settings.twilioForm[0]);

                expect(settings.twilioForm.find('#formGrp4 .help-block')).toContainText('an error message');
                expect(settings.twilioForm.find('#formGrp4')).toHaveClass('has-error');

                expect($('#twilio')).toHaveClass('active');
                expect($('#config')).not.toHaveClass('active');

            });

            it('should when data status is not 422 pass the error handling to ' +
                'the common Ajax error handler', function () {

                var data = {
                    status: 403
                };
                spyOn(Common, 'handleAjaxError');

                OrganisationDialogue.handleAjaxError(data, settings);

                expect(Common.handleAjaxError).toHaveBeenCalledWith(data);

            });

        });

        describe('getOrgSuccess', function () {
            var data;
            beforeEach(function () {
                //jasmine.clock().install();
                data = {
                    _id: '1234567890',
                    prop1: 'value1',
                    prop2: 'value2',
                    prop3: 'value3',
                    prop4: 'value4',
                    users: [
                        {
                            _id: '0987654321',
                            name: 'user1',
                            email: 'user1@example.com'
                        }
                    ]
                };
                spyOn(console, 'log');
                spyOnEvent('#orgConfigForm', 'reset');
                spyOnEvent('#orgTwilioForm', 'reset');

                OrganisationDialogue.getOrgSuccess(data, settings);
                //jasmine.clock().tick(1000);

            });
            afterEach(function () {
                //jasmine.clock().uninstall();
            });

            it('should log the data received to the console', function () {

                expect(console.log).toHaveBeenCalledWith(data);

            });

            it('should trigger form reset events on the two forms', function () {

                expect('reset').toHaveBeenTriggeredOn('#orgConfigForm');
                expect('reset').toHaveBeenTriggeredOn('#orgTwilioForm');

            });
            it('should populate the organisation form fields in preparation', function () {

                expect(settings.configForm.find('[name="prop1"]')).toHaveValue('value1');
                expect(settings.configForm.find('[name="prop2"]')).toHaveValue('value2');
                expect(settings.twilioForm.find('[name="prop3"]')).toHaveValue('value3');
                expect(settings.twilioForm.find('[name="prop4"]')).toHaveValue('value4');

            });
            it('should enable the users tab', function () {

                expect($('#org-users-tab-link').parent()).not.toHaveClass('disabled');
                expect($('#org-users-tab-link')).toHaveAttr('data-toggle', 'tab');


            });
            it('should populate the users list', function () {

                expect($('#user0987654321')).toExist();
                expect($('#user0987654321')).toHaveAttr('data', '0987654321');

                expect($('#user0987654321 .name_email')).toExist();
                expect($('#user0987654321 .name_email').text())
                    .toEqual('user1 user1@example.com');

            });
            it('should change the submit button value and text for updating', function () {

                expect(settings.submitButton).toHaveValue('update');
                expect(settings.submitButton).toHaveText(settings.lang.saveChanges);

            });
            it('should show the organisation dialogue modal window box', function () {

                //expect($('#orgModal')).toBeVisible();

            });

        });

        describe('saveOrgSuccess', function () {
            var data;
            var click;

            beforeEach(function () {
                data = {
                    _id: '1234567890',
                    name: 'Mother Truckers'
                };
                spyOn(console, 'log');
               spyOn(OrganisationDialogue,'prepForEdit');

                OrganisationDialogue.saveOrgSuccess(data, settings);
                settings = OrganisationDialogue.settings;

            });

            it('should put the organisation name in the dashboard panel heading', function () {

                expect(settings.orgNameHeading).toHaveText('Mother Truckers');

            });

            it('should adjust the add button so it becomes edit button', function () {
                expect(settings.editButton).toExist();
                expect(settings.addButton).not.toExist();
                expect(settings.editButton).toHaveAttr('name', 'btn-edit-org');
                expect(settings.editButton).toHaveAttr('data', '1234567890');
                expect(settings.editButton).toHaveText(settings.lang.editOrg);

            });

            it('should bind the prep for edit action to the edit button', function () {
                settings.editButton.click();

                expect(OrganisationDialogue.prepForEdit).toHaveBeenCalled();

            });


            it('should adjust the submit button so it becomes save changes', function () {

                expect(settings.submitButton).toHaveValue('update');
                expect(settings.submitButton).toHaveText(settings.lang.saveChanges);

            });

            it('should adjust the hidden input used to keep the organisation id for the form', function () {

                expect(settings.orgId).toHaveValue('1234567890');

            });

            it('the add driver and vehicle buttons/links should be visible', function () {

                expect($('#btn-add-driver')).toBeVisible();
                expect($('#btn-add-vehicle')).toBeVisible();

            });

            it('the dialogue modal should be hidden', function () {

                expect($('#orgModal')).not.toBeVisible();

            });
        });

        describe('saveOrg', function () {
            var configFormData;
            var twilioFormData;
            var formData;
            var resData;
            var serialiseSpy;

            beforeEach(function () {
                $('head').append('<meta name="_token" content="dummy_csrf_token" />');

                configFormData = {
                    prop1: 'Mother Truckers',
                    prop2: 'valuex'
                };
                twilioFormData = {
                    prop3: 'valuey',
                    prop4: 'valuez'
                };
                formData = {
                    prop1: 'Mother Truckers',
                    prop2: 'valuex',
                    prop3: 'valuey',
                    prop4: 'valuez'
                };
                spyOn($, 'ajaxSetup');

                spyOn(console, 'log');


            });

            describe('successfully adding organisation', function () {
                beforeEach(function () {
                    resData = {
                        status: 200
                    };
                    spyOn(OrganisationDialogue, 'saveOrgSuccess');
                    spyOn($, 'ajax').and.callFake(function (options) {
                        options.success(resData, settings);
                    });
                    serialiseSpy = spyOn($.fn, 'serializeFormJSON')
                        .and.returnValues(configFormData, twilioFormData);
                    $('#btn-save-org').val('add');

                    OrganisationDialogue.saveOrg(settings);

                });

                it('should setup the ajax request header with the CSRF token', function () {
                    expect($.ajaxSetup).toHaveBeenCalledWith({
                        headers: {
                            'X-CSRF-TOKEN': 'dummy_csrf_token'
                        }
                    });
                });

                it('should use the jquery extension to serialise the form data', function () {
                    expect($.fn.serializeFormJSON.calls.count()).toEqual(2);
                    expect($.fn.serializeFormJSON.calls.all()[0].object.selector).toEqual('#orgConfigForm');
                    expect($.fn.serializeFormJSON.calls.all()[1].object.selector).toEqual('#orgTwilioForm');

                });

                it('should do the ajax call with expected non-function options', function () {
                    expect($.ajax.calls.argsFor(0)[0].type).toEqual('POST');
                    expect($.ajax.calls.argsFor(0)[0].url).toEqual('/organisation');
                    expect($.ajax.calls.argsFor(0)[0].data).toEqual(formData);
                    expect($.ajax.calls.argsFor(0)[0].dataType).toEqual('json');

                });

                it('should call the function to handle success', function () {
                    expect(OrganisationDialogue.saveOrgSuccess)
                        .toHaveBeenCalledWith(resData, settings);
                });


            });

            describe('successfully updating organisation', function () {
                var serialiseSpy;
                beforeEach(function () {
                    spyOn(OrganisationDialogue, 'saveOrgSuccess');
                    spyOn($, 'ajax').and.callFake(function (options) {
                        options.success(resData, settings);
                    });
                    serialiseSpy = spyOn($.fn, 'serializeFormJSON')
                        .and.returnValues(configFormData, twilioFormData);
                    $('#btn-save-org').val('update');
                    $('#org_id').val('1234567890');

                    OrganisationDialogue.saveOrg(settings);

                });

                it('should setup the ajax request header with the CSRF token', function () {
                    expect($.ajaxSetup).toHaveBeenCalledWith({
                        headers: {
                            'X-CSRF-TOKEN': 'dummy_csrf_token'
                        }
                    });
                });

                it('should use the jquery extension to serialise the form data', function () {
                    expect($.fn.serializeFormJSON.calls.count()).toEqual(2);
                    expect($.fn.serializeFormJSON.calls.all()[0].object.selector).toEqual('#orgConfigForm');
                    expect($.fn.serializeFormJSON.calls.all()[1].object.selector).toEqual('#orgTwilioForm');

                });

                it('should do the ajax call with expected non-function options', function () {
                    expect($.ajax.calls.argsFor(0)[0].type).toEqual('PUT');
                    expect($.ajax.calls.argsFor(0)[0].url).toEqual('/organisation/1234567890');
                    expect($.ajax.calls.argsFor(0)[0].data).toEqual(formData);
                    expect($.ajax.calls.argsFor(0)[0].dataType).toEqual('json');

                });

                it('should do the ajax call with expected non-function options', function () {
                    expect(OrganisationDialogue.saveOrgSuccess)
                        .toHaveBeenCalledWith(resData, settings);
                });
            });

            describe('failed adding organisation', function () {
                beforeEach(function () {
                    resData = {status: 422}
                    spyOn(OrganisationDialogue, 'handleAjaxError');
                    spyOn($, 'ajax').and.callFake(function (options) {
                        options.error(resData, settings);
                    });
                    serialiseSpy = spyOn($.fn, 'serializeFormJSON')
                        .and.returnValues(configFormData, twilioFormData);
                    $('#btn-save-org').val('add');

                    OrganisationDialogue.saveOrg(settings);

                });

                it('should setup the ajax request header with the CSRF token', function () {
                    expect($.ajaxSetup).toHaveBeenCalledWith({
                        headers: {
                            'X-CSRF-TOKEN': 'dummy_csrf_token'
                        }
                    });
                });

                it('should use the jquery extension to serialise the form data', function () {
                    expect($.fn.serializeFormJSON.calls.count()).toEqual(2);
                    expect($.fn.serializeFormJSON.calls.all()[0].object.selector).toEqual('#orgConfigForm');
                    expect($.fn.serializeFormJSON.calls.all()[1].object.selector).toEqual('#orgTwilioForm');

                });

                it('should do the ajax call with expected non-function options', function () {
                    expect($.ajax.calls.argsFor(0)[0].type).toEqual('POST');
                    expect($.ajax.calls.argsFor(0)[0].url).toEqual('/organisation');
                    expect($.ajax.calls.argsFor(0)[0].data).toEqual(formData);
                    expect($.ajax.calls.argsFor(0)[0].dataType).toEqual('json');

                });

                it('should do the ajax call with expected non-function options', function () {
                    expect(OrganisationDialogue.handleAjaxError)
                        .toHaveBeenCalledWith(resData, settings);
                });
            });


            describe('failed updating organisation', function () {
                beforeEach(function () {
                    resData = {status: 422}
                    spyOn(OrganisationDialogue, 'handleAjaxError');
                    spyOn($, 'ajax').and.callFake(function (options) {
                        options.error(resData, settings);
                    });
                    serialiseSpy = spyOn($.fn, 'serializeFormJSON')
                        .and.returnValues(configFormData, twilioFormData);
                    $('#btn-save-org').val('update');
                    $('#org_id').val('1234567890');

                    OrganisationDialogue.saveOrg(settings);

                });

                it('should setup the ajax request header with the CSRF token', function () {
                    expect($.ajaxSetup).toHaveBeenCalledWith({
                        headers: {
                            'X-CSRF-TOKEN': 'dummy_csrf_token'
                        }
                    });
                });

                it('should use the jquery extension to serialise the form data', function () {
                    expect($.fn.serializeFormJSON.calls.count()).toEqual(2);
                    expect($.fn.serializeFormJSON.calls.all()[0].object.selector)
                        .toEqual('#orgConfigForm');
                    expect($.fn.serializeFormJSON.calls.all()[1].object.selector)
                        .toEqual('#orgTwilioForm');

                });

                it('should do the ajax call with expected non-function options', function () {
                    expect($.ajax.calls.argsFor(0)[0].type).toEqual('PUT');
                    expect($.ajax.calls.argsFor(0)[0].url).toEqual('/organisation/1234567890');
                    expect($.ajax.calls.argsFor(0)[0].data).toEqual(formData);
                    expect($.ajax.calls.argsFor(0)[0].dataType).toEqual('json');

                });

                it('should do the ajax call with expected non-function options', function () {
                    expect(OrganisationDialogue.handleAjaxError)
                        .toHaveBeenCalledWith(resData, settings);
                });
            });
        });

    });

});
