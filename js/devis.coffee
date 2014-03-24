###
devis.js - pour school phenomenon
@author: Ophir LOJKINE
###


selection_nbr = (liste, nbr, greater=false) ->
  # liste est une liste d’objets ayant un attribut 'nbr'.
  #		nbr est un nombre
  #		Retourne le premier élément de la liste tel que nbr < element.nbr
  #		ou null si il n'existe pas de tel élément
  for obj in liste by (if greater then -1 else 1)
    if (not greater and nbr < obj.nbr) or (greater and nbr >= obj.nbr)
      return obj
  return null

html_id = (str) -> str.replace RegExp(" ", "g"), "_"
  # En html5, la seule restriction sur les id est qu'ils ne doivent pas contenir d'espaces
  # http://www.w3.org/TR/html-markup/global-attributes.html#common.attrs.id
  

maj_prix = ->
  # Calcul et affichage des prix à l’unité et du prix total
  prix_total =
    prix: 0
    approx: false

  ajout_prix = (p1, p2) ->
    p1.prix += p2.prix | 0
    p1.approx = !!(p1.approx or p2.approx)


  for article in conf.liste_articles
    # Calcul du prix à l’unité
    nbr = $("#" + article.id + "-nbr").val() | 0
    $prix = $("#prix-unite-" + article.id)
    $prix_varie = $("#prix-varie-" + article.id).addClass("hidden")
    prix_obj = $.extend({}, selection_nbr(article.prix, nbr, true)) # Object copy
    continue  if prix_obj is null

    # Calcul du prix des personnalisations supplémentaires
    for perso in conf.personnalisations
      input_id = html_id("perso-" + article.id + "-" + perso.perso)
      if $("#" + input_id).is(":checked")
        prix_perso_obj = selection_nbr(perso.prix, nbr, true)
        ajout_prix prix_obj, prix_perso_obj  if prix_perso_obj and prix_perso_obj.prix

    prix_str = prix_obj.prix + conf.devise
    if prix_obj.approx
      prix_str = "environ " + prix_str
      $prix_varie.removeClass "hidden"
    $prix.text prix_str
    prix_obj.prix *= nbr # Prix total de ce type d'articles
    ajout_prix prix_total, prix_obj

  $("#prix-total").text prix_total.prix
  $("#prix-total-varie").toggle prix_total.approx
  return prix_total

#Ajout des "tooltips" (informations supplémentaires sur les champs à remplir)
$("#association").tooltip()
$("#date-naissance input").on "blur change keyup", ->
  # Validation de l'année de naissance
  j = parseInt($("#date-naissance-jour").val())
  m = parseInt($("#date-naissance-mois").val())
  a = parseInt($("#date-naissance-annee").val())
  #Si tout a été rempli
  if j and m and a
    # Date() prend un mois entre 0 et 11
    date_dixhuitans = new Date(a + 18, m - 1, j)
    action = (if (date_dixhuitans > (new Date())) then "show" else "hide")
    $("#alerte-age")[action] 500

$(".article-nbr").on "change mouseup keyup blur", maj_prix
$(".checkbox-perso").on "change mouseup keyup blur", maj_prix


gestion_nbr_articles = (article) ->
  # Gestion du nombre d'article minimum
  id = article.id
  $nbrInput = $("##{id}-nbr")
  nbr = parseInt($nbrInput.val()) or 0
  $btn = $("#personnaliser-#{id}")
  if nbr is 0
    $nbrInput.parent().removeClass("has-error").removeClass "has-success"
    $nbrInput.tooltip "hide"
    $btn.attr "disabled", true
  else if nbr >= $nbrInput.attr("min")
    $nbrInput.parent().removeClass("has-error").addClass "has-success"
    $nbrInput.tooltip "hide"
    $btn.attr "disabled", false
  else
    $nbrInput.parent().addClass("has-error").removeClass "has-success"
    $nbrInput.tooltip "show"
    $btn.attr "disabled", true

conf = {}
# Fill the colors dropdowns
$.ajax
  url: "config/articles.json"
  dataType: "json"
  success: (config) ->
    conf = config
    nuancier = conf.nuancier
    liste_articles = conf.liste_articles

    # Remplissage du dropdown de choix des couleurs
    for nuance in nuancier
      num = nuance.num
      couleur = nuance.couleur
      $preview = $ "<div>"
                  .css "background-color", couleur
                  .data "num", num
      $preview.css "background-image", "url(images/motifs/#{num}.jpg)"  if nuance.motif
      $preview.click ->
        $this = $ this
        color = $this.css "background-color"
        $($this.parent().data("target")).val($this.data("num")).css(
          "background-image": $this.css("background-image")
          "background-color": color
          color: color
        ).on "change keyup", ->
          $(this).css
            "background-image": ""
            "background-color": ""
            color: ""

      $(".dropdown-colors").each ->
        $(this).append $preview.clone(true)
        return

    # Gestion des articles
    for article in liste_articles
      min = article.nbr_min or conf.commande_nbr_min
      $nbrInput = $("#" + article.id + "-nbr")
                      .data("article-id", article.id)
                      .attr("min", min)
                      .attr("title", "Impossible de passer une commande de moins de " + min + " articles.")
                      .on('change focus blur keyup mouseup', -> gestion_nbr_articles(article))
                      .on("change focus blur mouseup", article, (event) ->
                        $this = $(this)
                        nbr = parseInt($this.val()) or 0
                        article = event.data

                        #Gestion des restrictions de couleur sur les petits nombres d'articles
                        article_id = article.id
                        for choix_couleur in article.choix_couleurs
                          restrictions = choix_couleur.restriction_couleurs or []
                          couleurs = null # Pas de restriction par défaut
                          restriction = selection_nbr(restrictions, nbr)
                          dropdown = $("#dropdown-colors-" + choix_couleur.type_couleur + "-" + article_id)
                          group = dropdown.parent(".choose-color").show()
                          divs = dropdown.find("div").show()
                          continue  if restriction is null # Pas de restriction à appliquer
                          couleurs = restriction.couleurs
                          if couleurs.length is 0
                            # Si il n’y a aucune couleur à choisir, on cache le champ de texte
                            group.find("input").val("").trigger "change"
                            group.hide()
                          else
                            # Sinon, on filtre les couleurs affichées
                            for div in divs
                              div = $(div)
                              num = div.data("num") + "" #Must be a string
                              div.hide()  if couleurs.indexOf(num) is -1
      )

  error: (xhr, status, e) ->
    alert "Impossible de charger le nuancier."
    console.log status, e
    return
