(function(){
    var scriptSrc = document.currentScript.src;
    var packagePath = scriptSrc.replace('/scripts/scripts.js', '').trim();
    var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
    var packageId = re.exec(scriptSrc.toLowerCase())[1];

    $(document).ready(function(){
        var baseUrl = window.location.hostname;
        var token = getCookie('webapitoken');
        var user = $("#userGuid").val();
        /* if(($('#merchantId') && $('#merchantId').length)){
			//console.log('test');
			var a = document.createElement("a");
            a.href = "https://" + baseUrl + "/admin/plugins/" + packageId + "/index.php?user=" + user;
            a.innerHTML = "Magento";
            a.target = "_blank";

            var b = document.createElement("li");
                b.appendChild(a);

            var c = document.querySelector("ul.login-nav");
                c.insertBefore(b, document.querySelector("ul.login-nav li:nth-child(1)"));
        }  */
    });

    function getCookie(name){
        var value = '; ' + document.cookie;
        var parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
    }
})();