/**
 * Created by nclifton on 29/05/2016.
 */

$(document).ready(function ($) {

    var org_url = "/organisation";

    //display modal form for creating new organisation
    $('#btn-add-org').click(function () {
        $('#btn-save-org').val("add");
        $('#btn-save-org').text('Add Organisation');
        $('#orgForm').trigger("reset");
        $('#orgModal').modal('show');
    });

    //display modal form for org editing

    $('.open-modal-org').click(function (e) {
        var org_id = $(this).val();

        $.get(org_url + '/' + org_id, function (data) {
            //success data
            console.log(data);
            $('#orgForm').trigger("reset");

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
                        $('user' + user._id + ' button.delete-user').val(user._id);
                        $('user' + user._id + ' button.delete-user').show();

                    }
                } else if (i == "_id") {
                    $('#org_id').val(data[i]);
                } else {
                    $('#orgForm [name="' + i + '"]').val(data[i]);
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


    //create new organisation / update existing driver

    $("#btn-save-org").click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        e.preventDefault();
        var formData = $('#orgForm').serializeFormJSON();
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
                    $('#orgForm span.help-block').remove();
                    $.each(data.responseJSON, function (index, value) {
                        var input = $('#orgForm').find('[name="' + index + '"]');
                        input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                        input.closest('div.form-group').addClass('has-error');
                        var panelId = input.closest('div[role="tabpanel"]').attr('id');
                        $('a[href="#'+panelId+'"][aria-expanded="false"]').click();
                    });
                }
            }
        });
    });
    
    // force display modal form for creating a new organisation
    if (!$("#org_id").val()) {
        $('#btn-save-org').val("add");
        $('#orgForm').trigger("reset");
        $('#orgModal').modal('show');
    }

});