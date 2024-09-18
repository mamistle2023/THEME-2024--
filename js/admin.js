jQuery(function() {


    jQuery('#portfolio-images-list, #tableau-images-list').sortable();


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


    addTableauImagesFromPortfolio();

    // Funktion zum Hinzufügen eines Tableau-Bildes



    // Event-Handler für das Klicken auf "Tableau-Beiträge generieren" Button
    jQuery('#generate-tableau-posts').on('click', function() {
        generateTableauPosts();
    });
});

// Funktion zum Anzeigen oder Ausblenden von Bildern basierend auf den ausgewählten Checkbox-Werten
function toggleTableauImages() {
    var checkedCheckboxes = jQuery('input[name="portfolio_image_checkbox[]"]:checked');
    var tableauImages = jQuery('#tableau-images-list li');

    tableauImages.each(function() {
        var imageId = jQuery(this).find('input[name="tableau_images[]"]').val();

        if (checkedCheckboxes.filter('[value="' + imageId + '"]').length > 0) {
            jQuery(this).show();
        } else {
            jQuery(this).hide();
        }
    });
}

jQuery(document).on('change', 'input[name="portfolio_image_checkbox[]"]', function() {
    toggleTableauImages();
});





// Initialisierung der Bilder als draggable Elemente
jQuery('#tableau-images-list li').draggable({
    revert: 'invalid',
    helper: 'clone'
});

