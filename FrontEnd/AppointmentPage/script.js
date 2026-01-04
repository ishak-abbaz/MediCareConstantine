// script.js - Enhanced for Appointment Page + Auto Active Nav
$(document).ready(function () {

    // ============================
    // 1. AUTO HIGHLIGHT CURRENT PAGE IN NAV (Works on ALL pages!)
    // ============================
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    
    // Get the service parameter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const serviceParam = urlParams.get('service');
    
    $('.navigation_links a').removeClass('active');
    $('.navigation_links a').each(function () {
        const href = $(this).attr('href').split('/').pop();
        if (href === currentPath || (currentPath === 'index.html' && href === 'index.html')) {
            $(this).addClass('active');
        }
    });

    // Special case for Home page if it's in root or subfolder
    if (currentPath === '' || currentPath === 'index.html') {
        $('.navigation_links a[href$="index.html"]').addClass('active');
    }

    // ============================
    // 2. APPOINTMENT FORM HANDLING
    // ============================
    const $form = $('form');
    const $submitBtn = $('.submit-btn');

    // Prevent selecting past dates
    const today = new Date().toISOString().split('T')[0];
    $('input[name="preferred_date"]').attr('min', today);

    // Real-time validation on input
    $('input, select, textarea').on('blur change', function () {
        validateField($(this));
    });

    function validateField($field) {
        const value = $field.val().trim();
        const type = $field.attr('type');
        let valid = true;
        let errorMsg = '';

        // Remove previous error state
        $field.removeClass('error success');

        if ($field.prop('required') && !value) {
            valid = false;
            errorMsg = 'This field is required';
        } 
        else if (type === 'email' && value && !isValidEmail(value)) {
            valid = false;
            errorMsg = 'Please enter a valid email';
        } 
        else if ($field.attr('name') === 'phone' && value && !isValidPhone(value)) {
            valid = false;
            errorMsg = 'Please enter a valid phone number';
        }
        else if ($field.attr('name') === 'preferred_date') {
            // Strict YYYY-MM-DD check
            const dateRegex = /^(202[4-7])-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/;

            if (!dateRegex.test(value)) {
                valid = false;
                errorMsg = 'Please use a valid date';
            } else {
                const selectedDate = new Date(value);
                const todayDate = new Date(today);

                // Invalid date (e.g., 2024-13-40)
                if (isNaN(selectedDate.getTime())) {
                    valid = false;
                    errorMsg = 'Invalid date selected';
                }
                // Past dates not allowed
                else if (selectedDate < todayDate) {
                    valid = false;
                    errorMsg = 'Date cannot be in the past';
                }
            }
        }

        if (!valid) {
            $field.addClass('error').removeClass('success');
            showFieldError($field, errorMsg);
        } else if (value) {
            $field.addClass('success').removeClass('error');
            hideFieldError($field);
        }

        return valid;
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPhone(phone) {
        return /^[\+]?[0-9]{10,15}$/.test(phone.replace(/\s/g, ''));
    }

    // Doctor list for each service
    const doctorsByService = {
        'general-medicine': [
            { value: 'dr-benali', name: 'Dr. Amina Benali - General Practitioner' },
            { value: 'dr-meziani', name: 'Dr. Karim Meziani - General Practitioner' },
            { value: 'dr-hadj', name: 'Dr. Sarah Hadj - General Practitioner' }
        ],
        'cardiology': [
            { value: 'dr-boudiaf', name: 'Dr. Youcef Boudiaf - Cardiologist' },
            { value: 'dr-salem', name: 'Dr. Fatima Salem - Cardiologist' }
        ],
        'pediatrics': [
            { value: 'dr-khelifi', name: 'Dr. Leila Khelifi - Pediatrician' },
            { value: 'dr-brahimi', name: 'Dr. Ahmed Brahimi - Pediatrician' }
        ],
        'dermatology': [
            { value: 'dr-mansouri', name: 'Dr. Nadia Mansouri - Dermatologist' },
            { value: 'dr-ziani', name: 'Dr. Mohamed Ziani - Dermatologist' }
        ],
        'gynecology-obstetrics': [
            { value: 'dr-benamara', name: 'Dr. Samia Benamara - Gynecologist' },
            { value: 'dr-aissa', name: 'Dr. Aicha Aissa - Obstetrician' }
        ],
        'special-consultation': [
            { value: 'dr-bouazza', name: 'Dr. Rachid Bouazza - Senior Consultant' },
            { value: 'dr-djilali', name: 'Dr. Hanane Djilali - Specialist' }
        ],
        'emergency-care': [
            { value: 'dr-emergency', name: 'Emergency Duty Doctor' }
        ],
        'medical-imaging': [
            { value: 'dr-radiologist', name: 'Dr. Omar Rahmani - Radiologist' },
            { value: 'tech-imaging', name: 'Imaging Technician on Duty' }
        ],
        'laboratory-tests': [
            { value: 'lab-tech', name: 'Laboratory Technician' }
        ]
    };

    // Function to update doctor list based on selected service
    function updateDoctorList(service) {
        const doctorSelect = $('#doctor');
        doctorSelect.empty(); // Clear existing options
        
        if (service && doctorsByService[service]) {
            doctorSelect.append('<option value="">-- Choose a Doctor --</option>');
            
            // Add doctors for the selected service
            doctorsByService[service].forEach(function(doctor) {
                doctorSelect.append(
                    `<option value="${doctor.value}">${doctor.name}</option>`
                );
            });
            
            doctorSelect.prop('disabled', false);
        } else {
            doctorSelect.append('<option value="">-- First Select a Service --</option>');
            doctorSelect.prop('disabled', true);
        }
    }

    // Handle service selection change
    $('#service').change(function() {
        const selectedService = $(this).val();
        
        // Update doctor list
        updateDoctorList(selectedService);
        
        // Show/hide time slot based on Special Consultation
        if (selectedService === 'special-consultation') {
            $('#timeSlotGroup').slideDown(300);
            $('#timeSlot').prop('required', true);
        } else {
            $('#timeSlotGroup').slideUp(300);
            $('#timeSlot').prop('required', false);
            $('#timeSlot').val(''); // Clear selection
        }
    });

    // If service is pre-selected from URL, update doctor list
    if (serviceParam) {
        $('#service').val(serviceParam);
        updateDoctorList(serviceParam);
        
        // Check if it's special consultation
        if (serviceParam === 'special-consultation') {
            $('#timeSlotGroup').show();
            $('#timeSlot').prop('required', true);
        }
        
        $('#service').css('border-color', '#4a90e2');
        
        $('html, body').animate({
            scrollTop: $('.appointment-form').offset().top - 100
        }, 500);
    }

    function showFieldError($field, msg) {
        let $error = $field.next('.error-msg');
        if ($error.length === 0) {
            $field.after(`<span class="error-msg">${msg}</span>`);
        } else {
            $error.text(msg);
        }
    }

    function hideFieldError($field) {
        $field.next('.error-msg').remove();
    }

    // Form Submission
    // $form.on('submit', function (e) {
    //     e.preventDefault(); // Remove this line later when connecting to real backend
    //
    //     let allValid = true;
    //     let firstErrorField = null;
    //
    //     $('input[required], select[required], textarea[required]').each(function () {
    //         if (!validateField($(this))) {
    //             allValid = false;
    //             if (!firstErrorField) firstErrorField = $(this);
    //         }
    //     });
    //
    //     if (!allValid) {
    //         alert('Please fix the errors in the form before submitting.');
    //         if (firstErrorField) {
    //             $('html, body').animate({
    //                 scrollTop: firstErrorField.offset().top - 100
    //             }, 500);
    //             firstErrorField.focus();
    //         }
    //         return;
    //     }
    //
    //     // Get form data for submission
    //     const formData = {
    //         name: $('#fullName').val(),
    //         email: $('#email').val(),
    //         phone: $('#phone').val(),
    //         date: $('#date').val(),
    //         service: $('#service option:selected').text(),
    //         doctor: $('#doctor option:selected').text(),
    //         time: $('#time').val(),
    //         timeSlot: $('#timeSlot').val() ? $('#timeSlot option:selected').text() : 'N/A',
    //         message: $('#message').val()
    //     };
    //
    //     // Simulate successful submission
    //     $submitBtn.text('Submitting...').prop('disabled', true);
    //
    //     setTimeout(function () {
    //         alert('Appointment booked successfully!\n\nService: ' + formData.service + '\nDoctor: ' + formData.doctor + '\n\nWe will contact you soon to confirm.');
    //         console.log('Appointment Data:', formData);
    //
    //         $form[0].reset();
    //         $('input, select, textarea').removeClass('success error');
    //         $('.error-msg').remove();
    //         $submitBtn.text('Submit Appointment').prop('disabled', false);
    //
    //         // Reset doctor dropdown to disabled state
    //         updateDoctorList('');
    //
    //         // Hide time slot if visible
    //         $('#timeSlotGroup').hide();
    //         $('#timeSlot').prop('required', false);
    //     }, 1500);
    // });

    // Optional: Add placeholder-like labels that move on focus
    $('.form-row label').each(function () {
        const $label = $(this);
        const $input = $label.next('input, select, textarea');
        if ($input.length) {
            $input.on('focus', function () {
                $label.addClass('focused');
            }).on('blur', function () {
                if (!$input.val()) {
                    $label.removeClass('focused');
                }
            });
            if ($input.val()) $label.addClass('focused');
        }
    });
});