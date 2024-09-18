document.addEventListener('DOMContentLoaded', function() {
    let checkboxes = document.querySelectorAll('#TG-category-filter input[type="checkbox"]');
    let tableauImages = document.querySelectorAll('.TG-images-list .TG-image');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            filterImages();
            saveState();
        });
    });

    function filterImages() {
        let checkedCategories = [];
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                checkedCategories.push('cat-' + checkbox.value);
            }
        });

        tableauImages.forEach(image => {
            image.style.display = 'none';
            checkedCategories.forEach(cat => {
                if (image.classList.contains(cat)) {
                    image.style.display = 'block';
                }
            });
        });
    }

    function collectState() {
        let checkedCategories = [];
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                checkedCategories.push(checkbox.value);
            }
        });

        let imageOrder = [];
        tableauImages.forEach(image => {
            imageOrder.push(image.dataset.id);
        });

        return {
            categories: checkedCategories,
            order: imageOrder
        };
    }

    function applyState(state) {
        checkboxes.forEach(checkbox => {
            checkbox.checked = state.categories.includes(checkbox.value);
        });

        let imageOrder = state.order;
        let imageList = document.querySelector('.TG-images-list');
        imageOrder.forEach(id => {
            let image = document.querySelector('.TG-image[data-id="' + id + '"]');
            if (image) {
                imageList.appendChild(image);
            }
        });

        filterImages();
    }

    function saveState() {
        let state = collectState();
        jQuery.post(tgVars.ajax_url, {
            action: 'TG_save_state',
            post_id: tgVars.post_id,
            state: JSON.stringify(state)
        }, function(response) {
            console.log(response);
        });
    }

    function loadState() {
        jQuery.post(tgVars.ajax_url, {
            action: 'TG_load_state',
            post_id: tgVars.post_id
        }, function(response) {
            let state = JSON.parse(response.data.state);
            if (state) {
                applyState(state);
            }
        });
    }

    loadState();

    jQuery(".TG-images-list").sortable({
        update: function(event, ui) {
            saveState();
        }
    });
});
