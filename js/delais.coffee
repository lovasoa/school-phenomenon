###
  delais.coffee pour School Phenomenon
  @author: Ophir LOJKINE
  Gestion des délais en fonction des types de commande
###

class Article
  constructor: (prix_propre=0, prix_approx=false) ->
    @nbr = 0
    # Le véritable prix d’un élément est la somme de son prix propre (@prix)
    # et du prix de tous ses enfants, le tout multiplié par le nombre d’articles
    # @prix_total = @nbr * (@prix_propre + @prix_enfants)
    @prix_propre = 0
    @prix_approx = false
    @delais = 0
    # @prix_enfants contient la somme des prix des enfants
    @prix_enfants = 0
    @delais_enfants = 0
    @enfants = []

  _maj_prix_enfants: () ->
    @prix_enfants = @enfants.reduce ((p,e) -> p + e.getPrixTotal()), 0
    @_maj_prix()

   _maj_prix : () -> this.emit('maj_prix')

  setPrixPropre: (prix) ->
    # Définit le prix propre
    @prix_propre = parseInt(prix)
    @maj_prix()

  setPrixApprox : (approx) ->
    @prix_approx = !!approx
    @maj_prix()

  getPrixTotal: () -> @nbr * (@prix_enfants + @prix_propre)

  ajout_enfant: (enfant) ->
    @enfants.push(enfant)
    enfant.on 'maj_prix', => _maj_prix_enfants()


