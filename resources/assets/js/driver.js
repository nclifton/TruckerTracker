/**
 * Created by nclifton on 29/05/2016.
 */

DriverDialogue = {

    settings: {
        url:            '/drivers',
        classPrefix:    'driver',
        lineSelector:   '.driver_line.selected',
        multiSelect:    true,
        selectors: {
            form:           '#driverForm',
            messageButton:  '#btn-messageDriver',
            editButton:     '#btn-edit-driver',
            submitButton:   '#btn-save-driver',
            addButton:      '#btn-add-driver',
            modal:          '#driverModal',
            dataIdHolder:   '#driver_id',
            deleteButton:   '#btn-delete-driver',
            lineTemplate:   '#driver'
        },
        lang: {
            addButtonLabel:     "Add Driver",
            saveButtonLabel:    "Save Changes"
        }
    },

    init: function () {
        this.settings = Common.findElements(this.settings);
        this.onUIActions(this.settings);
        return this;
    },

    prepForMessage: function (settings) {
        var dataIds = {};
        $(settings.lineSelector).each(function (i, selected) {
            var dataId = $(selected).attr('data');
            dataIds[i] = dataId;
        });
        MessageDialogue.prepForMessage(dataIds);
    },

    setLineText: function (settings, data) {
        $('#' + settings.classPrefix + data._id + ' .name')
            .text(data.first_name + ' ' + data.last_name);
    },

    showModal: function (settings) {
        settings.modal.modal('show');
    },

    onUIActions: function (settings) {

        settings.form.on("reset", function (e) {
            Common.resetErrorDisplay(e.target);
        });

        settings.messageButton.click(function (e) {
            e.preventDefault();
            DriverDialogue.prepForMessage(settings);
        });
        settings.editButton.click(function (e) {
            e.preventDefault();
            Common.prepForEdit(settings,DriverDialogue.showModal);
        });
        settings.addButton.click(function (e) {
            e.preventDefault();
            Common.prepForAdd(settings,DriverDialogue.showModal);
        });
        settings.deleteButton.click(function (e) {
            e.preventDefault();
            Common.deleteSelection(settings);
        });
        settings.submitButton.click(function (e) {
            e.preventDefault();
            Common.ajaxSaveForm(DriverDialogue.setLineText, settings);
        });

        Common.setupSelect(settings.classPrefix, settings.multiSelect);
    }

};
