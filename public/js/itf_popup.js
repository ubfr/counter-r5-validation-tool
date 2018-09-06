 var formupdated = false;
 $(document).ready(function() {

    $(document).on("click",".travel-pop",function(){
        var popup_urlnames = $(this).attr("href"); 
        $.itfPopup.open({
                    items: {
                        src: popup_urlnames,
                        type: 'ajax',
                        alignTop: false,
                        overflowY: 'scroll'
                        }
                  });
         return false;              
    });

    $(document).on("click",".travel-pop-up",function(){
        var popup_urlnames = $(this).attr("data-href"); 
        $.itfPopup.open({
                    items: {
                        src: popup_urlnames,
                        type: 'ajax',
                        alignTop: false,
                        overflowY: 'scroll'
                        }
                  });
         return false;              
    });

    
  $(document).on("click","a.travel-image-pop-gallery",function(){

    var itfgalleryboxes = $(this).parents("div.itfgallery_boxes");
    var itfitems=[];
    var currentimg = $(this).attr("href");
    itfitems.push({'src':currentimg});
    
    itfgalleryboxes.find("a.travel-image-pop-gallery").each(function(){ if(currentimg!=$(this).attr("href"))  itfitems.push({'src':$(this).attr("href")}) });

    $.itfPopup.open({
                'items' : itfitems,
                  type: 'image',
                  gallery: {
                    enabled: true
                  }
            });
   return false;
  });

    $(document).on("click",".travel-image-pop",function(){
        var popup_urlnames = $(this).attr("href"); 
        $.itfPopup.open({
                    items: {
                        src: popup_urlnames,
                        type: 'image',
                        alignTop: false,
                        overflowY: 'scroll'
                        }
                  });
         return false;              
    });

                            
    $('.travel-pops').itfPopup({
      type: 'ajax',
      alignTop: false,
      overflowY: 'scroll'
    });

	 $('.image_popup').itfPopup({
          type: 'image',
          closeOnContentClick: true,          
          image: {
            verticalFit: true
          }          
        });

    $(document).on('click', '.itf-modal-dismiss', function (e) {
          e.preventDefault();
          $.itfPopup.close();
      });

    //Form Lost Data Start  
    $(document).on('change','input.form-control[type=text]',function(){
      formupdated=true; 
      //console.log(this);
    });   

});
	
