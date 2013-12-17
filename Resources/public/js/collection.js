/**
* @name collection
* @version 1.0 [1 juillet 2013]
* @author akambi <contact@akambi-fagbohoun.com>
*/

$(document).ready(function() {

    (function() {
        
        $.fn.exists = function () {
            return this.length !== 0;
        }
            
        Sf2CollectionView =  function() {
            var self = this;
            // Initialise les collections présent dans le dom
            $('.sf2collection').each(function() {
                self.stash = $(this);
                self.collection = self.stash.find('.sf2collectionlist');
                self.length = self.collection.attr('data-length');

                self.stash.find('.add-another-item').click(function(e) {
                    e.preventDefault();
                    // parcourt le template prototype
                    var newWidget = self.collection.attr('data-prototype');
                    // remplace les "__name__" utilisés dans l'id et le nom du prototype
                    // par un nombre unique pour chaque item
                    newWidget = newWidget.replace(/__name__/g, self.length);
                    self.length++;

                    // créer une nouvelle liste d'éléments et l'ajoute à notre liste
                    var newItem = $('<li></li>').html(newWidget).children('tr');

                    // On ajoute le bouton delete
                    self.del(newItem);
                    newItem.appendTo(self.collection);
                    return false;
                });

                self.del = function($prototype) {
                    // Création du lien
                    $lienSuppression = $('<td><a class="btn btn-danger" href="#" class="btn btndanger">Delete</a></td>');
                    // Ajout du lien
                    $prototype.append($lienSuppression);
                    // Ajout du listener sur le clic du lien
                    $lienSuppression.click(function(e) {
                        $prototype.remove();
                        e.preventDefault(); // évite qu'un # apparaisse dans l'URL
                        return false;
                    });
                };

                if (self.collection.exists()) {
                    self.stash.find('#sticker-fields-list li').each(function(){
                        self.del($(this));
                    });
                }
            });

        }

        new Sf2CollectionView();
    }());

});