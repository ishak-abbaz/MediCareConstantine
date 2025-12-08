$(document).ready(function() {
    
    // ... your existing code ...
    
    // Accordion functionality
    $(document).on('click', '.accordion-header', function(e) {
        e.preventDefault();
        
        const $item = $(this).closest('.accordion-item');
        const $content = $item.find('.accordion-content');
        const isActive = $item.hasClass('active');
        
        // Close all accordion items
        $('.accordion-item').removeClass('active');
        $('.accordion-content').slideUp(300);
        
        // If clicked item wasn't active, open it
        if (!isActive) {
            $item.addClass('active');
            $content.slideDown(400);
            
            // Smooth scroll to accordion item
            $('html, body').animate({
                scrollTop: $item.offset().top - 100
            }, 500);
        }
    });
    
    // ... rest of your code ...
});