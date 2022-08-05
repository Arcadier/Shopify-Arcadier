(function(){
    var scriptSrc = document.currentScript.src;
    var packagePath = scriptSrc.replace('/scripts/scripts.js', '').trim();
    var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
    var packageId = re.exec(scriptSrc.toLowerCase())[1];

    $(document).ready(function(){
        //run on page load
        init();

        //enable plugin toggle
        $("#myonoffswitch").click(function(){
            console.log("click");
            var values = document.getElementById("myonoffswitch");
            if(values.checked){ 
                console.log(true)
                toggle_plugin(true);
            }
            else{
                console.log(false); 
                toggle_plugin(false);
            }
        });

        function toggle_plugin(toggle){
            var settings = {
                "url": "/admin/plugins/"+packageId+"/toggle_plugin.php",
                "method": "POST",
                "data": JSON.stringify({"toggle": toggle}),
                "async": false,
                "headers":{
                "Content-Type":"application/json"
                }
            };
        
            $.ajax(settings).done(function(response){
                console.log(JSON.parse(response));
            });
        }

        function init(){
            //check plugin CF
            var init_settings = {
                "url": "/admin/plugins/"+packageId+"/initialise.php",
                "method": "POST",
                "data": JSON.stringify({"toggle": true}),
                "async": false,
                "headers":{
                "Content-Type":"application/json"
                }
            };
        
            $.ajax(init_settings).done(function(response){
                //if response = null. Means the plugin was just installed
                if(response == null || response == ""){
                    var settings = {
                        "url": "/admin/plugins/"+packageId+"/check_custom_fields.php",
                        "method": "POST",
                        "data": JSON.stringify({"ONE":[{"ONE":"one"}], "TWO":[{"TWO": "two"}]}),
                        "async": false,
                        "headers":{
                        "Content-Type":"application/json"
                        }
                    };
                    $.ajax(settings).done(function(response){
                        var values = document.getElementById("myonoffswitch");
                        values.checked = false;
                    });
                }
                else{
                    response = JSON.parse(response);
                    //if response = "true". Means the plugin was turned on.
                    if(response == "true"){
                        var values = document.getElementById("myonoffswitch");
                        values.checked = true;
                    }
                    
                    //if response = "false". Means the plugin was toggled off.
                    if(response == "false"){
                        var values = document.getElementById("myonoffswitch");
                        values.checked = false;
                    }
                }
            });
        }
    })
})();