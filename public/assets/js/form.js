$(document).ready(function () {
    $('#form-contact').on('submit', function (e) {
        e.preventDefault();        
        $.ajax({  
            type: 'POST',
            url: '/landing',
            data: $('#form-contact').serialize(),
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.result) {
                    $('#senderror').hide();
                    $('#sendmessage').show();
                } else {
                    $('#senderror').show();
                    $('#sendmessage').hide();
                }
            },
            error: function () {
                $('#senderror').show();
                $('#sendmessage').hide();
            }
        });
    });
});