/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function ($) {

    var organisation_url = "/organisation";

    $('.message-organisation').click(function () {
        var id = $(this).val();

        $.get("/text/" + id, function (data) {

            if (data == "OK") {

                $("#" + id).addClass("done");
            }

        });
    });

    //display modal form for creating new organisation
    $('#btn-add-organisation').click(function () {
        $('#btn-save-organisation').val("add");
        $('#btn-save-organisation').text('Add Organisation');
        $('#frmOrganisation').trigger("reset");
        $('#organisationModal').modal('show');
    });
    
    //display modal form for organisation editing

        $('.open-modal-organisation').click(function (e) {
            var organisation_id = $(this).val();

            $.get(organisation_url + '/' + organisation_id, function (data) {
                //success data
                console.log(data);
                $('#organisation_id').val(data._id);
                $('#organisation_name').val(data.name);
                $('#btn-save-organisation').val("update");
                $('#organisationModal').modal('show');
            }).fail(function(data){
                var newDoc = document.open("text/html", "replace");
                newDoc.write(data.responseText);
                newDoc.close();
            });
        });


    //create new organisation / update existing driver
    $("#btn-save-organisation").click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        e.preventDefault();

        var formData = {
            name: $('#organisation_name').val()
        };

        //used to determine the http verb to use [add=POST], [update=PUT]
        var state = $('#btn-save-organisation').val();

        var type = "POST"; //for creating new resource
        var organisation_id = $('#organisation_id').val();
        var my_organisation_url = organisation_url;

        if (state == "update") {
            type = "PUT"; //for updating existing resource
            my_organisation_url += '/' + organisation_id;
        }

        console.log(formData);

        $.ajax({

            type: type,
            url: my_organisation_url,
            data: formData,
            dataType: 'json',
            success: function (data) {
                console.log(data);

                $("#heading_organisation_name").html(data.name);
                $("#btn-edit-organisation").val(data._id);

                $("#btn-add-organisation").hide();
                $("#btn-edit-organisation").show();
                $('#btn-add-driver').prop('disabled',false);
                $('#btn-add-vehicle').prop('disabled',false);

                $('#frmOrganisation').trigger("reset");
                $('#organisationModal').modal('hide');
                $("#btn-save-organisation").html('Save Changes');


            },
            error: function (data) {
                console.log('Error:', data);
                if (data.status == 500) {
                    var newDoc = document.open("text/html", "replace");
                    newDoc.write(data.responseText);
                    newDoc.close();
                } else if (data.status == 422) {
                    $.each(data.responseJSON, function (index, value) {
                        var input = $('#frmOrganisation').find('[name="' + index + '"]');
                        input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                        input.closest('div.form-group').addClass('has-error');
                    });
                }
            }
        });
    });


    //display modal form for creating new organisation
    if (!$("#organisation_id").val() ) {
        $('#btn-save-organisation').val("add");
        $('#frmOrganisation').trigger("reset");
        $('#organisationModal').modal('show');
    }

});