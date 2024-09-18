jQuery(document).ready(function() {
    console.log('admin.js wurde geladen!');
  
    // Definieren der portfolioImageCheckboxes-Variable
    window.portfolioImageCheckboxes = [];
  
    // Funktion zum Hinzufügen der Portfolio-Bilder
    window.addPortfolioImages = function() {
      var frame = wp.media({
        title: 'Bilder auswählen',
        multiple: true,
        library: {
          type: 'image'
        }
      });
  
      frame.on('select', function() {
        var attachments = frame.state().get('selection').toJSON();
        var imageList = jQuery('#portfolio-images-list');
  
        for (var i = 0; i < attachments.length; i++) {
          var listItem = jQuery('<li></li>');
          var hiddenInput = jQuery('<input type="hidden" name="portfolio_images[]" />').val(attachments[i].id);
          var image = jQuery('<img />').attr('src', attachments[i].url).attr('width', 100).attr('height', 'auto');
          var removeButton = jQuery('<button type="button" class="button button-secondary remove-portfolio-image">Entfernen</button>');
          var checkBox = jQuery('<input type="checkbox" name="portfolio_image_checkbox[]" value="'+ attachments[i].id +'" />');
          checkBox.prop('checked', window.portfolioImageCheckboxes.includes(attachments[i].id));
  
          removeButton.on('click', function() {
            jQuery(this).parent().remove();
          });
  
          listItem.append(hiddenInput);
          listItem.append(image);
          listItem.append(removeButton);
          listItem.append(checkBox);
  
          imageList.append(listItem);
        }
      });
  
      frame.open();
    };
  
    // Funktion zum Entfernen eines Portfolio-Bildes
    function removePortfolioImage() {
      jQuery(this).parent().remove();
    }
  
    // Funktion zum Speichern der Reihenfolge der Portfolio-Bilder
    function savePortfolioImageOrder() {
      var imageList = jQuery('#portfolio-images-list');
      var imageOrder = [];
  
      imageList.find('li').each(function() {
        var imageId = jQuery(this).find('input[name="portfolio_images[]"]').val();
        imageOrder.push(imageId);
      });
  
      var data = {
        action: 'portfolio_image_order',
        post_id: jQuery('#post_ID').val(),
        image_order: imageOrder
      };
  
      jQuery.post(ajaxurl, data, function(response) {
        console.log(response.data);
      });
    }
  
    // Event-Handler für das Klicken auf "Bilder auswählen" Button
    jQuery('#add-portfolio-images').on('click', function(e) {
      e.preventDefault();
      addPortfolioImages();
    });
  
    // Event-Handler für das Klicken auf "Entfernen" Button eines Portfolio-Bildes
    jQuery(document).on('click', '.remove-portfolio-image', removePortfolioImage);
  
    // AJAX-Anfrage zum Abrufen von Portfolio-Bildern und Hinzufügen von Tableau-Bildern beim Laden der Seite
    function addTableauImagesFromPortfolio() {
      var portfolioId = jQuery('#portfolio_id').val();
  
      // Überprüfen, ob die Portfolio ID vorhanden ist
      if (portfolioId !== '') {
        jQuery.ajax({
          url: ajaxurl,
          method: 'POST',
          data: {
            action: 'get_portfolio_images',
            portfolio_id: portfolioId
          },
          success: function(response) {
            if (response.success) {
              for (var i = 0; i < response.data.images.length; i++) {
                var image = response.data.images[i];
                addTableauImage(image.id, image.url);
              }
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown);
          }
        });
      } else {
        console.error('Portfolio ID fehlt');
      }
    }
  
    addTableauImagesFromPortfolio();
    // Funktion zum Hinzufügen eines Tableau-Bildes
    function addTableauImage(imageId, imageUrl) {
        var imageList = jQuery('#tableau-images-list');

        var listItem = jQuery('<li></li>');
        var hiddenInput = jQuery('<input type="hidden" name="tableau_images[]" />').val(imageId);
        var image = jQuery('<img />').attr('src', imageUrl).attr('width', 100).attr('height', 'auto');
        var removeButton = jQuery('<button type="button" class="button button-secondary remove-tableau-image">Entfernen</button>');

        removeButton.on('click', function() {
            jQuery(this).parent().remove();
        });

        listItem.append(hiddenInput);
        listItem.append(image);
        listItem.append(removeButton);
        // Hinzugefügtes Bild als Draggable-Element initialisieren
      // Hinzugefügtes Bild als Draggable-Element initialisieren
  listItem.addClass('draggable-image');
  listItem.data('image-id', imageId);
  listItem.draggable({ revert: 'invalid' });

  imageList.append(listItem);

  // Bild als responsive (img-fluid) markieren
  image.addClass('img-fluid');
    }
// Initialisierung der Original-Elemente als draggable
jQuery('#tableau-images-list li').addClass('draggable-image').draggable({ revert: 'invalid' });




   
    // Funktion zum Generieren der Tableau-Beiträge
    function generateTableauPosts() {
      jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
          action: 'generate_tableau_posts'
        },
        success: function(response) {
          if (response.success) {
            alert('Tableau-Beiträge wurden erfolgreich generiert.');
          } else {
            alert('Fehler beim Generieren der Tableau-Beiträge.');
          }
        }
      });
    }
  
    // Event-Handler für das Klicken auf "Tableau-Beiträge generieren" Button
    jQuery('#generate-tableau-posts').on('click', function() {
      generateTableauPosts();
    });



      
    // ...
});


// Function to update the portfolio post when a tableau post is changed
function updatePortfolioPost() {
  // Get the image ids and checked values
  var imageIds = jQuery('#tableau-images-list li input[name="tableau_images[]"]').map(function() {
      return jQuery(this).val();
  }).get();
  var checkedValues = jQuery('#tableau-images-list li input[type="checkbox"]:checked').map(function() {
      return jQuery(this).siblings('input[name="tableau_images[]"]').val();
  }).get();

  // Send an AJAX request to update the portfolio post
  jQuery.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
          action: 'update_portfolio_images',
          post_id: jQuery('#post_ID').val(),
          images: imageIds,
          checked: checkedValues
      },
      success: function(response) {
          if (response.success) {
              console.log('Portfolio post updated');
          } else {
              console.error('Error updating portfolio post');
          }
      },
      error: function(jqXHR, textStatus, errorThrown) {
          console.error('AJAX error:', textStatus, errorThrown);
      }
  });
}

// Add event handlers to update the portfolio post when an image is added, removed, or a checkbox is changed
jQuery(document).on('click', '#add-portfolio-images', updatePortfolioPost);
jQuery(document).on('click', '.remove-tableau-image', updatePortfolioPost);
jQuery(document).on('change', '#tableau-images-list li input[type="checkbox"]', updatePortfolioPost);


































