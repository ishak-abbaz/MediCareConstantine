let currentIndex = 0;
let autoSlideInterval;

function bookAppointment(){
    $(".appointment-button").on('click', function(){
        window.location.href= "../AppointmentPage/appointment.html";
    });
}

// Show specific slide
function showSlide(index) {
    const slides = $('.slide');
    const dots = $('.dot');
    
    // Wrap around if index goes out of bounds
    if (index >= slides.length) {
        currentIndex = 0;
    } else if (index < 0) {
        currentIndex = slides.length - 1;
    } else {
        currentIndex = index;
    }

    // Remove active class from all slides and dots
    slides.removeClass('active');
    dots.removeClass('active');

    // Add active class to current slide and dot
    slides.eq(currentIndex).addClass('active');
    dots.eq(currentIndex).addClass('active');
}

// Change slide (next or previous)
function changeSlide(direction) {
    showSlide(currentIndex + direction);
    resetAutoSlide();
}

// Go to specific slide
function currentSlide(index) {
    showSlide(index);
    resetAutoSlide();
}

// Auto slide functionality
function autoSlide() {
    autoSlideInterval = setInterval(() => {
        changeSlide(1);
    }, 3000); // Change slide every 3 seconds
}

// Reset auto slide timer
function resetAutoSlide() {
    clearInterval(autoSlideInterval);
    autoSlide();
}

// jQuery document ready
$(document).ready(function() {
    // Start auto slide when page loads
    autoSlide();

    // Pause auto slide on hover
    $('.slider-container').hover(
        function() {
            // Mouse enter
            clearInterval(autoSlideInterval);
        },
        function() {
            // Mouse leave
            autoSlide();
        }
    );

    // Keyboard navigation
    $(document).keydown(function(e) {
        if (e.key === 'ArrowLeft') {
            changeSlide(-1);
        } else if (e.key === 'ArrowRight') {
            changeSlide(1);
        }
    });
});