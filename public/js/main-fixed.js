(function() {
    'use strict';

    // Safe element selector with optional parent
    function getElement(selector, parent = document) {
        try {
            return parent.querySelector(selector);
        } catch (e) {
            console.warn(`Element not found: ${selector}`, e);
            return null;
        }
    }

    // Safe classList operations
    function toggleClasses(element, add = [], remove = []) {
        if (!element) return false;
        
        try {
            remove.forEach(className => element.classList.remove(className));
            add.forEach(className => element.classList.add(className));
            return true;
        } catch (e) {
            console.warn('Error toggling classes:', e);
            return false;
        }
    }

    // Safe navigation toggle
    function openNav() {
        const elements = {
            sidebar: getElement('aside'),
            maxSidebar: getElement('.max-sidebar, [class*="max-sidebar"], [class^="max-sidebar"]'),
            miniSidebar: getElement('.mini-sidebar, [class*="mini-sidebar"], [class^="mini-sidebar"]'),
            maxToolbar: getElement('.max-toolbar, [class*="max-toolbar"], [class^="max-toolbar"]'),
            logo: getElement('.logo, [class*="logo"], [class^="logo"]'),
            content: getElement('main, .content, [class*="content"], [class^="content"]')
        };

        // Check if sidebar exists
        if (!elements.sidebar) {
            console.warn('Sidebar element not found');
            return false;
        }

        const isExpanded = elements.sidebar.classList.contains('-translate-x-48');

        if (isExpanded) {
            // Expand sidebar
            toggleClasses(elements.sidebar, ['translate-x-none'], ['-translate-x-48']);
            toggleClasses(elements.maxSidebar, ['flex'], ['hidden']);
            toggleClasses(elements.miniSidebar, ['hidden'], ['flex']);
            toggleClasses(elements.maxToolbar, ['translate-x-0'], ['translate-x-24', 'scale-x-0']);
            toggleClasses(elements.logo, [], ['ml-12']);
            if (elements.content) {
                elements.content.classList.remove('ml-12', 'md:ml-60');
                elements.content.classList.add('ml-12', 'md:ml-60');
            }
        } else {
            // Collapse sidebar
            toggleClasses(elements.sidebar, ['-translate-x-48'], ['translate-x-none']);
            toggleClasses(elements.maxSidebar, ['hidden'], ['flex']);
            toggleClasses(elements.miniSidebar, ['flex'], ['hidden']);
            toggleClasses(elements.maxToolbar, ['translate-x-24', 'scale-x-0'], ['translate-x-0']);
            toggleClasses(elements.logo, ['ml-12'], []);
            if (elements.content) {
                elements.content.classList.remove('ml-12', 'md:ml-60');
                elements.content.classList.add('ml-12');
            }
        }

        return true;
    }

    // File upload function with error handling
    function uploadFile(input) {
        if (!input || !input.files || !input.files[0]) {
            console.warn('No file selected or input invalid');
            return;
        }

        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
            try {
                const container = document.getElementById("image-container");
                if (!container) {
                    console.warn('Image container not found');
                    return;
                }

                // Clear previous content
                container.innerHTML = '';

                const uploadedImage = document.createElement('img');
                uploadedImage.src = e.target.result;
                uploadedImage.classList.add("w-32", "h-32", "object-cover", "rounded-lg");

                const deleteButton = document.createElement('button');
                deleteButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-red-700 w-4 h-4" viewBox="0 0 512 512">
                        <path d="M310.6 361.4c12.5 12.5 12.5 32.75 0 45.25C304.4 412.9 296.2 416 288 416s-16.38-3.125-22.62-9.375L160 301.3 54.63 406.6C48.38 412.9 40.19 416 32 416S15.63 412.9 9.375 406.6c-12.5-12.5-12.5-32.75 0-45.25l105.4-105.4L9.375 150.6c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L160 210.8l105.4-105.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-105.4 105.4L310.6 361.4z" />
                    </svg>
                `;
                
                deleteButton.classList.add("absolute", "top-2", "right-2", "p-1", "bg-white", "rounded-full", "shadow-md");
                deleteButton.onclick = function () {
                    container.innerHTML = '';
                    input.value = '';
                    const uploadText = document.getElementById("upload-text");
                    if (uploadText) uploadText.innerText = "Select a file";
                    const iconInput = document.getElementById("icon-input");
                    if (iconInput) iconInput.value = '';
                };

                const div = document.createElement('div');
                div.classList.add("relative", "mb-2");
                div.appendChild(uploadedImage);
                div.appendChild(deleteButton);
                container.appendChild(div);

                const uploadText = document.getElementById("upload-text");
                if (uploadText) uploadText.innerText = file.name;

                const iconInput = document.getElementById("icon-input");
                if (iconInput) iconInput.value = file.name;
            } catch (error) {
                console.error('Error processing file upload:', error);
            }
        };

        reader.onerror = function() {
            console.error('Error reading file');
        };

        reader.readAsDataURL(file);
    }

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Safe initialization of other components
        try {
            // Initialize Nestable if available
            if (typeof $ !== 'undefined' && $.fn.nestable) {
                $('.dd').nestable({
                    maxDepth: 10,
                    error: function(error) {
                        console.warn('Nestable error:', error);
                    }
                }).on('change', function() {
                    // Handle nestable changes
                });
            }

            // Initialize Select2 if available
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').select2({
                    theme: 'classic'
                });
            }
        } catch (e) {
            console.warn('Initialization error:', e);
        }
    });

    // Make functions available globally
    window.openNav = openNav;
    window.uploadFile = uploadFile;

    // Add error handling for uncaught errors
    window.addEventListener('error', function(event) {
        console.error('Uncaught error:', event.error);
        return false;
    });
})();
