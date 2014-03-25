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

# Preview du prix déplacable
console.log $("#prix-devis-boite").draggable()

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

    window.commande = new Commande
    # Gestion des articles
    for article in liste_articles
      controleur_article = new Article article
      commande.ajout_article controleur_article

      for option in conf.personnalisations
        controleur_article.options.ajout option


      min = article.nbr_min or conf.commande_nbr_min
      $nbrInput = $("#" + article.id + "-nbr")
                      .data("article-id", article.id)
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
