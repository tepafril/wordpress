

    // setTimeout(function(){
    
    // }, 1000);

    jQuery(document).ready( function() {

        var bar = new ldBar(".loading_bar", {
            "stroke": '#0c75bc',
            "stroke-width": 10,
            "preset": "circle",
            "value": 0
        });

        var ordered_num = 1;


        function setDelay(i, log_str) {
            setTimeout(function(){
                jQuery('#log-textarea #text').html( jQuery('#log-textarea #text').html() + log_str );
                        
                var element = document.getElementById("log-textarea");
                element.scrollTop = element.scrollHeight;
            }, 500 * i);
        }


        function call_ajax(page_number)
        {
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : myAjax.ajaxurl,
                data : {action: "odoo_sync_action", page_number : page_number, nonce: nonce},
                success: function(response) {
                    jQuery('#loading').text('Loading...');
                    if(page_number == 0)
                    {
                        page_number = 1;
                    }
                    else
                    {
                        page_number++;
                    }

                    if(page_number <= response.total_page)
                    {
                        let percentage = ( page_number / response.total_page ) * 100;
                        bar.set(percentage, true);
                        call_ajax(page_number);
                        console.log(percentage);
                        let log_str = '';
                        for(var i=0; i<response.result.length; i++)
                        {
                            var settings = {
                                "url": "https://staging.toysnme.com.kh//wp-json/wc/v3/products?consumer_key=ck_92c1bfaa76a8a8b2289b11f256034473b5549299&consumer_secret=cs_ad82d7ca8059c7c5ba918c25438d2604f337b725",
                                "method": "POST",
                                "timeout": 0,
                                "headers": {
                                  "Content-Type": "application/json",
                                  "Cookie": "PHPSESSID=2dcbk40rp69t5hnso8o7af52hd"
                                },
                                "data": JSON.stringify({
                                    "name": response.result[i].name,
                                    "type":"simple",
                                    "regular_price": "100",
                                    "description":"Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.",
                                    "short_description":"Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.",
                                    "categories":[{"id":9},{"id":14}],
                                    "images":[
                                        {"src":"http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg"},
                                        {"src":"http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg"}
                                    ]
                                }),
                            };
                              
                            jQuery.ajax(settings).done(function (response) {
                                console.log(response);
                            });
                            log_str = ordered_num + ". " + response.result[i].name + "<br>";
                            setDelay(i, log_str);
                            ordered_num++;
                        }
                    }
                    else {
                        jQuery(this).attr("disabled", false);
                        jQuery('#loading').text('Completed');
                    }
                }
            });
        }
            
        jQuery("#sync_odoo").click( function(e) {
            e.preventDefault(); 
            post_id = jQuery(this).attr("data-post_id");
            nonce = jQuery(this).attr("data-nonce");
            var page_number = 0;
            jQuery(this).attr("disabled", true);
            jQuery('#loading').text('Preparing...');
            // page_number = call_ajax(page_number);

            var category_ids = [];
            var brand_ids = [];

            // Stage 1
            // Sync Categories
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : myAjax.ajaxurl,
                data : {action: "odoo_sync_action", stage : "categories", page_number : page_number, nonce: nonce},
                success: function(response) {
                    category_ids = response;
                    // Stage 2
                    // Sync Brands
                    jQuery.ajax({
                        type : "post",
                        dataType : "json",
                        url : myAjax.ajaxurl,
                        data : {action: "odoo_sync_action", stage : "brands", page_number : page_number, nonce: nonce},
                        success: function(response) {
                            brand_ids = response;
                        }
                    });
                }
            });

        });

    });