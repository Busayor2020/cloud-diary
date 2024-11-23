jQuery(document).ready(function($) {
    var $notesContent = $('#user-notes-content');
    var $saveButton = $('#user-notes-save');
    var $savedMessage = $('.notes-saved-message');
    var storageKey = 'userNotes'; // Key for localStorage

    // Function to check if localStorage is available
    function isLocalStorageAvailable() {
        try {
            var test = '__storage_test__';
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch (e) {
            return false;
        }
    }

    // Load notes from local storage if available
    if (isLocalStorageAvailable()) {
        var savedNotes = localStorage.getItem(storageKey);
        if (savedNotes) {
            $notesContent.val(savedNotes);  // Set the textarea content with the stored note
        }
    } else {
        console.warn('Local storage not available. Notes will not be saved locally.');
    }

    // Save notes to local storage every time the user types (real-time save)
    $notesContent.on('input', function() {
        var notes = $(this).val();
        if (isLocalStorageAvailable()) {
            localStorage.setItem(storageKey, notes);  // Store the note in localStorage
        }
    });

    // Handle the "Save Notes" button click to save notes to the server and localStorage
    $saveButton.on('click', function() {
        var notes = $notesContent.val();

        // Save to local storage if available
        if (isLocalStorageAvailable()) {
            localStorage.setItem(storageKey, notes);
        }

        // Save to the server using AJAX
        $.ajax({
            url: userNotesAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'save_user_notes',
                nonce: userNotesAjax.nonce,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    // Show success alert
                    alert('Note Saved Successfully');

                    // Also display success message in the UI
                    $savedMessage.text('Notes saved successfully').fadeIn().delay(3000).fadeOut();
                } else {
                    $savedMessage.text('Error saving notes: ' + response.data).fadeIn().delay(3000).fadeOut();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                $savedMessage.text('Error saving notes: ' + error).fadeIn().delay(3000).fadeOut();
            }
        });
    });
});