jQuery(function() {
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



    // Event-Handler für das Klicken auf "Bilder auswählen" Button für Tableau-Bilder
    window.addTableauImages = function() {
      var frame = wp.media({
        title: 'Bilder auswählen',
        multiple: true,
        library: {
          type: 'image'
        }
      });
  
      frame.on('select', function() {
        var attachments = frame.state().get('selection').toJSON();
        var imageList = jQuery('#tableau-images-list');
  
        for (var i = 0; i < attachments.length; i++) {
          var listItem = jQuery('<li></li>');
          var hiddenInput = jQuery('<input type="hidden" name="tableau_images[]" />').val(attachments[i].id);
          var image = jQuery('<img />').attr('src', attachments[i].url).attr('width', 100).attr('height', 'auto');
          var removeButton = jQuery('<button type="button" class="button button-secondary remove-tableau-image">Entfernen</button>');
  
          removeButton.on('click', function() {
            jQuery(this).parent().remove();
          });
  
          listItem.append(hiddenInput);
          listItem.append(image);
          listItem.append(removeButton);
  
          imageList.append(listItem);
  
          // Hinzugefügtes Bild als Draggable-Element initialisieren
          listItem.addClass('draggable-image');
          listItem.data('image-id', attachments[i].id);
          listItem.draggable({ revert: 'invalid' });
        }
      });
  
      frame.open();
    }

  
    // Event-Handler für das Klicken auf "Tableau-Beiträge generieren" Button
    jQuery('#generate-tableau-posts').on('click', function() {
      generateTableauPosts();
    });
  });
  
  jQuery(function() {
    // Funktion zum Anzeigen oder Ausblenden von Bildern basierend auf den ausgewählten Checkbox-Werten
    function toggleTableauImages() {
      var checkedCheckboxes = jQuery('input[name="portfolio_image_checkbox[]"]:checked');
      var tableauImages = jQuery('#tableau-images-list li');
  
      tableauImages.each(function() {
        var imageId = jQuery(this).find('input[name="tableau_images[]"]').val();
  
        if (checkedCheckboxes.filter('[value="' + imageId + '"]').length > 0) {
          jQuery(this).show();
        } else {
          jQuery(this).hide();
        }
      });
    }
  
    jQuery(document).on('change', 'input[name="portfolio_image_checkbox[]"]', function() {
      toggleTableauImages();
    });
  

    // Initialisierung der Drop-Zonen mit Bootstrap Grid
    jQuery(function() {
        console.log('admin.js wurde geladen!');
    
        // ...
    
        // Initialisierung der Drop-Zonen mit Bootstrap Grid
        jQuery('.image-column').sortable({
            connectWith: '.image-column',
            revert: true,
            placeholder: 'sortable-placeholder',
            start: function(event, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(event, ui) {
                var droppedImage = ui.item;
    
                // Überprüfen, ob das Feld bereits ein Bild enthält
                if (droppedImage.siblings().length > 0) {
                    var columnId = droppedImage.closest('.image-column').attr('id');
    
                    // Zurücksetzen des vorhandenen Bildes in die Liste
                    var originalImage = droppedImage.siblings().first();
                    var originalImageId = originalImage.find('input[name="tableau_images[]"]').val();
                    var originalImageClone = originalImage.clone();
                    originalImage.remove();
                    jQuery('#tableau-images-list').append(originalImageClone);
    
                    // Hinzufügen des neuen Bildes in das Feld
                    droppedImage.appendTo('#' + columnId);
    
                    // Entfernen des abgelegten Bildes aus der Liste
                    droppedImage.addClass('dropped-image');
    
                    // Senden der Bild-ID und der Spalten-ID an den Server
                    sendImageIdToServer(originalImageId, columnId);
                } else {
                    var imageId = droppedImage.find('input[name="tableau_images[]"]').val();
                    var columnId = droppedImage.closest('.image-column').attr('id');
    
                    // Entfernen des abgelegten Bildes aus der Liste
                    droppedImage.addClass('dropped-image');
    
                    // Senden der Bild-ID und der Spalten-ID an den Server
                    sendImageIdToServer(imageId, columnId);
                }
            }
        }).disableSelection();
    
        // Initialisierung der Original-Elemente als draggable
        jQuery('#tableau-images-list li').addClass('draggable-image').draggable({
            revert: 'invalid',
            connectToSortable: '.image-column',
            helper: 'clone'
        });
    
        // ...
    });
    
      
    // ...
});


jQuery(function() {
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





  // Event-Handler für das Klicken auf "Tableau-Beiträge generieren" Button
  jQuery('#generate-tableau-posts').on('click', function() {
    generateTableauPosts();
  });
});

jQuery(function() {
  // Funktion zum Anzeigen oder Ausblenden von Bildern basierend auf den ausgewählten Checkbox-Werten
  function toggleTableauImages() {
    var checkedCheckboxes = jQuery('input[name="portfolio_image_checkbox[]"]:checked');
    var tableauImages = jQuery('#tableau-images-list li');

    tableauImages.each(function() {
      var imageId = jQuery(this).find('input[name="tableau_images[]"]').val();

      if (checkedCheckboxes.filter('[value="' + imageId + '"]').length > 0) {
        jQuery(this).show();
      } else {
        jQuery(this).hide();
      }
    });
  }

  jQuery(document).on('change', 'input[name="portfolio_image_checkbox[]"]', function() {
    toggleTableauImages();
  });

// Funktion, um die Spalten-ID eines Bildes zu ermitteln
function getColumnIdForImage(imageId) {
  // Bild-Element mit der angegebenen ID suchen
  const imageElement = document.querySelector(`input[name="tableau_images[]"][value="${imageId}"]`);

  // Übergeordnetes Spalten-Element finden (mit ID im Format "image-column-*")
  const columnElement = imageElement.closest('[id^="image-column-"]');
  if (columnElement) {
    // ID der Spalte extrahieren (die Nummer am Ende der ID)
    const columnId = columnElement.id.replace('image-column-', '');
    return columnId;
  }

  // Wenn kein passendes Spalten-Element gefunden wurde, null zurückgeben
  return null;
}



  // Initialisierung der Drop-Zonen mit Bootstrap Grid
  jQuery(function() {
      console.log('admin.js wurde geladen!');
  
      // ...
  
      // Initialisierung der Drop-Zonen mit Bootstrap Grid
      jQuery('.image-column').sortable({
          connectWith: '.image-column',
          revert: true,
          placeholder: 'sortable-placeholder',
          start: function(event, ui) {
              ui.placeholder.height(ui.item.height());
          },
          stop: function(event, ui) {
              var droppedImage = ui.item;
  
              // Überprüfen, ob das Feld bereits ein Bild enthält
              if (droppedImage.siblings().length > 0) {
                  var columnId = droppedImage.closest('.image-column').attr('id');
  
                  // Zurücksetzen des vorhandenen Bildes in die Liste
                  var originalImage = droppedImage.siblings().first();
                  var originalImageId = originalImage.find('input[name="tableau_images[]"]').val();
                  var originalImageClone = originalImage.clone();
                  originalImage.remove();
                  jQuery('#tableau-images-list').append(originalImageClone);
  
                  // Hinzufügen des neuen Bildes in das Feld
                  droppedImage.appendTo('#' + columnId);
  
                  // Entfernen des abgelegten Bildes aus der Liste
                  droppedImage.addClass('dropped-image');
  
                  // Senden der Bild-ID und der Spalten-ID an den Server
                  sendImageIdToServer(originalImageId, columnId);
              } else {
                  var imageId = droppedImage.find('input[name="tableau_images[]"]').val();
                  var columnId = droppedImage.closest('.image-column').attr('id');
  
                  // Entfernen des abgelegten Bildes aus der Liste
                  droppedImage.addClass('dropped-image');
  
                  // Senden der Bild-ID und der Spalten-ID an den Server
                  sendImageIdToServer(imageId, columnId);
              }
          }
      }).disableSelection();
  
      // Initialisierung der Original-Elemente als draggable
      jQuery('#tableau-images-list li').addClass('draggable-image').draggable({
          revert: 'invalid',
          connectToSortable: '.image-column',
          helper: 'clone'
      });
  
      // ...
  });
  
    
  // ...
});

jQuery(document).ready(function($) {
// Event-Handler für das Klicken auf den Update-Button
$(document).on('click', '#post .save-post', function(e) {
  e.preventDefault();

  // Erfasse den aktuellen Seitenzustand
  var pageState = {
    // Hier kannst du den Seitenzustand erfassen, der gespeichert werden soll
    // Zum Beispiel: Tableau-Bilder, Sortierreihenfolge, etc.
  };

  // Sende den Seitenzustand an den Server, um ihn zu speichern
  // Hier kannst du AJAX oder eine andere Methode verwenden, um die Daten an den Server zu senden

  console.log('Seitenzustand aktualisiert:', pageState);

  // Führe den ursprünglichen Klick auf den Update-Button aus
  $(this).trigger('click');
});
});
