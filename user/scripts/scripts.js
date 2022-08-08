(function(){
    var scriptSrc = document.currentScript.src;
    var packagePath = scriptSrc.replace('/scripts/scripts.js', '').trim();
    var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
    var packageId = re.exec(scriptSrc.toLowerCase())[1];

    $(document).ready(function(){
        var baseUrl = window.location.hostname;
        var token = getCookie('webapitoken');
        var user = $("#userGuid").val();
        if(($('#merchantId') && $('#merchantId').length)){
			//console.log('test');
			var a = document.createElement("a");
            a.href = "https://" + baseUrl + "/user/plugins/" + packageId + "/index.php?user=" + user;
            a.innerHTML = "Tanoo_Test";
            a.target = "_blank";

            var b = document.createElement("li");
                b.appendChild(a);

            var c = document.querySelector("ul.login-nav");
                c.insertBefore(b, document.querySelector("ul.login-nav li:nth-child(1)"));
        } 
    });

    function getCookie(name){
        var value = '; ' + document.cookie;
        var parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
    }

    
      function saveShopifyData(){
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



    });




})();