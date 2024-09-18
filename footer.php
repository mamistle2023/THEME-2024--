<?php wp_footer(); ?>



</body>
</html>




<!-- FullPage.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.9/fullpage.min.css">

<!-- FullPage.js JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.9/fullpage.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fullpage !== 'undefined') {
        new fullpage('#fullpage', {
            autoScrolling: true,
            navigation: true,
            slidesNavigation: true,
            slidesNavPosition: 'bottom',
        });
    }
});
</script>