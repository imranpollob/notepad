$(document).ready(function () {

    //setup before functions
    let typingTimer;                //timer identifier
    let doneTypingInterval = 2000;  //time in ms (5 seconds)

    $('#data').summernote({
        minHeight: 300,
        focus: true,
        placeholder: "Just dump data!!",
        callbacks: {
            onChange: function () {
                $('#save-status').text('Saving ...');
                clearTimeout(typingTimer);

                typingTimer = setTimeout(doneTyping, doneTypingInterval);
            }
        },
        followingToolbar: true
    });

    //on keyup, start the countdown
    $('#title').keyup(function () {
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
            // url: '/{{ Request::path() }}',
            type: "POST",
            data: $('#note-form').serialize(),
            success: function () {
                $('#save-status').text('Saved');
            }
        });
    }

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    })

});
