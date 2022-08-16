(function(){
    var scriptSrc = document.currentScript.src;
    var packagePath = scriptSrc.replace('/scripts/scripts.js', '').trim();
    var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
    var packageId = re.exec(scriptSrc.toLowerCase())[1];
    var userId = $('#userGuid').val();
    const url = window.location.href.toLowerCase();

    //create new menu option for shopify
    $(document).ready(function(){
        var baseUrl = window.location.hostname;
        var user = $("#userGuid").val();
        if(($('#merchantId') && $('#merchantId').length)){
			//console.log('test');
			var a = document.createElement("a");
            a.href = packagePath + "/index.php?user=" + user;
            a.innerHTML = "Shopify";

            var b = document.createElement("li");
                b.appendChild(a);

            var c = document.querySelector("ul.login-nav");
                c.insertBefore(b, document.querySelector("ul.login-nav li:nth-child(1)"));
        } 
    });
    
    //save shopify credentials
    $(document).ready(function(){
        jQuery("#shopify-connect").click(function (){
            saveShopifyData();
        })
    });

    //fix footer's wonky look
    $(document).ready(function(){
        if(window.location.href.indexOf(packageId) > -1){
            var footer_wrapper = document.querySelector(".footer-wrapper");
            footer_wrapper.style.width = "auto";
            footer_wrapper.style.margin = "auto";
        }

        if (url.indexOf("/user/checkout/success") >= 0) {
            waitForElement(".invoice-id", function() {
                
                syncOrderShopify();
                
            });
         }
        

        //merchant order list

        if (url.indexOf("/user/manage/orders") >= 0) {
         
            //append new header for sync
            waitForElement(".refund-icon", function ()
            {
                
                $('.order-list-tit-sec ').append('<div class="order-status-sec">Shopify Sync</div>');
             
                $('#order-list .order-un-read-box').append(`<div class="order-status-sec">
                <button class="form-control shop-sync">Sync Order</button>
                </div>`);
             
            })

            //get order details via order id https://{{your-marketplace}}.arcadier.io/api/v2/users/{{merchantID}}/orders/{{orderID}} ---? 
            //this endpoint do not get cf for cartitems, reference invoice id instead then validate the order id

            $('body').on('click', '.shop-sync', function () {
                const invoiceId = $(this).parents('.order-un-read-box').find('.invoice-number').text();
                const orderId = $(this).parents('.order-un-read-box').attr('data-order-guid');
                
                syncOrderShopifyManual(orderId, invoiceId)

            })

              
        }
    });

    function saveShopifyData(){
        var apiUrl = packagePath + '/shopify-token.php';
        var data = {
            'shop': $('#store-name').val(),
            'pluginID': packageId,
            'marketplace': window.location.hostname,
            'merchant_guid': userId
        };
        
        $.ajax({
            url: apiUrl,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            complete: function(){
                toastr.info('You will be redirected to the specified Shopify store to install the Arcadier app.');
            },
            success: function(result) {
                setTimeout(function(){
                    if(result.startsWith("https://")){
                        location.href = result;
                    }
                },   
                1000);
            },
            error: function(jqXHR, status, err) {
            	toastr.error('There was a problem connecting to your Shopify store.');
            }
        });
    }

    
    function waitForElement(elementPath, callBack) {
        window.setTimeout(function() {
            if ($(elementPath).length) {
                callBack(elementPath, $(elementPath));
            } else {
                waitForElement(elementPath, callBack);
            }
        }, 700);
    }

    function syncOrderShopify() {
        // console.log(result);
        var apiUrl = packagePath + '/sync_orders.php';
        var data = {
            'invoice-id' : $('.invoice-id').text()

        }
        
        $.ajax({
            url: apiUrl,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(result) {
            result =  JSON.parse(result);
                console.log(`result  ${result}`);

            },
            error: function(jqXHR, status, err) {
            //	toastr.error('Error!');
            }
        });
    }


    function syncOrderShopifyManual(orderId, invoiceId)
    {
        
         var apiUrl = packagePath + '/sync_orders_manual.php';
            var data = {
                'invoice-id': invoiceId,
                'order-id':  orderId

            }
        
        $.ajax({
            url: apiUrl,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(result) {
           result =  JSON.parse(result);
                console.log(`result  ${result}`);
                
                if (result == 'success') {
                     toastr.success(`Synced order Number: ${orderId}`);
                } else {
                    toastr.error(`This order has already been synced`);

                }

            },
            error: function(jqXHR, status, err) {
            //	toastr.error('Error!');
            }
        });
        
    }
})();