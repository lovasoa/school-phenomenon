/** devis.js - pour school phenomenon
* auteur: Ophir LOJKINE
**/
$(function(){
	//Ajout des "tooltips" (informations supplémentaires sur les champs à remplir)
	$("#association").tooltip();

	// Validation de l'année de naissance
	$("#date-naissance input").on("blur change keyup", function(){
		var j = parseInt($("#date-naissance-jour").val()),
			m = parseInt($("#date-naissance-mois").val()),
			a = parseInt($("#date-naissance-annee").val());
		if (j && m && a) { //Si tout a été rempli
			// Date() prend un mois entre 0 et 11
			var date_dixhuitans = new Date(a+18, m-1, j);
			var action = ( date_dixhuitans > (new Date()) ) ? "show" : "hide";
			console.log(date_dixhuitans.toString(), action);
			$("#alerte-age")[action](500);
		}
	});

	// Date de livraison
	var date_livraison_min = new Date();
	$("#date-livraison").datepicker({
		format : "dd/mm/yyyy",
		onRender: function(date) {
			return date < date_livraison_min ? 'disabled' : '';
		}
	});
});
