$(document).ready(function () {

    //setup before functions
    let typingTimer;                //timer identifier
    let doneTypingInterval = 2000;  //time in ms (5 seconds)

    //on keyup, start the countdown
    $('#data, #title').keyup(function () {
        $('#save-status').text('Saving ...');
        clearTimeout(typingTimer);

        typingTimer = setTimeout(doneTyping, doneTypingInterval);
    });

    //user is "finished typing," do something
    function doneTyping() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '/{{ Request::path() }}',
            type: "POST",
            data: $('#note-form').serialize(),
            success: function (response) {
                console.log('saved');
                $('#save-status').text('Saved');
            }
        });
    }

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    })

});
