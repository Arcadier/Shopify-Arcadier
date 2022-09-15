(function(){
    var scriptSrc = document.currentScript.src;
    var packagePath = scriptSrc.replace('/scripts/scripts.js', '').trim();
    var baseUrl = packagePath.substring(0, packagePath.indexOf('/user'));
    var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
    var packageId = re.exec(scriptSrc.toLowerCase())[1];
    var userId = $('#userGuid').val();
    const url = window.location.href.toLowerCase();
    // var baseUrl = theCourierApp_scriptSrc = document.currentScript.src;
    // var theCourierApp_packagePath = theCourierApp_scriptSrc.replace('/scripts/scripts.js', '').trim(); //window.location.hostname.replace('fe.', "");
    var token = getCookie('webapitoken');
    let hostname = window.location.hostname;
    //create new menu option for shopify
    $(document).ready(function(){
       
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
    $(document).ready(function ()
    {
       if (url.indexOf("/merchants/dashboard") >= 0) {

             userId = window.REDUX_DATA.userReducer.user.ID;
            $('.sidebar-nav').append(`<li id="shopify-link"><a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="
    width: 24px;
    height: 19px;
    margin-right: 15px;
    fill: #4d4d4d;
    transform: translate(0px, 4px);
"><!-- Font Awesome Pro 5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) --><path d="M388.32,104.1a4.66,4.66,0,0,0-4.4-4c-2,0-37.23-.8-37.23-.8s-21.61-20.82-29.62-28.83V503.2L442.76,472S388.72,106.5,388.32,104.1ZM288.65,70.47a116.67,116.67,0,0,0-7.21-17.61C271,32.85,255.42,22,237,22a15,15,0,0,0-4,.4c-.4-.8-1.2-1.2-1.6-2C223.4,11.63,213,7.63,200.58,8c-24,.8-48,18-67.25,48.83-13.61,21.62-24,48.84-26.82,70.06-27.62,8.4-46.83,14.41-47.23,14.81-14,4.4-14.41,4.8-16,18-1.2,10-38,291.82-38,291.82L307.86,504V65.67a41.66,41.66,0,0,0-4.4.4S297.86,67.67,288.65,70.47ZM233.41,87.69c-16,4.8-33.63,10.4-50.84,15.61,4.8-18.82,14.41-37.63,25.62-50,4.4-4.4,10.41-9.61,17.21-12.81C232.21,54.86,233.81,74.48,233.41,87.69ZM200.58,24.44A27.49,27.49,0,0,1,215,28c-6.4,3.2-12.81,8.41-18.81,14.41-15.21,16.42-26.82,42-31.62,66.45-14.42,4.41-28.83,8.81-42,12.81C131.33,83.28,163.75,25.24,200.58,24.44ZM154.15,244.61c1.6,25.61,69.25,31.22,73.25,91.66,2.8,47.64-25.22,80.06-65.65,82.47-48.83,3.2-75.65-25.62-75.65-25.62l10.4-44s26.82,20.42,48.44,18.82c14-.8,19.22-12.41,18.81-20.42-2-33.62-57.24-31.62-60.84-86.86-3.2-46.44,27.22-93.27,94.47-97.68,26-1.6,39.23,4.81,39.23,4.81L221.4,225.39s-17.21-8-37.63-6.4C154.15,221,153.75,239.8,154.15,244.61ZM249.42,82.88c0-12-1.6-29.22-7.21-43.63,18.42,3.6,27.22,24,31.23,36.43Q262.63,78.68,249.42,82.88Z"></path></svg>
            Shopify

            </a></li>`);

            $('body').on('click', '#shopify-link', function ()
            {

                redirect(userId);
            })
           
       }

        if (url.indexOf("/user/plugins") >= 0) {
            $('.navigation').hide();
            $('.footer-wrapper').hide();
            $('.search-bar').hide()


            $('.category-div').css('height', '400px'); 
            $('.category-div').niceScroll({ cursorcolor: "#b3b3b3 ", cursorwidth: "6px", cursorborderradius: "5px", cursorborder: "1px solid transparent", touchbehavior: true, preventmultitouchscrolling: true, enablekeyboard: true });
        }

        
        
        if(window.location.href.indexOf(packageId) > -1){
            var footer_wrapper = document.querySelector(".footer-wrapper");
            footer_wrapper.style.width = "auto";
            footer_wrapper.style.margin = "auto";
        }

        if (url.indexOf("/user/checkout/success") >= 0) {
            waitForElement(".invoice-id", function() {
                //syncOrderShopify();
                
            });
        }
        
        // if(window.location.href === baseUrl){
        //     var shopifyLink = $('ul.login-nav.dropdown-menu.hidden-xs li:first a');
	    //      window.location.href = $(shopifyLink ).attr('href');
        // }
        if(window.location.href === 'https://aedamarketplace.arcadier.io/'){
            var shopifyLink = $('ul.login-nav.dropdown-menu.hidden-xs li:first a');
	    window.location.href = $(shopifyLink).attr('href');
        }
        

        //merchant order list

        if (url.indexOf("/merchants/order/history") >= 0) {
         
            //append new header for sync
            $('.order-list-tit-sec ').append('<div class="order-status-sec">Shopify Sync</div>');


            waitForElement(".refund-icon", function ()
            {
                
                $("#order-list .order-un-read-box:not(.loadedstatus)").each(function ()
                {
             
                $(this).append(`<div class="order-status-sec">
                <button class="form-control shop-sync">Sync Order</button>
                </div>`);
                    
                $(this).addClass("loadedstatus");
                     
                })
             
            })

            //api template
            $('.order-data1 thead tr').append('<th class="order-status-sec">Shopify Sync</th>')

            waitForElement(".order-data1", function ()
            {
                $('.order-data1 tbody tr:not(.loadedstatus)').each(function ()
                {
                     $(this).append(`<td class="order-status-sec">
                    <button class="form-control shop-sync">Sync Order</button>
                    </td>`);
                    
                    $(this).addClass("loadedstatus");
                })
            })

            

            //get order details via order id https://{{your-marketplace}}.arcadier.io/api/v2/users/{{merchantID}}/orders/{{orderID}} ---? 
            //this endpoint do not get cf for cartitems, reference invoice id instead then validate the order id

            $('body').on('click', '.shop-sync', function ()
            {
                

                var orderList = REDUX_DATA.orderReducer.history.Records;
                var userId = REDUX_DATA.userReducer.user.ID;
                    
                    //var records = orderList.Orders.filter(x => x.PurchaseOrderNo == "PO125");

                

                const res = orderList.filter(x =>
                    x.Orders.some(y => y.PurchaseOrderNo == $(this).parents('.loadedstatus').find('td:first a').text())
                    
                    
                );
                console.table(res);
                console.log(res[0].InvoiceNo)
                console.log(res[0].Orders[0].ID)


                // const invoiceId = $(this).parents('.order-un-read-box').find('.invoice-number').text();
                // const orderId = $(this).parents('.order-un-read-box').attr('data-order-guid');
                
                syncOrderShopifyManual(res[0].Orders[0].ID, res[0].InvoiceNo,userId)

            })


             window.onscroll = function (ev){
              waitForElement(".refund-icon", function ()
                {
                
               // $('.order-list-tit-sec ').append('<div class="order-status-sec">Shopify Sync</div>');


                $("#order-list .order-un-read-box:not(.loadedstatus)").each(function ()
                {
             
                $(this).append(`<div class="order-status-sec">
                <button class="form-control shop-sync">Sync Order</button>
                </div>`);
                    
                $(this).addClass("loadedstatus");
                     
                })
             
            })
            };

              
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


    function syncOrderShopifyManual(orderId, invoiceId,userId)
    {
        
         var apiUrl = packagePath + '/sync_orders_manual.php';
        var data = {
            'invoice-id': invoiceId,
            'order-id': orderId,
             'user-id': userId

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


    function redirect(userId)
    {
    
        var plugin_url = `user/plugins/${packageId}/index.php?user=${userId}`; //`/user/plugins/${packageId}/index.php`
       
        var urls = `${baseUrl }/account/signintodomain?returnUrl=%2F${plugin_url}&code=`;
        
        //let trimHost = `${location.protocol}//${hostname.replace('fe.', "")}`;
        let apiUrl = `${baseUrl }/api/v2/users/${userId}/generate-login-code`;   
         
                $.ajax({
                        url: apiUrl,
                        data: {},
                        type: 'POST',
                            beforeSend: function (xhr)
                        {
                                xhr.setRequestHeader("Authorization", "Bearer " + token)
                                xhr.setRequestHeader("url", apiUrl);
                        },

                        success: function (response) {
                            if (response) {
                               // window.open(urls + response, '_blank');
                                openInNewTab(urls + response);
                                //alert(urls + response);
                            } else {
                               // toastr.error('Failed', 'Error');
                            }
                        },
                        error: function () {
                            //toastr.error('Failed', 'Error');
                        }
                    });


    }


    function openInNewTab(href) {
    Object.assign(document.createElement('a'), {
        target: '_blank',
        rel: 'noopener noreferrer',
        href: href,
    }).click();
    }
    function getCookie(name){
      var value = '; ' + document.cookie;
      var parts = value.split('; ' + name + '=');
      if (parts.length === 2) {
          return parts.pop().split(';').shift();
      }
    }
})();