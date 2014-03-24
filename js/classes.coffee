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

class InputWatcher extends EventEmitter
  constructor: (inputElm) ->
    @$input = $(inputElm)
    @$input.on("change blur focus mouseup keyup",
                 -> @emit('change', @$input.val())
    )

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
  constructor: () ->
    super
    @nbr = 0
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

class Article
  constructor: (article_data, nuancier) ->
    @vue = new ArticleVue article_data, nuancier
    @vue.on 'majNbr', @setNbr
    @vue.on 'majCouleurPrincipale', @setCouleur
    @id = article_data.id
    @setNbr(0)
    @couleur = 'noir'
    @delais_data = article_data.delais
    @arbrePrix = new ArbrePrix
    @arbreDelais = new ArbreDelais

  majDelais: (nbr) ->
    delaisobj = selection_nbr delais_data,nbr,true
    if delaisobj?
      delai = delaisobj.delai
      if delaisobj.exceptions
        for e in delaisobj.exceptions
          couls = e.couleurs_exception
          if couls and couls.indexOf(@couleur) isnt -1
            delai = e.nouvdelai
      @arbreDelais.setVal(delai)
  setNbr: (nbr) =>
    @nbr = nbr
    @arbrePrix.setNbr nbr
    @majDelais nbr
  setPrix: (prix) ->
    @arbrePrix.setVal(prix)
  setCouleur: (couleur) =>
    @couleur = ''+couleur # Les couleurs sont stockées sous forme de chaînes de caractères
  ajout_option: (option) ->
    @vue.ajout_option(option)

class Commande
  #Groupement d'articles
  constructor: (conf) ->
    @vue = new CommandeVue(conf)
    @arbrePrix = new ArbrePrix
    @arbrePrix.on 'maj', (prixTotal) => @vue.setPrix prixTotal
    @arbreDelais = new ArbreDelais
    @arbreDelais.on 'maj', (delaisTotal) => @vue.setDelai delaiTotal
    @articles = {}

  ajoutArticle: (article) ->
    @articles[article.id] = article
    @arbrePrix.ajout_enfant article.arbrePrix
    @arbreDelais.ajout_enfant article.arbreDelais

if module? and module.exports?
  module.exports =
    ArbreDelais: ArbreDelais
    ArbrePrix: ArbrePrix

