(function($){

    $('a[rel=oembed]').click(function(){
        var $this = $(this);
        $('#oEmbedPlayer').hide().html($this.next('script').html()).fadeIn();
        return false;
    })

})(jQuery)