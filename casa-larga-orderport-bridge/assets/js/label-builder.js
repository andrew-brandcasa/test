/**
 * Custom Label Builder JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    var canvas, isDrawing = false;
    var selectedObject = null;
    var labelWidth = 500;
    var labelHeight = 700;

    // Initialize the label builder
    initLabelBuilder();

    function initLabelBuilder() {
        // Initialize Fabric.js canvas
        canvas = new fabric.Canvas('label-canvas', {
            width: labelWidth,
            height: labelHeight,
            backgroundColor: '#ffffff'
        });

        // Event listeners
        setupEventListeners();
        
        // Initialize pricing calculator
        initPricingCalculator();
    }

    function setupEventListeners() {
        // Add text button
        $('#add-text').on('click', function() {
            addText();
        });

        // Add image button
        $('#add-image').on('click', function() {
            $('#image-upload').click();
        });

        // Image upload
        $('#image-upload').on('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                addImage(file);
            }
        });

        // Font controls
        $('#font-family').on('change', function() {
            updateSelectedObject('fontFamily', $(this).val());
        });

        $('#font-size').on('input', function() {
            var size = $(this).val();
            $('#font-size-value').text(size + 'px');
            updateSelectedObject('fontSize', parseInt(size));
        });

        $('#text-color').on('change', function() {
            updateSelectedObject('fill', $(this).val());
        });

        // Canvas events
        canvas.on('selection:created', function(e) {
            selectedObject = e.selected[0];
            updateControls();
        });

        canvas.on('selection:updated', function(e) {
            selectedObject = e.selected[0];
            updateControls();
        });

        canvas.on('selection:cleared', function() {
            selectedObject = null;
            resetControls();
        });

        // Object modification
        canvas.on('object:modified', function() {
            saveDesign();
        });

        // Order form submission
        $('#custom-label-order-form').on('submit', function(e) {
            e.preventDefault();
            submitOrder();
        });
    }

    function addText() {
        var text = new fabric.Text('Your Text Here', {
            left: 100,
            top: 100,
            fontFamily: 'Arial',
            fontSize: 24,
            fill: '#000000'
        });
        
        canvas.add(text);
        canvas.setActiveObject(text);
        canvas.renderAll();
    }

    function addImage(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            fabric.Image.fromURL(e.target.result, function(img) {
                // Scale image to fit within label bounds
                var scale = Math.min(
                    (labelWidth - 20) / img.width,
                    (labelHeight - 20) / img.height
                );
                img.scale(scale);
                img.set({
                    left: (labelWidth - img.width * scale) / 2,
                    top: (labelHeight - img.height * scale) / 2
                });
                
                canvas.add(img);
                canvas.setActiveObject(img);
                canvas.renderAll();
            });
        };
        reader.readAsDataURL(file);
    }

    function updateSelectedObject(property, value) {
        if (selectedObject) {
            selectedObject.set(property, value);
            canvas.renderAll();
        }
    }

    function updateControls() {
        if (selectedObject && selectedObject.type === 'text') {
            $('#font-family').val(selectedObject.fontFamily || 'Arial');
            $('#font-size').val(selectedObject.fontSize || 24);
            $('#text-color').val(selectedObject.fill || '#000000');
        }
    }

    function resetControls() {
        $('#font-family').val('Arial');
        $('#font-size').val('24');
        $('#text-color').val('#000000');
    }

    function initPricingCalculator() {
        $('#quantity').on('input', calculatePricing);
        $('#wine-opsku').on('change', calculatePricing);
    }

    function calculatePricing() {
        var quantity = parseInt($('#quantity').val()) || 0;
        var winePrice = parseFloat($('#wine-opsku option:selected').data('price')) || 0;
        
        // Calculate label price based on quantity
        var labelPrice = 0;
        if (quantity >= 1 && quantity <= 11) {
            labelPrice = 5.00;
        } else if (quantity >= 12 && quantity <= 23) {
            labelPrice = 4.00;
        } else if (quantity >= 24 && quantity <= 47) {
            labelPrice = 3.50;
        } else if (quantity >= 48 && quantity <= 59) {
            labelPrice = 3.00;
        } else if (quantity >= 60) {
            labelPrice = 2.50;
        }
        
        var wineTotal = winePrice * quantity;
        var labelTotal = labelPrice * quantity;
        var grandTotal = wineTotal + labelTotal;
        
        $('#total-price').text(grandTotal.toFixed(2));
        
        // Update selected wine info
        var selectedWine = $('#wine-opsku option:selected').text();
        var selectedWineName = $('<div>').text(selectedWine).html(); // Escapes HTML
        $('#selected-wine-info').html(
            '<strong>Selected:</strong> ' + selectedWineName + '<br>' +
            '<strong>Wine Total:</strong> $' + wineTotal.toFixed(2) + '<br>' +
            '<strong>Label Total:</strong> $' + labelTotal.toFixed(2)
        );
    }

    function saveDesign() {
        var designData = JSON.stringify(canvas.toJSON());
        localStorage.setItem('cl_label_design', designData);
    }

    function loadDesign() {
        var designData = localStorage.getItem('cl_label_design');
        if (designData) {
            canvas.loadFromJSON(designData, function() {
                canvas.renderAll();
            });
        }
    }

    function submitOrder() {
        var formData = new FormData();
        
        // Get form data
        formData.append('customer_name', $('#customer-name').val());
        formData.append('customer_email', $('#customer-email').val());
        formData.append('customer_phone', $('#customer-phone').val());
        formData.append('wine_opsku', $('#wine-opsku').val());
        formData.append('quantity', $('#quantity').val());
        formData.append('design_json', JSON.stringify(canvas.toJSON()));
        
        // Get canvas as image
        var canvasData = canvas.toDataURL('image/png');
        var blob = dataURLToBlob(canvasData);
        formData.append('design_image', blob, 'label-design.png');
        
        // Submit order
        $.ajax({
            url: clLabelBuilder.resturl + 'custom-label-order',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', clLabelBuilder.nonce);
                $('.cl-submit-order').prop('disabled', true).text('Submitting...');
            },
            success: function(response) {
                if (response.success) {
                    alert('Order submitted successfully! We will contact you within 1-2 business days.');
                    // Reset form
                    $('#custom-label-order-form')[0].reset();
                    canvas.clear();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while submitting your order. Please try again.');
            },
            complete: function() {
                $('.cl-submit-order').prop('disabled', false).text('Submit Order');
            }
        });
    }

    function dataURLToBlob(dataURL) {
        var arr = dataURL.split(',');
        var mime = arr[0].match(/:(.*?);/)[1];
        var bstr = atob(arr[1]);
        var n = bstr.length;
        var u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    }

    // Load saved design on page load
    loadDesign();
});
