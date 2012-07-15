/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($){
    
    
        $('.widefat img').bind('click',function(evt){
        evt.preventDefault();
        var id =$(this).attr('class');
        
        var self = $(this);
        
            $.ajax({
            type :  "post",
            url : ajaxurl,
            timeout : 5000,
            data : {
                'action' : 'city_remove',
                'id' : id		  
            },			
            success :  function(data){               
                if(data==1){
                 self.parent().parent().parent().hide('slow');   
                }
            }
        })	//end of ajax	
        
        })
})
