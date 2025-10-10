<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"
    integrity="sha512-7bS2beHr26eBtIOb/82sgllyFc1qMsDcOOkGK3NLrZ34yTbZX8uJi5sE0NNDYFNflwx1TtnDKkEq+k2DCGfb5w=="
    crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

<!-- Load the fixed JavaScript file -->
<script src="{{ asset('js/main-fixed.js') }}"></script>

<!-- Fallback in case the main-fixed.js fails to load -->
<script>
    // Safe version of openNav that won't throw errors
    if (typeof openNav === 'undefined') {
        window.openNav = function() {
            console.warn('Navigation function not loaded');
            return false;
        };
    }
    
    // Safe version of uploadFile
    if (typeof uploadFile === 'undefined') {
        window.uploadFile = function(input) {
            console.warn('File upload function not loaded');
            return false;
        };
    }
</script>
