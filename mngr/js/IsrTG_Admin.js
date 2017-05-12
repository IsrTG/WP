$.extend( true, $.fn.dataTable.defaults, {
    "searching": false,
    "ordering": false
} );

jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ? 
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}
 

$(document).ready(function()
{
	console.log("IsrTG Admin.js Loaded!");
    var eventLogtable_Id="#EventLogTable";
	
	var EventLogTableDom = $(eventLogtable_Id).dataTable({
        "order": [[ 2, "desc" ],[ 3, "desc" ]]
														});
														
	$(eventLogtable_Id+' tbody').on('click', 'tr', function () {
        var data = $(this).data();
        console.log(data);
    });
	
	
	//menu//
	var pathname = window.location.pathname.split( '/' );
	pathname=pathname[1];
	console.log(pathname);
	if (pathname !== 'admin')
		{
		$(".active").attr("class","");
		//$("a:regex(data, .*"+pathname+".*)").parent('li').attr("class","active");
		$('*[data="'+pathname+'"]').parent('li').attr("class","active");
		}
	
	$('#InnerMenu').find('a').click(function(e)
		{
		e.preventDefault();
		var url=$(this).attr('data');
		if (url)
			{
			var attributes=$(this).attr('urlAttr');
			if (attributes)
				{
				console.log(url+"?"+attributes);
				window.location.href = url+"?"+attributes;
				}
				else
				{
				console.log(url);
				window.location.href = url;
				}
			}
		});
	//end menu//
	
});
