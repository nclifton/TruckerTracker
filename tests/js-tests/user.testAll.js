describe('user.js test all', function () {


    var fixtures = jasmine.getFixtures();
    var styleFixtures = jasmine.getStyleFixtures();
    var settings;
    var reset;
    var click;
    var ajaxSetupParams;

    beforeAll(function () {
        fixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
        styleFixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
    });

    beforeEach(function () {
        fixtures.load('userControls.html');
        fixtures.appendLoad('userModal.html');
        fixtures.appendLoad('userLines.html');
        fixtures.appendLoad('userForm.html');
        fixtures.appendLoad('organisationModal.html');
        fixtures.appendLoad('organisationControls.html');


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

    describe('initialisation', function () {

        beforeEach(function () {
            settings = Common.findElements(UserDialogue.settings);
        });

        describe('Common.findElements', function () {

            it('should resolve all the selectors including nested', function () {

                expect(settings.modal).toEqual($(settings.selectors.modal));
                expect(settings.form).toEqual($(settings.selectors.form));
                expect(settings.addButton).toEqual($(settings.selectors.addButton));
                expect(settings.editButton).toEqual($(settings.selectors.editButton));
                expect(settings.submitButton).toEqual($(settings.selectors.submitButton));
                expect(settings.dataIdHolder).toEqual($(settings.selectors.dataIdHolder));
                expect(settings.deleteButton).toEqual($(settings.selectors.deleteButton));
                expect(settings.lineTemplate).toEqual($(settings.selectors.lineTemplate));
                expect(settings.modalLabel).toEqual($(settings.selectors.modalLabel));
                expect(settings.name).toEqual($(settings.selectors.name));
                expect(settings.email).toEqual($(settings.selectors.email));
                expect(settings.currentPassword).toEqual($(settings.selectors.currentPassword));

            })

        });

        describe('onUIActions', function () {
            beforeEach(function () {
                spyOn(Common, 'resetErrorDisplay');
                spyOn(UserDialogue,'switchModals');
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

                UserDialogue.onUIActions(settings);
            });

            it('should reset error display action to form reset event', function () {
                $(settings.form).trigger(reset);
                expect(Common.resetErrorDisplay).toHaveBeenCalledWith(settings.form[0]);
            });

            it('should bind prep for add user action to clicking the add user button/link', function () {
                settings.addButton.trigger(click);
                expect(Common.prepForAdd).toHaveBeenCalled();
                expect(Common.prepForAdd.calls.mostRecent().args[0]).toBe(settings);
                expect(UserDialogue.switchModals).toHaveBeenCalled();
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should bind prep for edit user action to clicking the edit user button/link', function () {
                settings.editButton.trigger(click);
                expect(Common.prepForEdit).toHaveBeenCalled();
                expect(Common.prepForEdit.calls.mostRecent().args[0]).toBe(settings);
                expect(UserDialogue.switchModals).toHaveBeenCalled();
                expect(click.preventDefault).toHaveBeenCalled();

            });
            it('should bind delete selected users action to clicking the delete button', function () {
                settings.deleteButton.trigger(click);
                expect(Common.deleteSelection).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should bind save user form action to clicking the submit button', function () {
                settings.submitButton.trigger(click);
                expect(Common.ajaxSaveForm)
                    .toHaveBeenCalledWith(UserDialogue.setLineText, settings);
                expect(click.preventDefault).toHaveBeenCalled();
            });

            it('should have delegated to the function to setup the selectable lines', function () {
                expect(Common.setupSelect)
                    .toHaveBeenCalledWith(settings.classPrefix, settings.multiSelect);
            });


        });

    });

    describe('initialised',function () {

        beforeEach(function () {
            settings = UserDialogue.init().settings;
        });

        describe('switchModals', function () {
            var modalHiddenEvent = $.Event('hidden.bs.modal');

            beforeEach(function () {
                OrganisationDialogue.init();
                spyOn(OrganisationDialogue.settings.submitButton,'click');
                spyOn(settings.modal,'off');
                spyOn(settings.modal,'modal');
                spyOn(OrganisationDialogue.settings.modal,'modal');
                spyOn(OrganisationDialogue.settings.modal,'off');

                UserDialogue.switchModals(settings);
            });

            it('should bind show user modal action to organisation modal hidden event', function () {

                OrganisationDialogue.settings.modal.trigger(modalHiddenEvent);

                expect(settings.modal.modal).toHaveBeenCalledWith('show');

            });

            it('should call off the hidden.bs.modal event on the user modal element', function () {
                expect(settings.modal.off).toHaveBeenCalledWith('hidden.bs.modal');

            });

            it('should bind show organisation modal and off the hidden.bs.modal event ' +
                'to the user modal hidden event', function () {

                settings.modal.trigger(modalHiddenEvent);

                expect(OrganisationDialogue.settings.modal.modal).toHaveBeenCalledWith('show');
                expect(OrganisationDialogue.settings.modal.off).toHaveBeenCalledWith('hidden.bs.modal');

            });

            it('should trigger the click evenet on the organisation submit button', function () {
                expect(OrganisationDialogue.settings.submitButton.click).toHaveBeenCalled();
            });

            
        });

        describe('setLineText', function () {
            var data = {
                _id: '0987654321',
                name: 'A User',
                email: 'some.name@example.com'
            };
            it('should set the text of the line identified in the data', function () {
                fixtures.appendSet(
                    '<div id="user0987654321"><span class="name_email"></span></div>')

                UserDialogue.setLineText(settings,data);

                expect($(settings.lineTemplate.selector + data._id).text())
                    .toEqual(data.name + ' ' + data.email);

            });

        });

    });
    



});