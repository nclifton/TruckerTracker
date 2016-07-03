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

    $('#orgConfigForm').on('reset',function(){
        $('#orgConfigForm span.help-block').remove();
    });
    $('#orgTwilioForm').on('reset',function(){
        $('#orgTwilioForm span.help-block').remove();
    });

    //display modal form for org editing

    $('.open-modal-org').click(function (e) {
        var org_id = $(this).val();

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
                            usr = $('#user').clone(false).prependTo('#user_list').attr("id", "user" + user._id);
                            usr.show();
                        }
                        $('user' + user._id + ' span.name').text(user.name);
                        $('user' + user._id + ' span.email').text(user.email);
                        $('user' + user._id + ' button.delete-user').val(user._id).show();

                    }
                } else if (i == "_id") {
                    $('#org_id').val(data[i]);
                } else {
                    $('#orgConfigForm [name="' + i + '"]').val(data[i]);
                    $('#orgTwilioForm [name="' + i + '"]').val(data[i]);
                }
            }

            $('#btn-save-org').val("update");
            $('#orgModal').modal('show');
        }).fail(function (data) {
            var newDoc = document.open("text/html", "replace");
            newDoc.write(data.responseText);
            newDoc.close();
        });
    });


    //create new organisation 

    $("#btn-save-org").click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        e.preventDefault();
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
                organisation_id = data._id;
                $("#btn-edit-org").val(data._id);
                $("#btn-save-org").val('update');
                $("#org_id").val(data._id);
                $("#btn-add-org").hide();
                $("#btn-edit-org").show();
                $('#btn-add-driver').prop('disabled', false);
                $('#btn-add-vehicle').prop('disabled', false);
                $('#orgModal').modal('hide');
                $("#btn-save-org").html('Save Changes');
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