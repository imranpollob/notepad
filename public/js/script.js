$(document).ready(function () {
    const editable = String($('#note-form').data('editable')) === '1';

    //setup before functions
    let typingTimer;                //timer identifier
    let doneTypingInterval = 2000;  //time in ms (5 seconds)

    $('#data').summernote({
        minHeight: 300,
        focus: true,
        placeholder: "Just dump data!!",
        callbacks: {
            onChange: function () {
                if (!editable) {
                    return;
                }

                $('#save-status').text('Saving ...');
                clearTimeout(typingTimer);

                typingTimer = setTimeout(doneTyping, doneTypingInterval);
            }
        },
        followingToolbar: true
    });

    if (!editable) {
        $('#data').summernote('disable');
        $('#title').prop('disabled', true);
    }

    //on keyup, start the countdown
    $('#title').keyup(function () {
        if (!editable) {
            return;
        }

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
            type: "POST",
            data: $('#note-form').serialize(),
            success: function () {
                $('#save-status').text('Saved');
            },
            error: function () {
                $('#save-status').text('Save failed');
            }
        });
    }

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    })

});
