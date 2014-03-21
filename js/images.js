/** devis.js - pour school phenomenon
* @author: Ophir LOJKINE
**/

var TAILLE_IMAGE_MAX = 5e6; //En octets

if (!window.FileReader) {
	var msg = "Votre navigateur ne permet pas la lecture de fichiers locaux.\n";
	msg += "Vous ne pourrez pas placer dynamiquement les images de personnalisation de vos sweats.\n";
	msg += "Si vous souhaitez avoir cette possibilité, visitez cette page depuis une version récente de Firefox ou Chrome.";
	alert(msg);
}

$(".image-personnalisation").change(function(e){
	console.log(e.target.files[0]);
	var input = e.target, $input = $(input);
	var file = e.target.files[0];
	if (file.size > TAILLE_IMAGE_MAX) {
		alert("Fichier trop volumineux. Taille maximale autorisée : "+(TAILLE_IMAGE_MAX/1e6)+"Mo");
		input.value="";
	}
	var r = new FileReader();
	r.onload = function (res) {
		var $imagezone = $($input.data("zone-personnalisation"));
		console.log($imagezone[0]);
		var $img = $("<img>")
					.attr("alt", file.name)
					.attr("src", r.result)
					.attr("class", "image-perso-deplacable")
					.draggable().resizable()
					.width(80).height("auto")
					.appendTo($imagezone);
	}
	r.readAsDataURL(file);
});
