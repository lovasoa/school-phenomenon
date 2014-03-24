###
Vues
###
class ArticleVue extends EventEmitter
  constructor: (article_data)->
    @id = article_data.id
    @$nbrInput = $("##{@id}-nbr")
    anychange = 'change focus blur keyup mouseup'
    @$nbrInput.on anychange, @majNbr
    @$couleur1Input = $("#couleur-principale-#{@id}")
    @$couleur1Input.on anychange, @majCouleurPrincipale
    @$btn = $("#personnaliser-#{@id}")
    super

  majCouleurPrincipale: =>
    @emit 'majCouleurPrincipale', @$couleur1Input.val()
  majNbr: =>
    @emit 'majNbr', @$nbrInput.val()
  setPrix: (prix) ->
    prix = Math.round(prix*100)/100 #Arrondi au centime
    $("#prix-unite-#{@id}").text(prix).hide().show('pulsate')
  setValid: (valide) ->
    # Gestion du nombre d'article minimum
    if valide is 'valide'
      @$nbrInput.parent().removeClass("has-error").addClass "has-success"
      @$nbrInput.tooltip "hide"
      @$btn.attr "disabled", false
    else if valide is 'invalide'
      @$nbrInput.parent().addClass("has-error").removeClass "has-success"
      @$nbrInput.tooltip "show"
      @$btn.attr "disabled", true
    else # Le champ n'est pas rempli
      @$nbrInput.parent().removeClass("has-error").removeClass "has-success"
      @$nbrInput.tooltip "hide"
      @$btn.attr "disabled", true

class CommandeVue extends EventEmitter
  constructor: (conf) ->
    @$prixTotal = $("#prix-total")
    # Date de livraison
    $("#date-livraison").datepicker(minDate:0)
    super

  setPrix: (prix) => @$prixTotal.text(prix)
  setDelai: (delai) -> 
    $('#date-livraison').datepicker 'option', 'minDate', delai
    $('#delai-livraison').text delai
    $('#msg-delai-livraison').toggle(delai > 0)
