
var $ = jQuery.noConflict();
var positions = {}; // Definiere die 'positions' Variable außerhalb der Funktionen, um sie global zu machen


function loadGridPositions(positions) {
    console.log('loadGridPositions function called');
    console.log('positions:', positions); // Log the positions variable

    // Loop through the loaded positions
    for (var columnId in positions) {
        console.log('columnId:', columnId); // Log the current columnId

        // Find the image with the saved ID
        var imageElement = $("#T_image_list img").filter(function() {
            return $(this).attr('id') == positions[columnId];
        });
        console.log('imageElement:', imageElement); // Log the imageElement

        // If the image was found...
        if (imageElement.length > 0) {
            // ...set the background image of the corresponding column
            var imageSrc = $(imageElement).attr("src");
            console.log('imageSrc:', imageSrc); // Log the imageSrc

            $("#" + columnId).css("background-image", "url(" + imageSrc + ")");
            $("#" + columnId).css("background-size", "cover");
            $("#" + columnId).css("background-position", "center");
            $("#" + columnId).css("background-repeat", "no-repeat");
        }
    }
}


$.ajax({
    // ... (existing AJAX request code) ...

    success: function(response) {
        console.log('Server response:', response);
        // Load the positions into the 'positions' variable
        positions = response.positions || {};

        // Call the loadGridPositions function to position the images
        loadGridPositions(positions);
    }
});
    // ...existing code...







var loadedPositions = loadedPositions || {};
console.log('Value of loadedPositions:', loadedPositions); // Konsolenausgabe, um den Wert der `loadedPositions` Variable zu zeigen
(function(jQuery) {
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'load_image_positions', // The 'action' parameter determines which PHP function is called
            post_id: $('#post_ID').val() // Pass the post ID as data to the PHP function
        },
        success: function(response) {
            console.log('Server response:', response);
        
            try {
                var positions = JSON.parse(response) || {};
            } catch(e) {
                console.log('Error parsing response:', e);
                var positions = {};
            }
        
            $('#Grid_image_positions').val(JSON.stringify(positions));
            loadGridPositions(positions);
        }
        
        
    });
    

    $(document).ready(function() {
        // Load saved image positions from global variable
        if (typeof loadedPositions !== 'undefined') {
            for (var columnId in loadedPositions) {
                var imageId = loadedPositions[columnId];
                // Find the image with the saved ID
                var imageElement = $("#T_image_list img").filter(function() {
                    return $(this).attr('id') == imageId;
                });
    
                if (imageElement.length > 0) {
                    // Clone the image and remove it from the image list
                    var clonedImage = imageElement.clone().removeClass("T_image").addClass("tableau-grid-image");
                 
    
                    // Append the cloned image to the column
                    $("#" + columnId).html(clonedImage);
                }
            }
        }

    
       

        $('#T_image_list').on('click', '.T_remove_button', function() {
            var imageID = $(this).siblings('input[type="hidden"]').val();
            $(this).parent().remove();
            T_update_images();
        });

        $('#T_image_list').on('click', '.T_checkbox', function() {
            T_update_images();
        
            var data = {
                'action': 'T_update_portfolio_checked',
                'portfolio_id': $('#portfolio_ID').val(),
                'checked': $('#T_image_list input[type="checkbox"]:checked').map(function(){return $(this).data('id');}).get()
            };
        
            $.post(ajaxurl, data, function(response) {
                // The alert has been removed
            });
        });
        
        // Function to handle image updates
        function T_update_images() {
            var data = {
                'action': 'T_update_images',
                'post_id': $('#post_ID').val(),
                'images': $('#T_image_list input[type="hidden"]').map(function(){return $(this).val();}).get(),
                'checked': $('#T_image_list input[type="checkbox"]:checked').map(function(){return $(this).data('id');}).get()
            };
        
            $.post(ajaxurl, data, function(response) {
                // The alert has been removed
            });
        }
    });









































