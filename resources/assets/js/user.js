
/**
 * Created by nclifton on 16/06/2016.
 */
$(document).ready(function ($) {

    var url_prefix = "/organisation/";

    // attach an open form action to the button / link used to register a new secondary user that will be linked to the organisation
    // make sure that the org modal is hidden first

    function switchModals(e) {
        e.preventDefault();
        $('#orgModal').on('hidden.bs.modal', function () {
            $('#userModal').modal('show');
        });
        $('#userModal').on('hidden.bs.modal', function () {
            $('#orgModal').modal('show');
            $('#orgModal').off('hidden.bs.modal');
        });

        $("#btn-save-org").click();
    }

    $('#btn-add-user').click(function (e) {
        switchModals(e);
    });

    //display modal form for user editing
    function setup_edit_user() {
        $('.open-modal-user').click(function (e) {
            e.preventDefault();
            var org_id = $('#org_id').val();
            var user_id = $(this).val();

            $.get(url_prefix + 'user/' + user_id, function (data) {
                //success data
                console.log(data);
                $('#user_id').val(data._id);
                $('#user_name').val(data.name);
                $('#email').val(data.email);
                $('#btn-save-user').val("update");
                $('#current_password').show();

                switchModals(e);

            }).fail(function(data){
                handleAjaxError(data);
            });
        });
    }

    setup_edit_user();

    //delete driver and remove it from list

    function setup_delete_user() {
        $('.delete-user').click(function (e) {
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            var org_id = $('#org_id').val();
            var user_id = $(this).val();

            $.ajax({

                type: "DELETE",
                url: url_prefix + 'user/' + user_id,
                success: function (data) {
                    console.log(data);
                    $("#user" + user_id).remove();
                },
                error: function (data) {

                    handleAjaxError(data);

                }
            });
        });
    }

    setup_delete_user();

    $("#btn-save-user").click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        e.preventDefault();
        var formData = $('#userForm').serializeFormJSON();

        //used to determine the http verb to use [add=POST], [update=PUT]
        var state = $('#btn-save-user').val();
        var type = "POST"; //for creating new resource
        var org_id = $('#org_id').val();
        var user_id = $('#user_id').val();
        var this_url = url_prefix + org_id + '/user';
        if (state == "update") {
            type = "PUT"; //for updating existing resource
            this_url = url_prefix + 'user/' + user_id;
            // blank password and confirm means unchanged password when updating user
            if (formData.password == '' && formData.password_confirmation == ''){
                delete formData.password;
                delete formData.password_confirmation;
            }
        }
        console.log(formData);
        $.ajax({
            type: type,
            url: this_url,
            data: formData,
            dataType: 'json',
            success: function (data) {
                console.log(data);

                if (state == "add") { //if user added a new record
                    $('#btn-save-user').text("Save Changes");
                    $('#user').clone(false).prependTo('#user_list').attr("id", "user" + data._id);
                    $("#user" + data._id + ' button.open-modal-user').val(data._id);
                    $("#user" + data._id + ' button.delete-user').val(data._id);
                    $("#user" + data._id).css('display','');
                }
                $("#user" + data._id + ' span.name').text(data.name);
                $("#user" + data._id + ' span.email').text(data.email);
                
                $('#userForm').trigger("reset");
                $('#userModal').modal('hide');
                setup_edit_user();
                setup_delete_user();

            },
            error: function (data) {
                handleAjaxError(data);
                if (data.status == 422) {
                    $('#userForm span.help-block').remove();
                    $.each(data.responseJSON, function (index, value) {
                        var input = $('#userForm').find('[name="' + index + '"]');
                        input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                        input.closest('div.form-group').addClass('has-error');
                    });
                }

            }
        });
    });

});