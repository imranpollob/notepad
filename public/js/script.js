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
        // Use jQuery to get the CSRF token and serialize the form data
        const csrfToken = $('meta[name="csrf-token"]').attr("content");
        const formData = $("#note-form").serialize();

        fetch(window.location.href, {
            // Use the current URL
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: formData,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.text(); // or response.json() if your endpoint returns JSON
            })
            .then(() => {
                $("#save-status").text("Saved"); // Use jQuery to update the save status
            })
            .catch((error) => {
                console.error(
                    "There has been a problem with your fetch operation:",
                    error
                );
            });
    }


    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    })

});
