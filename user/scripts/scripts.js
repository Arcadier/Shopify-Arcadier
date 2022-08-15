(function(){
    var scriptSrc = document.currentScript.src;
    var packagePath = scriptSrc.replace('/scripts/scripts.js', '').trim();
    var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
    var packageId = re.exec(scriptSrc.toLowerCase())[1];
    const url = window.location.href.toLowerCase();
    $(document).ready(function(){
        var baseUrl = window.location.hostname;
        var token = getCookie('webapitoken');
        var user = $("#userGuid").val();
        if(($('#merchantId') && $('#merchantId').length)){
			//console.log('test');
			var a = document.createElement("a");
            a.href = "https://" + baseUrl + "/user/plugins/" + packageId + "/index.php?user=" + user;
            a.innerHTML = "MagentoTest1";
            a.target = "_blank";

            var b = document.createElement("li");
                b.appendChild(a);

            var c = document.querySelector("ul.login-nav");
                c.insertBefore(b, document.querySelector("ul.login-nav li:nth-child(1)"));
        } 
    });

    
    function waitForElement(elementPath, callBack) {
        window.setTimeout(function() {
            if ($(elementPath).length) {
                callBack(elementPath, $(elementPath));
            } else {
                waitForElement(elementPath, callBack);
            }
        }, 700);
    }



    function getCookie(name){
        var value = '; ' + document.cookie;
        var parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
    }

    
    function saveShopifyData()
  {
    // console.log(result);
    var apiUrl = packagePath + '/shopify_link_account.php';
    var data = {
        'shopify-key': $('#api-key').val(),
        'shopify-store': $('#store-name').val(),
        'secret-key': $('#secret-key').val()

    }
      
      $.ajax({
        url: apiUrl,
        method: 'POST',
              contentType: 'application/json',
            data: JSON.stringify(data),
        success: function(result) {
          result =  JSON.parse(result);
            console.log(`cf ${result}`);

            setTimeout(
            window.location.href = result,
            
                5000);
           
        
        },
        error: function(jqXHR, status, err) {
        //	toastr.error('Error!');
        }
      });
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

            },
            error: function(jqXHR, status, err) {
            //	toastr.error('Error!');
            }
        });
        
    }

    var pathname = (window.location.pathname + window.location.search).toLowerCase();
   // var token = commonModule.getCookie('webapitoken');
    const packageVersion = "1.0.0";
    const localstorageLifetime = 86400;
    var hostname = window.location.hostname;
    var scriptSrc = document.currentScript.src;
    var packagePath = scriptSrc.replace('/scripts/scripts.js', '').trim();
    var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
    var packageId = re.exec(scriptSrc.toLowerCase())[1];
    var customFieldPrefix = packageId.replace(/-/g, "");
    var userId = $('#userGuid').val();
    var getPackageCustomFieldCache = userId + "_" + packageId;

    function getURLParam(key, target) {
        var values = [];
        if (!target) target = location.href;

        key = key.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");

        var pattern = key + '=([^&#]+)';
        var o_reg = new RegExp(pattern, 'ig');
        while (true) {
            var matches = o_reg.exec(target);
            if (matches && matches[1]) {
                values.push(matches[1]);
            } else {
                break;
            }
        }

        if (!values.length) {
            return null;
        } else {
            return values;
        }
    }

    $(document).ready(function(){
        var apiUrl = packagePath + '/_partial-check-license.php';
        $.ajax({
            url: apiUrl,
            method: 'GET',
            contentType: 'application/json',
            success: function(response) {
                response = response.trim();
                if (response == 'false') return;
                // TODO: add code here
                console.log('valid license. continue...');
                
            }
        });



        //save shopify credentials

        jQuery("#shopify-connect").click(function ()
        {

            saveShopifyData();
        })


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




})();