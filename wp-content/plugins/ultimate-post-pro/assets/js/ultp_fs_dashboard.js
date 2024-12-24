( function($) {    
    jQuery( document ).ready( function() {
        // Get the input field and label elements.
        const searchField = $('#ultp_fs_search_posts');
        const searchButton = $('#ultp_fs_search_post_action_btn');

        // Hide the searchButton initially.
        searchButton.hide();

        // Listen for changes to the input field.
        searchField.on('keyup change', function() {
            // If the input field has any content, show the searchButton.
            if (searchField.val() !== '') {
            searchButton.show();
            } else {
            // Otherwise, hide the searchButton.
            searchButton.hide();
            }
        });

        $('.ultp-fs-delete-post-btn').click(function() {
            $('#ultp-fs-delete-post-modal').show();
            $("#ultp-fs-delete-post-link").attr('href',$(this).attr('data-url'));
        });

        $('#ultp-fs-delete-post-modal-cancel').click(function(){
            $('#ultp-fs-delete-post-modal').hide();
        });
    } );
} )(jQuery);