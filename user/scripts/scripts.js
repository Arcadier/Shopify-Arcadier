(function(){
    var scriptSrc = document.currentScript.src;
    var packagePath = scriptSrc.replace('/scripts/scripts.js', '').trim();
    var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
    var packageId = re.exec(scriptSrc.toLowerCase())[1];
    var userId = $('#userGuid').val();

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
            //	toastr.error('Error!');
            }
        });
    }
})();