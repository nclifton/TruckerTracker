/**
 * Created by nclifton on 29/05/2016.
 */
var VehicleDialogue = {

    settings: {
        url: '/vehicles',
        lineSelector: '.vehicle_line.selected',
        classPrefix: 'vehicle',
        multiSelect: false,
        selectors: {
            registrationNumber: '#registration_number',
            locateButton: '#btn-locateVehicle',
            editButton: '#btn-edit-vehicle',
            addButton: '#btn-add-vehicle',
            submitButton: '#btn-save-vehicle',
            form: '#vehicleForm',
            modal: '#vehicleModal',
            deleteButton: '#btn-delete-vehicle',
            dataIdHolder: '#vehicle_id',
            lineTemplate: '#vehicle'
        },
        locate: {
            selectors: {
                form: '#locateVehicleForm',
                submitButton: '#btn-save-locateVehicle',
                dataIdHolder: '#locateVehicle_id',
                modal: '#locateVehicleModal',
            }
        },
        lang: {
            addButtonLabel: "Add Vehicle",
            saveButtonLabel: "Save Changes"
        }
    },

    init: function () {
        this.settings = Common.findElements(this.settings);
        this.onUIActions(this.settings);
        return this;
    },

    prepForLocate: function (settings) {
        $(settings.lineSelector).first().each(function (i, selected) {
            var dataId = $(selected).attr('data');
            settings.locate.form.trigger('reset');
            settings.locate.submitButton.val("send");
            settings.locate.dataIdHolder.val(dataId);
            settings.locate.modal.modal('show');
        });
    },

    setLineText: function (settings, data) {
        $("#" + settings.classPrefix + data._id + " .registration_number")
            .text(data.registration_number);
    },

    showModal: function (settings) {
        settings.modal.modal('show');
    },

    onUIActions: function (settings) {

        //TODO this needs covering by a test
        settings.registrationNumber.keyup(function () {
            this.value = this.value.toUpperCase();
        });

        settings.form.on("reset", function (e) {
            Common.resetErrorDisplay(e.target);
        });

        settings.locateButton.click(function (e) {
            e.preventDefault();
            VehicleDialogue.prepForLocate(settings);
        });

        settings.editButton.click(function (e) {
            e.preventDefault();
            Common.prepForEdit(settings, VehicleDialogue.showModal);
        });

        settings.addButton.click(function (e) {
            e.preventDefault();
            Common.prepForAdd(settings, VehicleDialogue.showModal);
        });

        settings.deleteButton.click(function (e) {
            e.preventDefault();
            Common.deleteSelection(settings);
        });

        settings.submitButton.click(function (e) {
            e.preventDefault();
            Common.ajaxSaveForm(VehicleDialogue.setLineText, settings);
        });

        Common.setupSelect(settings.classPrefix, settings.multiSelect);
    }
};
