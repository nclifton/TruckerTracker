/**
 * Created by nclifton on 30/07/2016.
 */

describe('LocationDialogue test all test suite', function () {

    var fixtures = jasmine.getFixtures();
    var styleFixtures = jasmine.getStyleFixtures();
    var settings;
    var ajaxSetupParams;

    default_locale_code = 'en-AU';
    time_format_hour12 = 'true';

    beforeAll(function () {
        fixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
        styleFixtures.fixturesPath = 'base/tests/js-tests/fixtures/';
    });

    beforeEach(function () {
        fixtures.load('locationListControls.html');
        fixtures.appendLoad('locationListLine.html');
        fixtures.appendLoad('locationView.html');
        fixtures.appendLoad('locationModal.html');
        fixtures.appendLoad('locateVehicle_modal.html');
        fixtures.appendLoad('locateVehicle_form.html');

        $('.modal-backdrop').remove();

        $('head').append('<meta name="_token" content="dummy_csrf_token" />');
        ajaxSetupParams = {
            headers: {
                'X-CSRF-TOKEN': 'dummy_csrf_token'
            }
        };

    });
    afterEach(function () {
        $('.modal-backdrop').remove();
    });

    describe('init functions',function () {

        beforeEach(function () {
            settings = Common.findElements(LocationDialogue.settings);
        });

        describe('Common.findElements',function () {

            it('should resolve all the selectors including nested',function () {

                expect(settings.viewButton).toEqual($(settings.selectors.viewButton));
                expect(settings.deleteButton).toEqual($(settings.selectors.deleteButton));
                expect(settings.registrationNumberHeading).toEqual($(settings.selectors.registrationNumberHeading));
                expect(settings.datetimeHeading).toEqual($(settings.selectors.datetimeHeading));
                expect(settings.modal).toEqual($(settings.selectors.modal));
                expect(settings.mapContainer).toEqual($(settings.selectors.mapContainer));

                expect(settings.locate.form).toEqual($(settings.locate.selectors.form));
                expect(settings.locate.submitButton).toEqual($(settings.locate.selectors.submitButton));
                expect(settings.locate.dataIdHolder).toEqual($(settings.locate.selectors.dataIdHolder));
                expect(settings.locate.modal).toEqual($(settings.locate.selectors.modal));


            })


        });

        describe('onUIActions',function () {
            var click;
            beforeEach(function () {

                spyOn(Common, 'setupSelect');
                spyOn(Common, 'deleteSelection');
                spyOn(LocationDialogue,'getViewLocation');
                spyOn(LocationDialogue,'submitLocationRequest');
                click = $.Event('click');
                spyOn(click,'preventDefault');

                LocationDialogue.onUIActions(settings);
            });

            it('should have delegated to the function to setup the selectable lines', function () {
                expect(Common.setupSelect).toHaveBeenCalledWith(settings.classPrefix, true);
            });

            it('should bind click on view button to open the view location modal', function () {

                $(settings.viewButton).trigger(click);
                expect(LocationDialogue.getViewLocation).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should bind delete selected locations action to clicking the delete button', function () {
                settings.deleteButton.trigger(click);
                expect(Common.deleteSelection).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();

            });

            it('should bind click on submit button in the locate vehicle modal form to submit the locate vehicle request action', function () {

                $(settings.locate.submitButton).trigger(click);
                expect(LocationDialogue.submitLocationRequest).toHaveBeenCalledWith(settings);
                expect(click.preventDefault).toHaveBeenCalled();

            });


        });

        describe('display location',function () {
            var loc;

            beforeEach(function () {
                loc = {
                    _id:        '0987654321',
                    latitude:   -34.0455263,
                    longitude:  150.8434914,
                    course:     88,
                    speed:      56,
                    status:     'received',
                    received_at:    '2015-07-30T13:45:42.55',
                    vehicle:    {
                        registration_number:    'FF56HJ'
                    }
                };
            });

            describe('showLocationMap (using mock gmap3)', function () {
                var map;
                var mapMarker;
                var marker;
                var gmap3;
                var thing;
                var mapInfowindow;
                beforeEach(function () {

                    spyOn(google.maps.event,'trigger');
                    spyOn(google.maps.event,'addListener');

                    map = jasmine.createSpyObj('map',['panTo','getCenter','setCenter']);
                    mapMarker = jasmine.createSpyObj('mapMarker',['addListener','setMap']);
                    mapInfowindow = jasmine.createSpyObj('mapInfowindow',['open','setMap']);
                    gmap3 = jasmine.createSpyObj('gmap3',['then','marker','infowindow','on']);
                    gmap3.then.and.callFake(function (fn) {
                        fn(thing);
                        return gmap3;
                    });
                    gmap3.marker.and.callFake(function (options) {
                        thing = mapMarker;
                        return gmap3;
                    });
                    gmap3.infowindow.and.callFake(function (options) {
                        thing = mapInfowindow;
                        return gmap3;
                    });
                    gmap3.on.and.returnValue(gmap3);
                    spyOn($.fn,'gmap3').and.callFake(function (options) {
                        thing = map;
                        return gmap3;
                    });
                });

                it('should if there is no location map, use gmap3 to create it', function () {

                    settings.locationMap = null;

                    LocationDialogue.showLocationMap(loc, settings);

                    expect($.fn.gmap3).toHaveBeenCalledWith({
                        center: {
                            lat: loc.latitude,
                            lng: loc.longitude
                        },
                        zoom: 15,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        disableDefaultUI: true,
                        zoomControl: true,
                        scaleControl: true,
                        fullscreenControl: true
                    });
                    expect(settings.locationMap).toBe(map);
                    $(window).trigger('resize');
                    expect(google.maps.event.trigger)
                        .toHaveBeenCalledWith(settings.locationMap, 'resize');
                    expect(gmap3.marker).toHaveBeenCalledWith({
                        position: {
                            lat: loc.latitude,
                            lng: loc.longitude
                        },
                        tag: loc._id,
                        title: loc.vehicle.registration_number,
                        icon: {
                            path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                            scale: 3
                        }
                    });
                    expect(settings.markerArray[0]).toBe(mapMarker);
                    expect(gmap3.infowindow).toHaveBeenCalledWith({
                        position: {
                            lat: loc.latitude,
                            lng: loc.longitude
                        },
                        tag: loc._id,
                        content: loc.vehicle.registration_number + ' ' +
                        loc.course + '° at ' + loc.speed + ' km/h'

                    });
                    expect(settings.infoWindowArray[0]).toBe(mapInfowindow);
                    expect(google.maps.event.addListener)
                        .toHaveBeenCalled();
                    expect(google.maps.event.addListener.calls.argsFor(0)[0])
                        .toBe(mapMarker);
                    expect(google.maps.event.addListener.calls.argsFor(0)[1])
                        .toBe('click');
                    google.maps.event.addListener.calls.argsFor(0)[2]();
                    expect(mapInfowindow.open).toHaveBeenCalled();

                    expect(map.panTo).toHaveBeenCalledWith({
                        lat: loc.latitude,
                        lng: loc.longitude
                    });

                    expect(gmap3.on).toHaveBeenCalled();
                    var bindHandler = gmap3.on.calls.mostRecent().args[0];
                    expect(bindHandler.idle).toBeDefined();
                    expect(bindHandler.resize).toBeDefined();
                    bindHandler.idle(map);
                    expect(map.getCenter).toHaveBeenCalled();
                    bindHandler.resize(map);
                    expect(map.setCenter).toHaveBeenCalled();

                    expect(mapMarker.setMap).not.toHaveBeenCalled();

                });
                it('should if there is a location map, remove the markers and infowindows from the map ' +
                    'and use gmap3 to add a new marker and infowindow for the location to the map', function () {
                    var oldMapMarker = jasmine.createSpyObj('oldMapMarker',['setMap']);
                    var oldMapInfowindow = jasmine.createSpyObj('oldMapInfowindow',['setMap']);
                    settings.markerArray = [oldMapMarker];
                    settings.infoWindowArray = [oldMapInfowindow];

                    settings.locationMap = map;

                    LocationDialogue.showLocationMap(loc, settings);

                    expect(oldMapMarker.setMap).toHaveBeenCalledWith(null);
                    expect(oldMapInfowindow.setMap).toHaveBeenCalledWith(null);
                    expect(settings.markerArray[0]).toBe(mapMarker);
                    expect(settings.infoWindowArray[0]).toBe(mapInfowindow);

                });

            });

            describe('showViewLocation', function () {

                it('should populate and popup the view location modal window', function () {
                    spyOn($.fn,'on');
                    spyOn($.fn,'off');
                    spyOn(LocationDialogue,'showLocationMap');
                    spyOn(console,'log');

                    LocationDialogue.showViewLocation(loc,settings);

                    expect(console.log).toHaveBeenCalledWith(loc);

                    expect(settings.registrationNumberHeading).toHaveText(loc.vehicle.registration_number);
                    expect(settings.datetimeHeading).toHaveText('30/07/2015, 11:45:42 pm');

                    expect($.fn.on).toHaveBeenCalled();
                    expect($.fn.on.calls.mostRecent().args[0]).toEqual('shown.bs.modal');
                    $.fn.on.calls.mostRecent().args[1]();
                    expect($.fn.off).toHaveBeenCalledWith('shown.bs.modal');
                    expect(LocationDialogue.showLocationMap).toHaveBeenCalledWith(loc,settings);

                    //expect(settings.mapContainer).toBeVisible();

                });

            });

            describe('getViewLocation', function () {

                beforeEach(function () {

                    fixtures.appendSet(
                        '<li id="location0987654321"' +
                        ' class="row list_panel_line location_line selected"' +
                        ' style=""' +
                        ' data="0987654321">' +
                        '<span class="line_fluid_column">' +
                        '<span class="registration_number">FF56HJ</span>' +
                        '<span class="status">received</span>' +
                        '<span class="overflow_container">' +
                        '<span class="overflow_ellipsis status_at">2015-07-30T13:45:42.55</span> ' +
                        '</span>' +
                        '</span>' +
                        '</li>')

                });

                it('should use jQuery get (ajax) to get the location details from ' +
                    'the server and if successful call the showViewLocation method', function () {
                    spyOn(LocationDialogue,'showViewLocation')
                    spyOn($,'get').and.callFake(function (url, fn) {
                        fn(loc, settings);
                        var d = $.Deferred();
                        d.resolve(loc);
                        return d.promise();
                    });

                    LocationDialogue.getViewLocation(settings);

                    expect($.get).toHaveBeenCalled();
                    expect($.get.calls.mostRecent().args[0]).toEqual(settings.url + '/0987654321');
                    expect(LocationDialogue.showViewLocation)
                        .toHaveBeenCalledWith(loc,settings);


                });

                it('should use jQuery get (ajax) to get the location details from ' +
                    'the server and if fails call common handle server error method', function () {

                    var data = {status:500};

                    spyOn($,'get').and.callFake(function (url, fn) {
                        var d = $.Deferred();
                        d.reject(data);
                        return d.promise();
                    });
                    spyOn(Common,'handleServerError')

                    LocationDialogue.getViewLocation(settings);

                    expect($.get).toHaveBeenCalled();
                    expect($.get.calls.mostRecent().args[0]).toEqual(settings.url + '/0987654321');
                    expect(Common.handleServerError)
                        .toHaveBeenCalledWith(data);


                });

            });
        });


        describe('submitLocationRequest', function () {
            var data;
            beforeEach(function () {

                spyOn(Common,'ajaxSetup');
                settings.locate.dataIdHolder.val('0987654321');


            });
            describe('successful server requests', function () {
                data = {
                    _id:        '0987654321',
                    status:     'queued',
                    queued_at:    '2015-07-30T13:45:42.55',
                    vehicle:    {
                        registration_number:    'FF56HJ'
                    }
                };
                beforeEach(function () {
                    spyOn($,'ajax').and.callFake(function (options) {
                        options.success(data)
                    });
                    spyOn(LocationDialogue,'locateRequestSuccess');

                    LocationDialogue.submitLocationRequest(settings);

                });

                it('should setup jquery ajax', function () {

                    expect(Common.ajaxSetup).toHaveBeenCalled();

                });

                it('should use jquery ajax POST json request to correct url with the id from the hidden data id holder input', function () {

                    expect($.ajax.calls.mostRecent().args[0].url).toBeDefined();
                    expect($.ajax.calls.mostRecent().args[0].url).toEqual('/vehicle/0987654321/location');
                    expect($.ajax.calls.mostRecent().args[0].type).toBeDefined();
                    expect($.ajax.calls.mostRecent().args[0].type).toEqual('POST');
                    expect($.ajax.calls.mostRecent().args[0].dataType).toBeDefined();
                    expect($.ajax.calls.mostRecent().args[0].dataType).toEqual('json');


                });

                it('should, on success call the location request success method', function () {
                    expect(LocationDialogue.locateRequestSuccess).toHaveBeenCalled();

                });
            });
            


            it('should on fail/error call the common handle ajax error method', function () {
                spyOn($,'ajax').and.callFake(function (options) {
                    options.error(data)
                });
                spyOn(Common,'handleAjaxError');

                LocationDialogue.submitLocationRequest(settings);

                expect(Common.handleAjaxError).toHaveBeenCalledWith(data,settings);

            });
            
        });

        describe('locateRequestSuccess', function () {
            var data;
            var resetEvent;
            beforeEach(function () {
                data = {
                    _id:        '0987654321',
                    latitude:   -34.0455263,
                    longitude:  150.8434914,
                    course:     88,
                    speed:      56,
                    status:     'received',
                    received_at:    '2015-07-30T13:45:42.55',
                    vehicle:    {
                        registration_number:    'FF56HJ'
                    }
                };
                spyOn(console,'log');
                resetEvent = spyOnEvent(settings.locate.form.selector,'reset');
                spyOn($.fn,'modal');
                spyOn(Common,'setupSelect');
                spyOn(Common,'adjustFluidColumns');

                LocationDialogue.locateRequestSuccess(settings,data)


            });

            it('should reset the locate form and popup location modal window', function () {


                expect(console.log).toHaveBeenCalledWith(data);
                expect(resetEvent).toHaveBeenTriggered();
                expect($.fn.modal).toHaveBeenCalledWith('hide');
                expect($.fn.modal.calls.mostRecent().object.selector).toEqual(settings.locate.modal.selector)

            });

            it('should add a new visible location request line to the locate list with the location id', function () {

                expect($('#' + settings.classPrefix + '0987654321')).toExist();
                expect($('#' + settings.classPrefix + '0987654321')).toHaveAttr('data','0987654321');
                expect($('#' + settings.classPrefix + '0987654321')).toBeVisible();

            });

            it('should set the line text', function () {

                expect($('#' + settings.classPrefix +
                    '0987654321 span.registration_number')).toHaveText(data.vehicle.registration_number);
                expect($('#' + settings.classPrefix +
                    '0987654321 span.status')).toHaveText(data.status);
                expect($('#' + settings.classPrefix +
                    '0987654321 span.status_at')).toHaveText(data.received_at);
            });

            it('should call the methods used to make the line selectable and ' +
                'to adjust any fluid columns used in the line', function () {

                expect(Common.setupSelect)
                    .toHaveBeenCalledWith(
                        settings.classPrefix,settings.multiSelect);
                expect(Common.adjustFluidColumns)
                    .toHaveBeenCalled();


            });

        });

    });

    describe('updateLocationLine', function () {
        var data;
        it('should update the status and datetime of an existing location line', function () {
            data = {
                _id:        '0987654321',
                status:     'sent',
                queued_at:  '2015-07-30T13:45:42.55',
                sent_at:    '2015-07-30T13:47:16.11',
                vehicle:    {
                    registration_number:    'FF56HJ'
                }
            };
            fixtures.appendSet(
                '<div id="location0987654321">' +
                '<span class="registration_number">FF56HJ</span>' +
                '<span class="status">queued</span>' +
                '<span class="status_at">2015-07-30T13:45:42.55</span>' +
                '</div>');

            LocationDialogue.updateLocationLine(data);

            var line = $(settings.lineTemplate.selector + data._id);
            expect(line.find('.status')).toHaveText(data.status);
            expect(line.find('.status_at')).toHaveText(data[data.status + '_at']);

        });

    });

    // TODO test for delete location/s

});
