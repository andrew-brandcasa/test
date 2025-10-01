/**
 * Admin JavaScript for Casa Larga OrderPort Bridge
 */

jQuery(document).ready(function($) {
    'use strict';

    // Connection testing
    initConnectionTesting();
    
    // Sync functionality
    initSyncFunctionality();
    
    // Form validation
    initFormValidation();

    /**
     * Initialize connection testing
     */
    function initConnectionTesting() {
        $('.cl-test-connection, .cl-test-connection-settings').on('click', function() {
            var $button = $(this);
            var $result = $button.siblings('.cl-test-result, #cl-test-result, #cl-test-result-settings');
            
            $button.prop('disabled', true).text('Testing...');
            $result.html('<div class="cl-spinner"></div> Testing connection...');
            
            $.post(ajaxurl, {
                action: 'cl_test_connection',
                nonce: clLabelBuilder.nonce
            }, function(response) {
                if (response.success) {
                    $result.html('<div class="cl-test-result success">' + response.data + '</div>');
                } else {
                    $result.html('<div class="cl-test-result error">' + response.data + '</div>');
                }
            }).always(function() {
                $button.prop('disabled', false).text('Test Connection');
            });
        });
    }

    /**
     * Initialize sync functionality
     */
    function initSyncFunctionality() {
        $('form[action*="cl_sync_now"]').on('submit', function(e) {
            var $form = $(this);
            var $button = $form.find('input[type="submit"]');
            
            $button.prop('disabled', true).val('Syncing...');
            
            // Show progress indicator
            if (!$('#cl-sync-progress').length) {
                $form.after('<div id="cl-sync-progress" class="cl-sync-progress"><div class="cl-sync-progress-bar" style="width: 0%"></div></div>');
            }
            
            // Simulate progress (since we can't track real progress easily)
            var progress = 0;
            var interval = setInterval(function() {
                progress += Math.random() * 20;
                if (progress > 90) progress = 90;
                $('#cl-sync-progress .cl-sync-progress-bar').css('width', progress + '%');
            }, 500);
            
            // Clear interval after form submission
            setTimeout(function() {
                clearInterval(interval);
                $('#cl-sync-progress .cl-sync-progress-bar').css('width', '100%');
                setTimeout(function() {
                    $('#cl-sync-progress').fadeOut();
                }, 1000);
            }, 3000);
        });
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        $('form').on('submit', function(e) {
            var $form = $(this);
            var isValid = true;
            
            // Check required fields
            $form.find('[required]').each(function() {
                var $field = $(this);
                if (!$field.val().trim()) {
                    $field.addClass('error');
                    isValid = false;
                } else {
                    $field.removeClass('error');
                }
            });
            
            // Email validation
            $form.find('input[type="email"]').each(function() {
                var $field = $(this);
                var email = $field.val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $field.addClass('error');
                    isValid = false;
                } else {
                    $field.removeClass('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotice('Please fill in all required fields correctly.', 'error');
            }
        });
    }

    /**
     * Show admin notice
     */
    function showNotice(message, type) {
        type = type || 'info';
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        $('[data-tooltip]').hover(
            function() {
                var tooltip = $(this).data('tooltip');
                $(this).append('<div class="cl-tooltip">' + tooltip + '</div>');
            },
            function() {
                $(this).find('.cl-tooltip').remove();
            }
        );
    }

    // Initialize tooltips
    initTooltips();
});

// Add CSS for admin enhancements
jQuery(document).ready(function($) {
    if (!$('#cl-admin-enhancements').length) {
        $('head').append('<style id="cl-admin-enhancements">' +
            '.cl-sync-progress { background: #f0f0f0; border-radius: 4px; overflow: hidden; margin: 10px 0; height: 20px; }' +
            '.cl-sync-progress-bar { background: #8B0000; height: 100%; transition: width 0.3s ease; }' +
            '.cl-test-result { margin-top: 10px; padding: 10px; border-radius: 4px; }' +
            '.cl-test-result.success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }' +
            '.cl-test-result.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }' +
            '.cl-spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid #f3f3f3; border-top: 3px solid #8B0000; border-radius: 50%; animation: cl-spin 1s linear infinite; margin-right: 10px; }' +
            '@keyframes cl-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }' +
            '.cl-tooltip { position: absolute; background: #333; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px; z-index: 1000; margin-top: 5px; }' +
            'input.error, select.error, textarea.error { border-color: #dc3232 !important; }' +
        '</style>');
    }
});
