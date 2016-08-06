/**
 * Created by nclifton on 29/05/2016.
 */
var OrganisationDialogue = {

        settings: {
            url:                '/organisation',
            selectors:          {
                orgId:              '#org_id',
                addButton:          '#btn-add-org',
                editButton:         '#btn-edit-org',
                submitButton:       '#btn-save-org',
                addDriverButton:    '#btn-add-driver',
                addVehicleButton:   '#btn-add-vehicle',
                configForm:         '#orgConfigForm',
                twilioForm:         '#orgTwilioForm',
                modal:              '#orgModal',
                usersTabLink:       '#org-users-tab-link',
                orgNameHeading:     '#heading_org_name'
            },
            lang:{
                addOrg:         'Add Organisation',
                editOrg:        'Edit Organisation',
                saveChanges:    'Save Changes'
            }
        },

        init: function () {
            this.settings = Common.findElements(this.settings);
            OrganisationDialogue.onUIActions(this.settings);
            if (this.settings.orgId.val() == '')
                OrganisationDialogue.prepForAdd(this.settings)
            return this;
        },

        prepForAdd: function (settings) {
            settings.submitButton
                .val("add")
                .text(settings.lang.addOrg);
            settings.configForm.trigger("reset");
            settings.twilioForm.trigger("reset");
            settings.modal.modal('show');
        },

        resetErrorDisplay: function (form) {
            Common.resetErrorDisplay(form);
        },

        getOrgSuccess: function (data, settings) {
            console.log(data);

            settings.configForm.trigger("reset");
            settings.twilioForm.trigger("reset");
            settings.usersTabLink.parent().removeClass('disabled');
            settings.usersTabLink.attr('data-toggle', 'tab');

            for (var i in data) {
                if (i == "users") {
                    var users = data[i];
                    for (var j in users) {
                        var user = users[j];
                        if (!$("#user" + user._id).length) {
                            var usr = $('#user')
                                .clone(false)
                                .prependTo('#user_list')
                                .attr("id", "user" + user._id)
                                .attr('data', user._id)
                                .show();
                        }
                        $('#user' + user._id + ' span.name_email')
                            .text(user.name + ' ' + user.email);
                    }
                } else if (i == "hour12") {
                    Common.settings.time_format_hour12 = data[i];
                } else if (i == "_id") {
                    settings.orgId.val(data[i]);
                    Common.settings.orgId = data[i];
                } else {
                    settings.configForm.find('[name="' + i + '"]').val(data[i]);
                    settings.twilioForm.find('[name="' + i + '"]').val(data[i]);
                }
            }
            settings.submitButton.val("update").text(settings.lang.saveChanges);
            settings.modal.modal('show');
        },

        getOrgIdFromElement: function (selector) {
            var orgId = $(selector).val();
            if (!orgId) {
                orgId = $(selector).attr('data');
            }
            return orgId;
        },

        prepForEdit: function (button, settings) {
            var orgId = OrganisationDialogue.getOrgIdFromElement(button);
            var url = settings.url + '/' + orgId;
            $.get(url, function (data) {
                OrganisationDialogue.getOrgSuccess(data, settings);
            }).fail(function (data) {
                Common.handleServerError(data);
            });
        },

        handleAjaxError: function (data, settings) {
            if (data.status == 422) {
                settings.configForm.trigger('reset');
                settings.twilioForm.trigger('reset');
                $.each(data.responseJSON, function (index, value) {
                    var input = settings.configForm.find('[name="' + index + '"]');
                    if (!input.length)
                        input = settings.twilioForm.find('[name="' + index + '"]');
                    if (input) {
                        input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                        input.closest('div.form-group').addClass('has-error');
                        var panelId = input.closest('div[role="tabpanel"]').attr('id');
                        $('a[href="#' + panelId + '"][aria-expanded="false"]').click();
                    }
                });
            } else {
                Common.handleAjaxError(data);
            }
        },

        saveOrgSuccess: function (data, settings) {
            console.log(data);
            settings.orgNameHeading.html(data.name);
            Common.settings.time_format_hour12 = data.hour12;
            settings.orgId.val(data._id);
            Common.settings.orgId = data._id;
            Common.setupSse();

            settings.addButton.attr('name', settings.selectors.editButton.substr(1))
                .attr('data', data._id)
                .text(settings.lang.editOrg)
                .attr('id', settings.selectors.editButton.substr(1));
            settings.editButton = $(settings.selectors.editButton);
            settings.addButton = $(settings.selectors.addButton);
            OrganisationDialogue.settings = settings;

            settings.editButton.off('click').click(function (e) {
                e.preventDefault();
                OrganisationDialogue.prepForEdit(this, settings);
            });

            settings.submitButton.val('update')
                .text(settings.lang.saveChanges);
            settings.orgId.val(data._id);

            settings.addDriverButton.show();
            settings.addVehicleButton.show();
            settings.modal.modal('hide');


        },

        saveOrg: function (settings) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            var formData = $(settings.configForm).serializeFormJSON();
            var twilioFormData = $(settings.twilioForm).serializeFormJSON();
            for (var key in twilioFormData) {
                formData[key] = twilioFormData[key];
            }
            //used to determine the http verb to use [add=POST], [update=PUT]
            var state = settings.submitButton.val();
            var type = "POST"; //for creating new resource
            var url = settings.url;
            if (state == "update") {
                var orgId = settings.orgId.val();
                type = "PUT"; //for updating existing resource
                url += '/' + orgId;
            }
            console.log(formData);
            $.ajax({
                type: type,
                url: url,
                data: formData,
                dataType: 'json',
                success: function (data) {
                    OrganisationDialogue.saveOrgSuccess(data, settings);
                },
                error: function (data) {
                    OrganisationDialogue.handleAjaxError(data, settings);
                }
            });
        },

        onUIActions: function (settings) {
            settings.addButton.click(function (e) {
                e.preventDefault();
                OrganisationDialogue.prepForAdd(settings);
            });
            settings.configForm.on('reset', function (e) {
                Common.resetErrorDisplay(e.target);
            });
            settings.twilioForm.on('reset', function (e) {
                Common.resetErrorDisplay(e.target);
            });
            settings.usersTabLink.on('shown.bs.tab', function () {
                Common.adjustFluidColumns();
            });
            settings.editButton.off('click').click(function (e) {
                e.preventDefault();
                OrganisationDialogue.prepForEdit(this, settings);
            });
            settings.submitButton.click(function (e) {
                e.preventDefault();
                OrganisationDialogue.saveOrg(settings);
            });



        }

    };
