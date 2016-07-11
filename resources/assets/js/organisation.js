/**
 * Created by nclifton on 29/05/2016.
 */

$(document).ready(function ($) {

    var org_url = "/organisation";

    //display modal form for creating new organisation
    $('#btn-add-org').click(function () {
        $('#btn-save-org').val("add");
        $('#btn-save-org').text('Add Organisation');
        $('#orgConfigForm').trigger("reset");
        $('#orgTwilioForm').trigger("reset");
        $('#orgModal').modal('show');
    });

    function setup_reset_config_form(){
        $('#orgConfigForm').on("reset",function(){
            var $form = $('#orgConfigForm');
            $form.find('.help-block').remove();
            $form.find('.form-group').removeClass('has-error');
        });
    }
    setup_reset_config_form();

    function setup_reset_twilio_form(){
        $('#orgTwilioForm').on("reset",function(){
            var $form = $('#orgTwilioForm');
            $form.find('.help-block').remove();
            $form.find('.form-group').removeClass('has-error');
        });
    }
    setup_reset_twilio_form();

    $('#org-users-tab-link').on('shown.bs.tab', function (e) {
        adjust_fluid_columns();
    });

    function setup_org_edit_button(){
        $('#btn-edit-org').off('click').click(function (e) {
            e.preventDefault();
            var org_id = $(this).val();
            if (!org_id){
                org_id = $(this).attr('value');
            }
            if (!org_id){
                org_id = $(this).attr('data');
            }

            $.get(org_url + '/' + org_id, function (data) {
                //success data
                console.log(data);
                $('#orgConfigForm').trigger("reset");
                $('#orgTwilioForm').trigger("reset");
                $('#org-users-tab-link').parent().removeClass('disabled');
                $('#org-users-tab-link').attr('data-toggle', 'tab');

                for (var i in data) {
                    if (i == "users") {
                        var users = data[i];
                        for (var j in users) {
                            var user = users[j];
                            if (!$("#user" + user._id).length) {
                                var usr = $('#user').clone(false).prependTo('#user_list').attr("id", "user" + user._id);
                                usr.show();
                            }
                            $('user' + user._id + ' span.name').text(user.name);
                            $('user' + user._id + ' span.email').text(user.email);
                            $('user' + user._id + ' button.delete-user').val(user._id).show();

                        }
                    } else if (i == "_id") {
                        $('#org_id').val(data[i]);
                    } else {
                        $('#orgConfigForm [name="' + i + '"], #orgTwilioForm [name="' + i + '"] ').val(data[i]);
                    }
                }
                $('#btn-save-org').val("update");
                $('#btn-save-org').text('Save Changes');
                $('#orgModal').modal('show');

            }).fail(function (data) {
                var newDoc = document.open("text/html", "replace");
                newDoc.write(data.responseText);
                newDoc.close();
            });
        });
    }

    setup_org_edit_button();


    //create new organisation 

    $("#btn-save-org").click(function (e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        var formData = $('#orgConfigForm').serializeFormJSON();
        var twilioFormData = $('#orgTwilioForm').serializeFormJSON();
        for (var key in twilioFormData) {
            formData[key] = twilioFormData[key];
        }
        //used to determine the http verb to use [add=POST], [update=PUT]
        var state = $('#btn-save-org').val();
        var type = "POST"; //for creating new resource
        var org_id = $('#org_id').val();
        var my_org_url = org_url;
        if (state == "update") {
            type = "PUT"; //for updating existing resource
            my_org_url += '/' + org_id;
        }
        console.log(formData);
        $.ajax({
            type: type,
            url: my_org_url,
            data: formData,
            dataType: 'json',
            success: function (data) {
                console.log(data);
                $("#heading_org_name").html(data.name);

                $("#btn-add-org").attr('name','btn-edit-org');
                $("#btn-add-org").attr('data',data._id);
                $("#btn-add-org").text('Edit Organisation');
                $("#btn-add-org").attr('id','btn-edit-org');

                $("#btn-save-org").val('update');
                $("#org_id").val(data._id);
                $('#btn-add-driver').show();
                $('#btn-add-vehicle').show();
                $('#orgModal').modal('hide');
                $("#btn-save-org").text('Save Changes');

                setup_org_edit_button()
            },
            error: function (data) {
                handleAjaxError(data);
                if (data.status == 422) {
                    $('#orgConfigForm span.help-block').remove();
                    $('#orgTwilioForm span.help-block').remove();
                    $.each(data.responseJSON, function (index, value) {
                        var input = $('#orgConfigForm').find('[name="' + index + '"]');
                        if (!input.length)
                            input = $('#orgTwilioForm').find('[name="' + index + '"]');
                        if (input){
                            input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                            input.closest('div.form-group').addClass('has-error');
                            var panelId = input.closest('div[role="tabpanel"]').attr('id');
                            $('a[href="#' + panelId + '"][aria-expanded="false"]').click();
                        }
                    });
                }
            }
        });
    });

    // force display modal form for creating a new organisation
    if (!$("#org_id").val()) {
        $('#btn-save-org').val("add");
        $('#orgConfigForm').trigger("reset");
        $('#orgTwilioForm').trigger("reset");
        $('#orgModal').modal('show');
    }

});