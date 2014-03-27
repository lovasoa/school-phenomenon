###
  delais.coffee pour School Phenomenon
  @author: Ophir LOJKINE
  Gestion des délais en fonction des types de commande
###

# Permet l’héritage multiple
mixIn = (classes...) ->
  class Base
    constructor: (args...)->
      c.constructor.apply(@, args) for c in classes
  classes.reduce ((o,n) -> o::[key]=method for key,method of n::;o), (Base)

class EventEmitter
  constructor: -> @listeners = {}
  on: (ev, fun) ->
    @listeners[ev] = @listeners[ev] or []
    @listeners[ev].push fun
    return this
  emit: (ev, args...) ->
    fun.apply(this,args) for fun in (@listeners[ev] or [])
    return this

class ArbreValeurs extends EventEmitter
  defaultVal:0
  constructor: (val) ->
    super # Initialize the event emitter
    @setVal(val ? @defaultVal)
    @val_enfants = @defaultVal
    @enfants = []
  _maj: -> @emit('maj', @getTotal())
  _maj_enfants: ->
    @_setValEnfants(@enfants.reduce ((p,c) => @valReducer(p,c.getTotal())), @defaultVal)
  _setValEnfants: (val) ->
    @val_enfants = val
    @_maj()
  setVal: (val) ->
    @val_propre = val
    @_maj()
  getTotal: -> @valReducer(@val_enfants, @val_propre)
  ajout_enfants: (enfants) ->
    @enfants.push.apply(@enfants, enfants)
    (enfant.on 'maj', => @_maj_enfants()) for enfant in enfants
    @_maj_enfants()
  ajout_enfant: (enfant) -> @ajout_enfants([enfant])

class ArbrePrix extends ArbreValeurs
  defaultVal: 0
  constructor: (prix, nbr=0) ->
    super
    @nbr = nbr
  setNbr: (nbr) ->
    @nbr = parseInt(nbr)
    @_maj()
  setVal: (prix) -> super(parseFloat(prix))
  valReducer: (prev,cur) -> prev + cur
  getTotal: -> @nbr * (@val_enfants + @val_propre)

class ArbreApprox extends ArbreValeurs
  defaultVal: false
  setVal: (approx) -> super(!!approx)
  valReducer: (prev,cur) -> prev or cur

class ArbreDelais extends ArbreValeurs
  defaultVal: -Infinity
  setVal: (delais) -> super(parseInt(delais))
  valReducer: Math.max

###
Controleurs
###
class Options
  constructor: (article_id)->
    # Une option est une personnalisation
    @prix_options_tab = []
    @options = []
    @prix_options = new ArbrePrix 0, 1
    @nbr = 0
    @article_id = article_id
    @$inputNbr = $ ('#'+@article_id+'-nbr')
  ajout: (option) ->
    @perso_id = html_id(option.perso)
    arbre = new ArbrePrix 0
    @prix_options_tab.push(arbre)
    @prix_options.ajout_enfant(arbre)
    @options.push option
    nbrmin = option.nbr_min
    $checkbox = $ document.getElementById html_id "perso-#{@article_id}-#{@perso_id}"
    $checkbox.change (e) =>
        coche = $checkbox.is(':checked')
        if coche and nbrmin > @$inputNbr.val()
          $checkbox.attr 'checked',false
          return alert "Vous devez faire une commande d’au moins #{nbrmin}
                        articles pour pouvoir choisir cette option."
        arbre.setNbr (if coche then 1 else 0)
        arbre.setVal @calcPrix option,@nbr

  calcPrix: (option, nbr) =>
    prixobj = selection_nbr option.prix, nbr, true
    if prixobj then prixobj.prix else 0
  setNbr: (nbr) ->
    @nbr = nbr
    for opt,i in @prix_options_tab
      opt.setVal(@calcPrix(@options[i], nbr))

class Article
  constructor: (article_data, nuancier) ->
    @vue = new ArticleVue article_data, nuancier
    @vue.on 'majNbr', @setNbr
    @vue.on 'majCouleurPrincipale', @setCouleur
    @id = article_data.id
    @couleur = 'noir'
    @article_data = article_data
    @delais_data = article_data.delais or []
    @prix_data = article_data.prix or []
    @arbrePrix = new ArbrePrix
    @arbreDelais = new ArbreDelais 0
    @options = new Options(@id)
    @arbrePrix.ajout_enfant @options.prix_options
    @arbrePrix.on 'maj', (prix) => @vue.setPrix prix/@arbrePrix.nbr
    @setNbr 0

  majDelais: (nbr) ->
    delaisobj = selection_nbr @delais_data,nbr,true
    if delaisobj?
      delai = delaisobj.delai
      if delaisobj.exceptions
        for e in delaisobj.exceptions
          couls = e.couleurs_exception
          if couls and couls.indexOf(@couleur) isnt -1
            delai = e.nouvdelai
      @arbreDelais.setVal(delai)
  setNbr: (nbr) =>
    nbr = parseInt(nbr)
    if isNaN(nbr) or nbr is 0 then return @vue.setValid "troppeu"
    if nbr < @article_data.nbr_min then return @vue.setValid "invalide"
    @vue.setValid "valide"
    @nbr = nbr
    @arbrePrix.setNbr nbr
    @majDelais nbr
    @options.setNbr nbr
    @setPrix @calcPrix nbr
  calcPrix: (nbr) ->
    prixobj = selection_nbr @prix_data, nbr, true
    if prixobj then prixobj.prix else 0
  setPrix: (prix) ->
    @arbrePrix.setVal(prix)
  setCouleur: (couleur) =>
    @couleur = ''+couleur # Les couleurs sont stockées sous forme de chaînes de caractères
    @majDelais @nbr

class Commande
  #Groupement d'articles
  constructor: (conf) ->
    @vue = new CommandeVue(conf)
    @arbrePrix = new ArbrePrix 0, 1
    @arbrePrix.on 'maj', (prixTotal) =>
      @vue.setPrix prixTotal
    @arbreDelais = new ArbreDelais 0
    @arbreDelais.on 'maj', (delaiTotal) => @vue.setDelai delaiTotal
    @articles = {}

  ajout_article: (article) ->
    @articles[article.id] = article
    @arbrePrix.ajout_enfant article.arbrePrix
    @arbreDelais.ajout_enfant article.arbreDelais

if module? and module.exports?
  module.exports =
    ArbreDelais: ArbreDelais
    ArbrePrix: ArbrePrix

