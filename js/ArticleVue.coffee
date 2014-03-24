###
Vues
###
class ArticleVue extends EventEmitter
  constructor: (article_data)->
    @id = article_data.id
    @$nbrInput = $("##{id}-nbr")
    anychange = 'change focus blur keyup mouseup'
    @$nbrInput.on anychange, @majNbr
    @$couleur1Input = $("#couleur-principale-#{id}")
    @$couleur1Input.on anychange @majCouleurPrincipale

  majCouleurPrincipale: =>
    @emit 'majCouleurPrincipale', @$couleur1Input.val()
  majNbr: =>
    @emit 'majNbr', @$inputNbr.val()
  setPrix: (prix) ->
    prix = Math.round(prix*100)/100 #Arrondi au centime
    $("#prix-unite-#{@id}").text(prix)

class CommandeVue extends EventEmitter
  constructor: (conf) ->
    @$prixTotal = $("#prix-total")
    # Date de livraison
    $("#date-livraison").datepicker(minDate:0)
    super

  setPrix: (prix) => @$prixTotal.text(prix)
  setDelai: (delai) -> 
    $('#date-livraison').datepicker 'option', 'minDate', delai
