$(document).ready(function()
{
console.log("IsrTG General Jquery Loaded!\n");
$("#showHideImg").click(function()
				{
				console.log("showHideImg Clicked!\n");
	var current_status = parseInt($(this).attr('data-role'));
	console.log(current_status+"\n");
	switch(current_status)
		{
		case 0: //current hide - need to show
				console.log("Current hide - need to show\n");
				$(this).attr("data-role", "1");
				$('img', this).attr({
									src:"/mngr/images/minus.png",
									alt:"לחץ כאן על מנת לצמצם את ההתפקדות",
									title:"לחץ כאן על מנת לצמצם את ההתפקדות"
									});
				break;
		case 1: //current show - need to hide
				$(this).attr("data-role","0");
				console.log("Current show - need to hide\n");
				$('img', this).attr({
									src:"/mngr/images/plus.png",
									title:"לחץ כאן על מנת להרחיב את ההתפקדות",
									alt:"לחץ כאן על מנת להרחיב את ההתפקדות"
									});
				$(this).css({
							
							});
				break;
		}
	
	$("#Hitpakdut #AttendanceShowDiv").slideToggle(300);		
				}
	);
});