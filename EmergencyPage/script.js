$(document).ready(function() {
    // Form submission handler
    $('#emergencyAppointmentForm').submit(function(e) {
    e.preventDefault();
                
    // Get form data
    const formData = {
        name: $('#fullName').val(),
        phone: $('#phone').val(),
        age: $('#age').val(),
        urgency: $('#urgency option:selected').text(),
        symptoms: $('#symptoms').val(),
        medicalHistory: $('#medicalHistory').val(),
        location: $('#location').val(),
        transportation: $('#transportation option:selected').text()
    };
                
     // Show urgent confirmation
    alert('🚨 EMERGENCY REQUEST RECEIVED!\n\nOur emergency response team will contact you at ' + formData.phone + ' within the next few minutes.\n\nPlease keep your phone nearby.');
                
    // Reset form
        this.reset();
    });

    // Highlight critical urgency
    $('#urgency').change(function() {
        if ($(this).val() === 'critical') {
            $(this).css({
                'border-color': '#e74c3c',
                'background-color': '#fee'
            });
            alert('⚠️ For life-threatening emergencies, please call 911 immediately!');
        } else {
            $(this).css({
                'border-color': '#e0e0e0',
                'background-color': 'white'
            });
        }
    });
});