/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    var location_url = "/text/vehicle";

    function timeConverter(UNIX_timestamp){
        var a = new Date(UNIX_timestamp * 1000);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var year = a.getFullYear();
        var month = months[a.getMonth()];
        var date = a.getDate();
        var hour = a.getHours();
        var min = a.getMinutes();
        var sec = a.getSeconds();
        var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
        return time;
    }

    //send location request to vehicle
    $("#btn-save-location").click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        e.preventDefault();

        var formData = {
            location_text: $('#location_text').val()
        };

        var type = "POST";
        var driver_id = $('#location_id').val();
        var my_location_url = location_url + '/' + driver_id;

        console.log(formData);

        $.ajax({

            type: type,
            url: my_location_url,
            data: formData,
            dataType: 'json',
            success: function (data) {
                console.log(data);
                
                $('#frmLocation').trigger("reset");
                $('#locationModal').modal('hide');
                $('#location-list-panel').show();

                var msg;
                msg = $('#location').clone(false).prependTo('#location_list').attr("id", "location" + data._id);
                msg.find('button.open-modal-view-location').val(data._id);
                msg.find('button.delete-location').val(data._id);
                msg.find('span.registration_number').text(data.vehicle.registration_number);
                msg.find('span.location-sent_at').text(data.sent_at);
                msg.find('span.location-status').text(data.status);
                msg.show();

            },
            error: function (data) {
                console.log('Error:', data);
                if (data.status == 500) {
                    var newDoc = document.open("text/html", "replace");
                    newDoc.write(data.responseText);
                    newDoc.close();
                } else if (data.status == 422) {
                    $.each(data.responseJSON, function (index, value) {
                        var input = $('#frmLocation').find('[name="' + index + '"]');
                        input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                        input.closest('div.form-group').addClass('has-error');
                    });
                }
            }
        });
    });

});