//
//
//drag and drop
//










    

    // Verschieben Sie diesen Code in einen $(window).load Handler
    $(window).load(function() {
        // Add the images to the #T_image_list
    // JavaScript-Funktion
function addGridIds() {
    const columns = document.querySelectorAll(".tableau-grid-column"); // Alle Elemente mit der Klasse "tableau-grid-column" auswählen
    
    columns.forEach((column, index) => {
      const gridId = `GridId${index + 1}`; // Eindeutige ID mit dem Prefix "GridId" erzeugen (index + 1, da Index bei 0 beginnt)
      column.setAttribute("id", gridId); // Die erzeugte ID dem Element als Attribut "id" zuweisen
      console.log('Added ID:', gridId);
    });
  }
  
  // Funktion aufrufen, um die IDs hinzuzufügen
   // Funktion aufrufen, um die IDs hinzuzufügen
   addGridIds();


  
        // Machen Sie die Bilder draggable
        $("#T_image_list img").draggable({
            revert: "invalid",
            helper: "clone",
            start: function(event, ui) {
                // Logge die ID des ursprünglichen Elements, wenn das Ziehen beginnt
                console.log('Original element ID:', $(this).attr('id'));
                // Übergebe die ID des ursprünglichen Elements an das helper-Element
                ui.helper.attr('id', $(this).attr('id'));
            },
            stop: function(event, ui) {
                // Logge die ID des helper-Elements, wenn das Ziehen stoppt
                console.log('Helper element ID:', ui.helper.attr('id'));
            }
        });

        loadGridPositions()    
    
        // Machen Sie die Spalten droppable
     


// Die Drop-Funktion für Grid-Spalten
$( ".tableau-grid-column" ).droppable({
    accept: "#T_image_list img",
    drop: function( event, ui ) {
        // Hole die ID des abgelegten Bildes mit prop statt attr
        var image_id = ui.helper.prop('id');
        
        // Logge die ID des helper Elements
        console.log('Helper element ID:', image_id);

        // Setze das Hintergrundbild der Spalte
        var imageSrc = $(ui.helper).attr("src");
        $(this).css("background-image", "url(" + imageSrc + ")");
        $(this).css("background-size", "cover");
        $(this).css("background-position", "center");
        $(this).css("background-repeat", "no-repeat");

        // Rest des Codes...

        // Hole die ID der Spalte
        var column_id = $(this).attr('id');

        // Rufe die Funktion auf, um die Positionen zu speichern
        Grid_save_positions(column_id, image_id); // Pass the column_id and image_id as parameters
    }
});

    });
    


 // Hole die Post-ID
 var post_id = $('#post_ID').val();

 // Erstelle eine AJAX-Anforderung
 $.ajax({
     url: ajaxurl, // In WordPress-Admin-Seiten ist die AJAX-URL in der globalen Variable 'ajaxurl' verfügbar
     type: 'POST',
     data: {
         action: 'save_image_positions', // Der 'action'-Parameter bestimmt, welche PHP-Funktion aufgerufen wird
         positions: positions, // Übergebe die Positionen als Daten an die PHP-Funktion
         post_id: post_id // Übergebe die Post-ID als Daten an die PHP-Funktion
     },
     // Rest of the code...
 });






 function Grid_save_positions(column_id, image_id) {
    // Hole die aktuellen Positionen aus dem versteckten Eingabefeld
    var positions = JSON.parse($('#Grid_image_positions').val() || "{}");

    // Aktualisiere die Position des Bildes in der spezifischen Spalte
    positions[column_id] = image_id;

    // Konvertiere das Positionsobjekt in eine JSON-Zeichenkette
    var positions_string = JSON.stringify(positions);

    // Schreibe die JSON-Zeichenkette in das versteckte Eingabefeld
    $('#Grid_image_positions').val(positions_string);

    console.log('Grid_save_positions called');
    console.log('Positions:', positions);
    console.log('JSON String:', JSON.stringify(positions));
    console.log('Hidden input value:', $('#Grid_image_positions').val());

    // Erstelle eine AJAX-Anforderung
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'save_image_positions', // Ändere 'load_image_positions' zu 'save_image_positions'
            positions: positions, // Übergebe die Positionen als Daten an die PHP-Funktion
            post_id: $('#post_ID').val() // Übergebe die Post-ID als Daten an die PHP-Funktion
        },
        success: function(response) {
            console.log('Image positions saved successfully:', response);
        },
        error: function(response) {
            console.error('Failed to save image positions:', response);
        }
    });


        
    }
    


       // Lade die Positionen
   

// Warte, bis das Dokument vollständig geladen ist


})(jQuery);














document.addEventListener('DOMContentLoaded', function() {
    // Der Button, der ausgelöst wird, um den Screenshot zu erstellen
    document.getElementById('TBI_screenshot_button').addEventListener('click', function(event) {
        event.preventDefault(); // Verhindern Sie das Standardverhalten des Buttons

        // Die Post-ID aus der URL extrahieren
        var postId = new URLSearchParams(window.location.search).get('post');

        html2canvas(document.getElementById('TBI')).then(function(canvas) {
            // Konvertieren Sie das Canvas in ein Base64-Bild
            var dataURL = canvas.toDataURL();

            // Senden Sie das Bild an WordPress zum Speichern
            fetch(wpVars.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=TBI_save_screenshot&image=' + encodeURIComponent(dataURL) + '&post_id=' + postId
                
            }).then(response => response.json()).then(data => {
                console.log(data); // Hier können Sie das Feedback vom Server verarbeiten, z.B. eine Benachrichtigung anzeigen
            });
        });
    });
});
