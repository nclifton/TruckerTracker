/**
 * Created by nclifton on 16/06/2016.
 */

var UserDialogue = {
    settings: {
        url: '/user',
        lineSelector: '.user_line.selected',
        classPrefix: 'user',
        multiSelect: false,
        selectors: {
            modal: '#userModal',
            form: '#userForm',
            addButton: '#btn-add-user',
            editButton: '#btn-edit-user',
            submitButton: '#btn-save-user',
            dataIdHolder: '#user_id',
            deleteButton: '#btn-delete-user',
            lineTemplate: '#user',
            modalLabel: '#userModalLabel',
            name: '#user_name',
            email: '#email',
            currentPassword: '#current_password',
        },

        lang: {
            modalLabelTextRegister: 'Register Organisation User',
            saveButtonTextRegister: 'Register',
            modalLabelTextEdit: 'Edit Organisation User',
            saveButtonTextEdit: 'Save',
            saveButtonLabel: 'Save Changes'

        }

    },

    init: function () {
        this.settings = Common.findElements(this.settings);
        this.onUIActions(this.settings);
        return this;
    },

    switchModals: function (settings) {
        OrganisationDialogue.settings.modal.on('hidden.bs.modal', function () {
            settings.modal.modal('show');
        });
        settings.modal.off('hidden.bs.modal');
        settings.modal.on('hidden.bs.modal', function () {
            OrganisationDialogue.settings.modal.modal('show');
            OrganisationDialogue.settings.modal.off('hidden.bs.modal');
        });
        OrganisationDialogue.settings.submitButton.click();
    },

    setLineText: function (settings, data) {
        $(settings.lineTemplate.selector + data._id + ' span.name_email')
            .text(data.name + ' ' + data.email);
        settings.modalLabel.text(settings.lang.modalLabelTextEdit);

    },

    onUIActions: function (settings) {

        settings.form.on("reset", function (e) {
            Common.resetErrorDisplay(e.target);
        });

        settings.addButton.click(function (e) {
            e.preventDefault();
            Common.prepForAdd(settings, UserDialogue.switchModals);
        });

        settings.editButton.click(function (e) {
            e.preventDefault();
            Common.prepForEdit(settings, UserDialogue.switchModals);
        });

        settings.deleteButton.click(function (e) {
            e.preventDefault();
            Common.deleteSelection(settings);
        });

        settings.submitButton.click(function (e) {
            e.preventDefault();
            Common.ajaxSaveForm(UserDialogue.setLineText, settings);
        });

        Common.setupSelect(settings.classPrefix, settings.multiSelect);
    }
};
