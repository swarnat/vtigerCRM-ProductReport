function filterProduct(button, module) {

    if (jQuery("#Wf2ListViewPOPUP").length == 0)
    {
        var div = document.createElement('div');
        div.setAttribute('id','Wf2ListViewPOPUP');
        div.setAttribute('style','display:none;width:350px; position:absolute;');
        div.innerHTML = 'Loading';
        document.body.appendChild(div);

        //      for IE7 compatiblity we can not use setAttribute('style', <val>) as well as setAttribute('class', <val>)
        newdiv = document.getElementById('Wf2ListViewPOPUP');
        newdiv.style.display = 'none';
        newdiv.style.width = '350px';
        newdiv.style.position = 'absolute';
    }

    jQuery.post("index.php", {
        "module" : "ProductReport",
        "action" : "ProductReportAjax",
        "file"   : "ListViewPopup",

        "return_module" : module
    }, function(response) {
        jQuery("#Wf2ListViewPOPUP").html(response);

        fnvshobj(button,'Wf2ListViewPOPUP');
    });

}
function startFilter() {
    var productID = jQuery("#filterProductSelector").val();
    var dateFrom = jQuery("#pr_date_from").val();
    var dateTo = jQuery("#pr_date_to").val();

    jQuery.post("index.php?module=ProductReport&action=ProductReportAjax&file=setproductfilter", {
            productid: productID,
            date_from: dateFrom,
            date_to: dateTo
        }, function() {
            showDefaultCustomView(document.getElementById("viewname"), document.getElementById("curmodule").value, "");
            jQuery("#Wf2ListViewPOPUP").hide();
        });
}
function clearFilter() {
    jQuery("#filterProductSelector").val("");
    startFilter();
}