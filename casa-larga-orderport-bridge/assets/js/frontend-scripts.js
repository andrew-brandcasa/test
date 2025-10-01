/**
 * Frontend JavaScript for Casa Larga OrderPort Bridge
 */

jQuery(document).ready(function($) {
    'use strict';

    // Age verification
    initAgeVerification();
    
    // Wine filtering
    initWineFiltering();
    
    // Image lightbox
    initImageLightbox();

    /**
     * Initialize age verification modal
     */
    function initAgeVerification() {
        // Check for age verification cookie
        function getCookie(name) {
            var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }
        
        function setCookie(name, value, days) {
            var expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
        }
        
        // Only show on wine product pages or custom label builder
        var isWinePage = $('body').hasClass('single-cl_wine_product') || 
                         $('#cl-age-gate-modal').length > 0;
        
        if (isWinePage && !getCookie('cl_age_verified')) {
            $('#cl-age-gate-modal').fadeIn();
            $('body').css('overflow', 'hidden'); // Prevent scrolling
        }
        
        $('#cl-age-yes').on('click', function() {
            setCookie('cl_age_verified', 'yes', 30);
            $('#cl-age-gate-modal').fadeOut();
            $('body').css('overflow', 'auto');
        });
        
        $('#cl-age-no').on('click', function() {
            window.location.href = 'https://casalarga.com';
        });
    }

    /**
     * Initialize wine filtering functionality
     */
    function initWineFiltering() {
        var $filters = $('.cl-wine-filters select');
        var $wineGrid = $('#wine-grid');
        var $wineItems = $wineGrid.find('.cl-wine-item');
        
        if ($filters.length === 0) return;
        
        $filters.on('change', function() {
            filterWines();
        });
        
        function filterWines() {
            var typeFilter = $('#wine-type-filter').val();
            var priceFilter = $('#price-filter').val();
            var sortFilter = $('#sort-filter').val();
            
            var $filteredItems = $wineItems.filter(function() {
                var $item = $(this);
                var show = true;
                
                // Type filter
                if (typeFilter && $item.data('type') !== typeFilter) {
                    show = false;
                }
                
                // Price filter
                if (priceFilter && show) {
                    var price = parseFloat($item.data('price')) || 0;
                    switch (priceFilter) {
                        case '0-25':
                            show = price < 25;
                            break;
                        case '25-50':
                            show = price >= 25 && price < 50;
                            break;
                        case '50-100':
                            show = price >= 50 && price < 100;
                            break;
                        case '100+':
                            show = price >= 100;
                            break;
                    }
                }
                
                return show;
            });
            
            // Sort items
            var sortedItems = $filteredItems.toArray().sort(function(a, b) {
                var $a = $(a);
                var $b = $(b);
                
                switch (sortFilter) {
                    case 'title':
                        return $a.find('.cl-wine-title').text().localeCompare($b.find('.cl-wine-title').text());
                    case 'title_desc':
                        return $b.find('.cl-wine-title').text().localeCompare($a.find('.cl-wine-title').text());
                    case 'price_asc':
                        return (parseFloat($a.data('price')) || 0) - (parseFloat($b.data('price')) || 0);
                    case 'price_desc':
                        return (parseFloat($b.data('price')) || 0) - (parseFloat($a.data('price')) || 0);
                    default:
                        return 0;
                }
            });
            
            // Update grid
            $wineGrid.empty().append(sortedItems);
            
            // Show/hide no results message
            if (sortedItems.length === 0) {
                $wineGrid.append('<div class="cl-no-results"><p>No wines found matching your criteria.</p></div>');
            }
        }
    }

    /**
     * Initialize image lightbox functionality
     */
    function initImageLightbox() {
        $('.cl-wine-image img, .cl-wine-main-image img').on('click', function() {
            var src = $(this).attr('src');
            var alt = $(this).attr('alt');
            
            var lightbox = $('<div class="cl-lightbox">' +
                '<div class="cl-lightbox-overlay"></div>' +
                '<div class="cl-lightbox-content">' +
                    '<img src="' + src + '" alt="' + alt + '">' +
                    '<button class="cl-lightbox-close">&times;</button>' +
                '</div>' +
            '</div>');
            
            $('body').append(lightbox);
            $('body').css('overflow', 'hidden');
            
            lightbox.fadeIn();
        });
        
        $(document).on('click', '.cl-lightbox-close, .cl-lightbox-overlay', function() {
            $('.cl-lightbox').fadeOut(function() {
                $(this).remove();
                $('body').css('overflow', 'auto');
            });
        });
        
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                $('.cl-lightbox').fadeOut(function() {
                    $(this).remove();
                    $('body').css('overflow', 'auto');
                });
            }
        });
    }
});

// Lightbox CSS (injected via JS)
jQuery(document).ready(function($) {
    if (!$('#cl-lightbox-styles').length) {
        $('head').append('<style id="cl-lightbox-styles">' +
            '.cl-lightbox { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999999; display: none; }' +
            '.cl-lightbox-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); }' +
            '.cl-lightbox-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-width: 90%; max-height: 90%; }' +
            '.cl-lightbox-content img { max-width: 100%; max-height: 100%; border-radius: 8px; }' +
            '.cl-lightbox-close { position: absolute; top: -40px; right: 0; background: white; border: none; font-size: 30px; cursor: pointer; border-radius: 50%; width: 40px; height: 40px; }' +
        '</style>');
    }
});
