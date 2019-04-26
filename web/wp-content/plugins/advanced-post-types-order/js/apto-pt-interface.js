
    jQuery(document).ready(function()
        {
            
            jQuery('table.wp-list-table #the-list').sortable({
                                                        'items': 'tr',
                                                        'axis': 'y',
                                                        'update' : function(e, ui) {
                                                           
                                                            var order       =   jQuery('#the-list').sortable('serialize');
                                                                                                           
                                                            var queryString = { "action": "update-post-type-interface-sort", "post_type" : APTO_vars.post_type, "taxonomy" : APTO_vars.taxonomy , "term_id" : APTO_vars.term_id, "paged" : APTO_vars.paged, "sort_id" : APTO_vars.sort_id, "nonce" : APTO_vars.nonce, "order" : order};
                                                            //send the data through ajax
                                                            jQuery.ajax({
                                                              type: 'POST',
                                                              url: ajaxurl,
                                                              data: queryString,
                                                              cache: false,
                                                              dataType: "html",
                                                              success: function(data){
                                                
                                                              },
                                                              error: function(html){

                                                                  }
                                                            });
                                                        
                                                        }
                                                    });
       

    });  