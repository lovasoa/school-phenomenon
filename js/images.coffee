###
  images.coffee pour School Phenomenon
  @author: Ophir LOJKINE
  Gestion de l’ajout de logos personnalisés
###

TAILLE_IMAGE_MAX = 5e6 # En octets

URL = window.URL || window.webkitURL |{}

if not URL.createObjectURL?
  alert """Votre navigateur ne permet pas la lecture de fichiers locaux.
         Vous ne pourrez pas placer dynamiquement les images de personnalisation de vos sweats.
         Si vous souhaitez avoir cette possibilité, visitez cette page depuis une version récente de Firefox ou Chrome."""


$ ".image-personnalisation"
  .change (e) ->
    input = e.target
    $inputs = $(input).parent()
    imgId = $inputs.data "imgId"
    file = e.target.files[0]
    bloburl = URL.createObjectURL(file)

    if file.size > TAILLE_IMAGE_MAX
        alert "Fichier trop volumineux. Taille maximale autorisée : #{TAILLE_IMAGE_MAX/1e6}Mo"
        input.value="";

    $imagezone = $($inputs.data("zone-personnalisation"))
    if imgId
      $('#'+imgId).remove()
    else
      imgId = "image-perso-deplacable-" + parseInt(Math.random()*1e6)
    $ "<img>"
      .attr 'id', imgId
      .attr 'alt', file.name
      .attr 'src', bloburl
      .attr 'class', 'image-perso-deplacable'
      .width(80).height("auto")
      .error -> alert "Impossible de lire l’image."
      .detach()
      .one 'load', ->
        $(this).appendTo $imagezone
               .resizable (
                  containment: "parent"
                  aspectRatio: true
                  stop: ->
                    $this = $(this)
                    w = 100 * $this.width() / $this.parent().width() #Pourcentage
                    h = 100 * $this.height() / $this.parent().height()
                    $inputs.find('.w').val(w)
                    $inputs.find('.h').val(h)
               )
               .parent().draggable(
                 containment:"parent",
                 cursor:"move"
                 stop: ->
                   $this = $(this)
                   x = 100 * $this.position().left / $this.parent().width()
                   y = 100 * $this.position().top / $this.parent().height()
                   $inputs.find('.x').val(x)
                   $inputs.find('.y').val(y)
               )
               .css top:0,left:0
      $inputs.data "imgId", imgId

      if $inputs.is ':last-child'
        $inputs.clone true
               .data 'imgId', ''
               .appendTo $inputs.parent()
               .find "input"
               .val ''

