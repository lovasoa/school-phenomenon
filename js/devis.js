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

function selection_nbr (liste, nbr, greater) {
	/* liste est une liste d’objets ayant un attribut 'nbr'.
		nbr est un nombre
		Retourne le premier élément de la liste tel que nbr < element.nbr
		ou null si il n'existe pas de tel élément
	*/
	var step = greater ? -1 : 1;
	for (var i=0, l=liste.length; i<l; i++) {
		var idx = greater ? l-1-i : i;
		var cmp = (nbr - liste[idx].nbr) * step;
		if (cmp < 0 || (greater && cmp==0)) return liste[idx];
	}
	return null;
}

// Fill the colors dropdowns
$.ajax({
	"url" : "config/articles.json",
	"dataType" : "json",
	"success" : function (conf) {
		window.conf = conf;
		var nuancier = conf.nuancier,
			liste_articles = conf.liste_articles;

		// Remplissage du dropdown de choix des couleurs
		for(var i=0; i<nuancier.length; i++) {
			var num = nuancier[i].num,
				couleur = nuancier[i].couleur;
			var $preview = $("<div>")
							.css("background-color", couleur)
							.data("num", num);
			if (nuancier[i].motif) {
				$preview.css("background-image", 'url(images/motifs/'+num+'.jpg)');
			}

			$preview.click(function(){
				var $this = $(this);
				var color = $this.css('background-color');
				$($this.parent().data('target'))
						.val($this.data('num'))
						.css({
							"background-image": $this.css('background-image'),
							'background-color' : color,
							'color' : color
							})
						.on("change keyup", function(){
							$(this).css({
								"background-image":"",
								"background-color":"",
								"color":""
							});
						});
			});

			$(".dropdown-colors").each(function(){
				$(this).append($preview.clone(true));
			});
		}

		// Gestion des articles
		for (var i=0; i<liste_articles.length; i++) {
			var article = liste_articles[i];
			var min = article.nbr_min || conf.commande_nbr_min;
			var $nbrInput = $("#"+article.id+"-nbr")
					.data('article-id', article.id)
					.attr('min', min)
					.attr("title", "Impossible de passer une commande de moins de "+min+" articles.")
					.on("change focus blur mouseup", article, function onNbrChange(event){
						var $this = $(this);
						var nbr = parseInt($this.val()) || 0;
						var article = event.data;

						// Gestion du nombre d'article minimum
						var $btn = $("#personnaliser-"+article.id);
						if (nbr === 0) {
							$this.parent().removeClass("has-error").removeClass("has-success");
							$this.tooltip("hide");
							$btn.attr("disabled", true);
						} else if (nbr >= $this.attr('min')) {
							$this.parent().removeClass("has-error").addClass("has-success");
							$this.tooltip("hide");
							$btn.attr("disabled", false);
						} else {
							$this.parent().addClass("has-error").removeClass("has-success");
							$this.tooltip("show");
							$btn.attr("disabled", true);
						}

						//Gestion des restrictions de couleur sur les petits nombres d'articles
						var article_id = article.id;
						for (var j=0; j<article.choix_couleurs.length; j++) {
							var choix_couleur = article.choix_couleurs[j];
							var restrictions = choix_couleur.restriction_couleurs || [];
							var couleurs = null; // Pas de restriction par défaut
							var restriction = selection_nbr(restrictions, nbr);
							var dropdown = $("#dropdown-colors-"+choix_couleur.type_couleur+"-"+article_id),
								group = dropdown.parent(".choose-color").show()
								divs = dropdown.find("div").show();

							if (restriction === null) continue; // Pas de restriction à appliquer

							var couleurs = restriction.couleurs;
							if (couleurs.length === 0) {
								// Si il n’y a aucune couleur à choisir, on cache le champ de texte
								group.find("input").val('').trigger('change');
								group.hide();
							} else {
								// Sinon, on filtre les couleurs affichées
								for (var n=0; n<divs.length; n++) {
									var div = $(divs[n]),
										num = div.data('num')+''; //Must be a string
									if (couleurs.indexOf(num) === -1) {
										div.hide();
									}
								}
							}
						}

						// Affichage des prix à l’unité
						var $prix = $("#prix-unite-"+article.id);
						var $prix_varie = $("#prix-varie-"+article.id).addClass("hidden");
						var prix_obj = selection_nbr(article.prix, nbr, true);
						var prix_str = prix_obj.prix + conf.devise;
						if (prix_obj.approx) {
							prix_str = 'environ ' + prix_str;
							$prix_varie.removeClass("hidden");
						}
						$prix.text(prix_str);
					});
		}
	},
	"error" : function(xhr, status, e) {
		alert("Impossible de charger le nuancier.");
		console.log(status, e);
	}
});
