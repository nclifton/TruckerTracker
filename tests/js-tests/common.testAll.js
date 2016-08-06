describe("common functions for all ", function () {
    var result;
    var fixtures = jasmine.getFixtures();
    var styleFixtures = jasmine.getStyleFixtures();

    beforeAll(function () {
        fixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
        styleFixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
    });


    beforeEach(function () {
        jasmine.clock().install();
        var baseTime = new Date("2016-07-14T21:54:42+10:00");
        jasmine.clock().mockDate(baseTime);
        spyOn(console, 'log');

    });

    afterEach(function () {
        jasmine.clock().uninstall();
    });

    describe("friendlyDatetime", function () {

        it("and returns a friendly date", function () {
            result = Common.friendlyDatetime("2016-07-14T21:54:42+10:00");
            expect(result).toEqual('9:54:42 PM (just now)');
        })

    });

    describe('HandlePermissionDenied', function () {

        it('will display permission denied message', function () {
            spyOn(window, 'alert');
            Common.handlePermissionDenied();
            expect(window.alert).toHaveBeenCalledWith('Permission Denied');
        })

    });

    describe('handleServerError', function () {
        var responseText = '<h1>a server error occured</h1>';
        it('will call document.open', function () {
            spyOn(document, 'open');
            spyOn(document, 'write');
            spyOn(document, 'close');

            Common.handleServerError({responseText: responseText});

            expect(document.open).toHaveBeenCalledWith("text/html", "replace");
            expect(document.write).toHaveBeenCalledWith(responseText);
            expect(document.close).toHaveBeenCalled();
        });

    });

    describe('handleAjaxError', function () {
        var responseText = '<h1>a server error occured</h1>';
        var data = {
            responseText: responseText,
            status: 500
        };
        var settings = {
            something: 'anything'
        };

        it('if status 500 will call Common.handleServerError', function () {
            spyOn(Common, 'handleServerError');
            Common.handleAjaxError(data,settings);
            expect(console.log).toHaveBeenCalledWith('Error:', data);
            expect(Common.handleServerError).toHaveBeenCalledWith(data);
        });
        it('if status 401 will call Common.handlePermissionDenied', function () {
            spyOn(Common, 'handlePermissionDenied');
            data.status = 401;
            Common.handleAjaxError(data,settings);
            expect(console.log).toHaveBeenCalledWith('Error:', data);
            expect(Common.handlePermissionDenied).toHaveBeenCalled();
        });
        it('if status 403 will call Common.handlePermissionDenied', function () {
            spyOn(Common, 'handlePermissionDenied');
            data.status = 403;
            Common.handleAjaxError(data,settings);
            expect(console.log).toHaveBeenCalledWith('Error:', data);
            expect(Common.handlePermissionDenied).toHaveBeenCalled();
        });
        it('if status 422 will call Common.handleFormError', function () {
            spyOn(Common, 'handleFormError');
            data.status = 422;
            Common.handleAjaxError(data,settings);
            expect(console.log).toHaveBeenCalledWith('Error:', data);
            expect(Common.handleFormError).toHaveBeenCalledWith(data,settings);
        });

    });

    describe('removeFluidColumnStyleWidths', function () {
        it('should remove style width from line_fluid_column elements and their siblings and children if the line_fluid_column is visible', function () {
            setFixtures('<div id="row" class="row">' +
                '   <div id="sibling1" style="width: 90px;"></div>' +
                '   <div id="fluidColumn" class="line_fluid_column" style="width: 100px;">' +
                '       <div id="child1" style="width: 50px;">some content</div>' +
                '       <div id="child2" style="width: 80px;">some content</div>' +
                '       <div id="child3" style="width: 110px;">some content</div>' +
                '   </div>' +
                '   <div id="sibling2" style="width: 70px;"></div>' +
                '</div>');

            expect($('#row')).toHaveClass('row');
            expect($('#fluidColumn')).toHaveClass('line_fluid_column');

            expect($('#fluidColumn')).toHaveCss({width: "100px"});
            expect($('#child1')).toHaveCss({width: "50px"});
            expect($('#child2')).toHaveCss({width: "80px"});
            expect($('#child3')).toHaveCss({width: "110px"});
            expect($('#sibling1')).toHaveCss({width: "90px"});
            expect($('#sibling2')).toHaveCss({width: "70px"});

            Common.removeFluidColumnStyleWidth();

            expect($('#fluidColumn')).not.toHaveCss({width: "100px"});
            expect($('#child1')).not.toHaveCss({width: "50px"});
            expect($('#child2')).not.toHaveCss({width: "80px"});
            expect($('#child3')).not.toHaveCss({width: "110px"});
            expect($('#sibling1')).not.toHaveCss({width: "90px"});
            expect($('#sibling2')).not.toHaveCss({width: "70px"});

        });
        it('should not remove style width from line_fluid_column elements and their siblings and children if the line_fluid_column is not visible', function () {
            setFixtures('<div id="row" class="row">' +
                '   <div id="fluidColumn" class="line_fluid_column" style="display: none; width: 100px;">' +
                '       <div id="child1" style="width: 50px;">some content</div>' +
                '   </div>' +
                '</div>');
            expect($('#child1')).toHaveCss({width: "50px"});
            Common.removeFluidColumnStyleWidth();
            expect($('#child1')).toHaveCss({width: "50px"});

        });

    });

    describe('adjustFluidColumns', function () {
        beforeEach(function () {
            fixtures.load('common.adjustfluidcolumns.html');
            styleFixtures.load('common.adjustfluidcolumns.css');

            spyOn(Common, 'removeFluidColumnStyleWidth');
        });

        it('should adjust fluid column width and set overflow_ellipsis_active on the overflow container child', function () {
            styleFixtures.appendLoad('common.adjustfluidcolumns.ellipsisActive.css');

            Common.adjustFluidColumns();

            expect(Common.removeFluidColumnStyleWidth).toHaveBeenCalled();

            expect($('#fluidColumn').width()).toEqual(154, '#fluidColumn width');
            expect($('.overflow_container').width()).toEqual(50, '.overflow_container width');
            expect($('#child3')).toHaveClass('overflow_ellipsis_active');
        });

        it('should adjust fluid column width and but not set overflow_ellipsis_active on the overflow container child when it is not required', function () {
            styleFixtures.appendLoad('common.adjustfluidcolumns.ellipsisNotActive.css');

            Common.adjustFluidColumns();

            expect(Common.removeFluidColumnStyleWidth).toHaveBeenCalled();

            expect($('#fluidColumn').width()).toEqual(454, '#fluidColumn width');
            expect($('.overflow_container').width()).toEqual(350, '.overflow_container width');
            expect($('#child3')).not.toHaveClass('overflow_ellipsis_active');
        });
        it('should do nothing if the fluid column is hidden', function () {
            styleFixtures.appendLoad('common.adjustfluidcolumns.hidden.css');

            Common.adjustFluidColumns();

            expect(Common.removeFluidColumnStyleWidth).toHaveBeenCalled();

            expect($('#fluidColumn')).not.toHaveCss({width: '450px'});
            expect($('.overflow_container')).not.toHaveCss({width: '342px'});
            expect($('#child3')).not.toHaveClass('overflow_ellipsis_active');
        });

    });

    describe('enableDisableControls', function () {

        it('should enable controls except btn-primary when line is selected', function () {
            fixtures.load('common.enableDisableControls.selectedDisabled.html');

            expect($('.btn-detail')).toBeDisabled();
            expect($('.btn-danger')).toBeDisabled();
            expect($('.btn-warning')).toBeDisabled();
            expect($('.btn-primary')).toBeDisabled();

            Common.enableDisableControls('test');

            expect($('.btn-detail')).not.toBeDisabled();
            expect($('.btn-danger')).not.toBeDisabled();
            expect($('.btn-warning')).not.toBeDisabled();
            expect($('.btn-primary')).toBeDisabled();

        });

        it('should disable controls except btn-primary when no line is selected', function () {
            fixtures.load('common.enableDisableControls.notSelectedEnabled.html');

            expect($('.btn-detail')).not.toBeDisabled();
            expect($('.btn-danger')).not.toBeDisabled();
            expect($('.btn-warning')).not.toBeDisabled();
            expect($('.btn-primary')).not.toBeDisabled();

            Common.enableDisableControls('test');

            expect($('.btn-detail')).toBeDisabled();
            expect($('.btn-danger')).toBeDisabled();
            expect($('.btn-warning')).toBeDisabled();
            expect($('.btn-primary')).not.toBeDisabled();

        });

    });

    describe('setupSelect', function () {

        beforeEach(function () {
            fixtures.load('common.setupSelect.html');
            styleFixtures.load('common.setupSelect.css');

            spyOnEvent($('#test1'), 'click');
            spyOnEvent($('#test2'), 'click');

            spyOn(Common, 'enableDisableControls');
        });

        it('should create a click handler on lines that will select only one line', function () {


            expect($('#test1')).not.toHaveClass('selected');
            expect($('#test2')).not.toHaveClass('selected');

            Common.setupSelect('test', false);

            $('#test1').click();

            expect('click').toHaveBeenTriggeredOn($('#test1'));

            expect($('#test1')).toHaveClass('selected');
            expect($('#test2')).not.toHaveClass('selected');
            expect(Common.enableDisableControls).toHaveBeenCalledWith('test');

            $('#test2').click();

            expect($('#test1')).not.toHaveClass('selected');
            expect($('#test2')).toHaveClass('selected');

        })

        it('should create a click handler on lines that will select only one line', function () {

            expect($('#test1')).not.toHaveClass('selected');
            expect($('#test2')).not.toHaveClass('selected');

            Common.setupSelect('test', true);

            $('#test1').click();

            expect('click').toHaveBeenTriggeredOn($('#test1'));

            expect($('#test1')).toHaveClass('selected');
            expect($('#test2')).not.toHaveClass('selected');
            expect(Common.enableDisableControls).toHaveBeenCalledWith('test');

            $('#test2').click();

            expect($('#test1')).toHaveClass('selected');
            expect($('#test2')).toHaveClass('selected');

        })

    });

    describe('setupSse', function () {

        it('only setup if has not already been setup', function () {

            expect(Common.sse).toBeFalsy();
            Common.sse = 'something';
            expect(Common.sse).toBeTruthy();

            Common.setupSse();

            expect(Common.sse).toBe('something');

        });

        it('should be configured with custom events binding these to methods ', function () {

            spyOn(Common,'handleSseMessageUpdate');
            spyOn(Common,'handleSseMessageReceived');
            spyOn(Common,'handleSseLocationUpdate');
            spyOn(Common,'handleSseLocationReceived');
            var mockSse = jasmine.createSpyObj('sse',['start']);

            spyOn($,'SSE').and.callFake(function (url, options) {
                for(var key in options.events){
                    options.events[key]();
                }
                return mockSse;
            });

            Common.settings.orgId ='0987654321';
            Common.sse = false;
            Common.setupSse();
            expect($.SSE).toHaveBeenCalled();
            expect($.SSE.calls.mostRecent().args[0])
                .toEqual(Common.settings.sseUrl + '/' + Common.settings.orgId);
            expect(Common.handleSseMessageUpdate).toHaveBeenCalled();
            expect(Common.handleSseMessageReceived).toHaveBeenCalled();
            expect(Common.handleSseLocationUpdate).toHaveBeenCalled();
            expect(Common.handleSseLocationReceived).toHaveBeenCalled();

            expect(mockSse.start).toHaveBeenCalled();

        });

    });

    describe('resetErrorDisplay', function () {
        it('clears or resets the error display on form elements', function () {

            setFixtures('<form id="form">' +
                '<div id="formGrp1" class="form-group has-error">' +
                '   <div>' +
                '       <input type="text" name="prop1">' +
                '       <span class="help-block">' +
                '           <strong>some content</strong>' +
                '       </span>' +
                '   </div>' +
                '</div>');

            expect($('#formGrp1')).toHaveClass('has-error');
            expect($('#formGrp1 .help-block')).toExist();

            Common.resetErrorDisplay('#form');

            expect($('#formGrp1')).not.toHaveClass('has-error');
            expect($('#formGrp1 .help-block')).not.toExist();

        });
    });

    describe('prependListLine', function () {
        var settings;
        var data;
        it('should clone the template line and add it to the list immediately after the template', function () {
            fixtures.set('<ul id="thing_list"><li id="thing" style="display:none;"></li><li id="thing0987654321" style="">existing visible line</li></ul>');
            settings = {
                lineTemplate: $('#thing'),
                list: $('#thing_list'),
                classPrefix: 'thing',
                multiSelect: true
            };
            var thing = jasmine.createSpyObj('thing', ['setLineText']);
            data = {
                _id: '1234567890',
                something: 'anything'
            };
            spyOn(Common, 'setupSelect');
            spyOn(Common, 'adjustFluidColumns');

            Common.prependListLine(data, settings, thing.setLineText);

            var line = $(settings.lineTemplate.selector + data._id);
            expect(line).toExist();
            expect($(settings.list.children()[1]).attr('id')).toEqual(line.attr('id'));
            expect(line).toHaveAttr('data', data._id);
            expect(thing.setLineText).toHaveBeenCalled();
            expect(thing.setLineText.calls.mostRecent().args[0]).toEqual(line);
            expect(thing.setLineText.calls.mostRecent().args[1]).toEqual(data);
            expect(line).not.toHaveCss('display:none');
            expect(Common.setupSelect)
                .toHaveBeenCalledWith(settings.classPrefix, settings.multiSelect);
            expect(Common.adjustFluidColumns).toHaveBeenCalled();

        });

        //TODO test scroll into view

    });

    describe('ajaxSetup', function () {

        it('should call jquery ajaxSetup using a the _token meta tag for the header X-CSRF-TOKEN', function () {

            $('head').append('<meta name="_token" content="dummy_csrf_token" />');

            spyOn($,'ajaxSetup');

            Common.ajaxSetup();

            expect($.ajaxSetup)
                .toHaveBeenCalledWith({headers:{'X-CSRF-TOKEN': 'dummy_csrf_token'}});
        });


    });

    describe('findElements', function () {

        it('should convert selectors in passed settings object to jquery objects', function () {

            fixtures.set('<div id="someElement"></div><div id="anotherElement"></div>')

            var settings = {
                selectors: {
                    someElement: '#someElementId'
                },
                nested: {
                    selectors: {
                        anotherElement: '#anotherElementId'
                    }
                }
            };

            settings = Common.findElements(settings);

            expect(settings.someElement.selector).toBeDefined();
            expect(settings.nested.anotherElement.selector).toBeDefined();

        });

    });

    //TODO test for prepForAdd
    //TODO test for prepForEdit
    //TODO test for handleGetSuccess
    //TODO test for deleteSelection
    //TODO test for handleFormError
    //TODO test for ajaxSaveForm
    //TODO test for handleSaveSuccess
    //TODO test for setClickableTooltip
    //TODO test for handleSseMessageUpdate
    //TODO test for handleSseMessageReceived
    //TODO test for handleSseLocationUpdate
    //TODO test for handleSseLocationReceived
    //TODO test for onUIActions



});



