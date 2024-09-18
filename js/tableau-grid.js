document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('#tableau-group-category-checkboxes input[type="checkbox"]');
    const images = document.querySelectorAll('.tableau-group-images-list img');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            let selectedCats = [];

            checkboxes.forEach(cbox => {
                if (cbox.checked) {
                    selectedCats.push(cbox.value);
                }
            });

            images.forEach(img => {
                const catIDs = img.getAttribute('data-cats').split(',');
                if (selectedCats.some(cat => catIDs.includes(cat))) {
                    img.style.display = 'block';
                } else {
                    img.style.display = 'none';
                }
            });
        });
    });

    $('#grid_layout_selection').change(function() {
        let selectedLayout = $(this).val();

        switch(selectedLayout) {
            case 'layout_1':
                // Hier können Sie den Inhalt des Containers mit dem Grid dynamisch ändern
                // z.B. $('#containerId').html(gridLayout1Html);
                break;
            case 'layout_2':
                // Ändern Sie den Inhalt entsprechend
                break;
            case 'layout_3':
                // Ändern Sie den Inhalt entsprechend
                break;
            default:
                break;
        }
    });
});














