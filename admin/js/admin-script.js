jQuery(document).ready(function($) {
    function addDeleteButton() {
        $('.fss-email-field').each(function() {
            if ($(this).find('.fss-delete-email').length === 0) {
                $(this).append('<span class="fss-delete-email">🗑</span>');
            }
        });

        $('.fss-delete-email').click(function() {
            $(this).parent().remove();
            updateLabels();
        });
    }

    $('#fss-add-email').click(function() {
        var index = $('#fss-email-fields .fss-email-field').length + 1;
        var newField = $(`<div class="fss-email-field"><label>` + fss_translations.emailAddress +  `${index}</label><input type="email" name="fss_emails[]" value="" /></div>`);
        $('#fss-email-fields').append(newField);
        updateLabels();
        addDeleteButton();
    });

    $('#fss-email-fields').sortable({
        update: function(event, ui) {
            updateLabels();
        }
    });

    $('#fss-add-cc-email').click(function() {
        var newField = $('<div class="fss-cc-email-field"><label>' + fss_translations.ccEmailAddress + '</label><input type="email" name="fss_email_cc[]" value="" /><span class="fss-delete-email">🗑</span></div>');
        $('#fss-email-cc-fields').append(newField);
    });

    $('body').on('click', '.fss-delete-email', function() {
        $(this).closest('.fss-email-field').remove();
    });

    $('form').submit(function(e) {
        $('.fss-email-error').remove();
        var isFormValid = true;
        $('input[type="email"]').each(function() {
            var emailField = $(this);
            if (emailField.val() && !emailField.val().match(/^[^@]+@[^@]+\.[^@]+$/)) {
                var errorMessage = $('<p class="fss-email-error">' + fss_translations.invalidEmail + '</p>');
                errorMessage.css({
                    color: 'red',
                    marginTop: '4px',
                    fontSize: '12px'
                });
                emailField.after(errorMessage);
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            e.preventDefault();
        }
    });

    function updateLabels() {
        $('#fss-email-fields .fss-email-field').each(function(index) {
            $(this).find('label').text( fss_translations.emailAddress + ` ${index + 1}`);
        });
    }

    addDeleteButton();
});