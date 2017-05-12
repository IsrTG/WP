<script src="//code.jquery.com/jquery-1.12.4.js"></script>
<script src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>

<link href="/mngr/css/IsrTG_General.css" rel="stylesheet" type="text/css"/>
<link href="/mngr/css/IsrTG_Admin.css" rel="stylesheet" type="text/css"/>
<script src="/mngr/js/IsrTG_Admin.js" type="text/javascript"></script>

<!-- IsrTG Server Time Synq with local seconds tics - in order to prevent every second ajax call for php synq !-->
	<?php
	$obj_now = new DateTime();

	// Get offset for UNIX timestamp based on server timezone
	$time = $obj_now->getTimestamp();

	//Get offset of server time
	$offset = $obj_now->getOffset();

	// Convert into a new timestamp based on the timezone
	$newtime = ($time + $offset) * 1000;
	?>
	
	<script>
var serverTime = <?php print("$newtime"); ?>;
var localTime = new Date();
// Offset between the computer and the server
var timeDiff = serverTime - localTime;
// The ticking clock function
setInterval(function () {
  var today = new Date(Date.now() + timeDiff);
  var h=today.getUTCHours();
  var m=today.getUTCMinutes();
  var s=today.getUTCSeconds();
  h = checkTime(h);
  m = checkTime(m);
  s = checkTime(s);
  var formatted = h+":"+m+":"+s;
  document.querySelector(".serverTime").innerHTML = formatted;
}, 1000);

// Helper function for leading 0's
function checkTime(i) {
  if (i<10) {i = "0" + i};  // add zero in front of numbers < 10
  return i;
}
	</script>
	<?php
	
$url='';
$MenuToPrint = <<<IsrTG
		<div id='SystemTime' class='RoundCorners'>
			<span class='glyphicon glyphicon-time' aria-hidden='true'></span>
			<span class='Title'>שעון מערכת:</span>
			&nbsp;
			<span class='serverTime'>{$obj_now->format("H:i:s")}</span>
			&nbsp;
			<span class='serverDate'>{$obj_now->format("d/m/Y")}</span>
		</div>
		
		<!-- End IsrTG Server Time -->

		<div id='Mngr_Menu'>
			<nav class="navbar navbar-default">
				<div class="container-fluid">
				<!-- Brand and toggle get grouped for better mobile display -->

				<!-- Collect the nav links, forms, and other content for toggling -->
					<div id="InnerMenu">
						<ul class="nav navbar-nav navbar-right">
								<li>
									<a href='#' data="admin-dutys" class="navbar-link"><span class='glyphicon glyphicon-tag' aria-hidden='true'></span>&nbsp;ניהול תפקידים</a>
								</li>
								<li>
									<a href='#' data="admin-badges" class="navbar-link"><span class='glyphicon glyphicon-star-empty' aria-hidden='true'></span>&nbsp;ניהול עיטורים</a>
								</li>
								<li>
									<a href='#' data="admin-qualifications" class="navbar-link"><span class='glyphicon glyphicon-blackboard' aria-hidden='true'></span>&nbsp;ניהול הכשרות</a>
								</li>
								<li>
									<a href='#' data="" class="navbar-link disable"><span class='glyphicon glyphicon-education' aria-hidden='true'></span>&nbsp;ניהול מדריכים</a>
								</li>
								<li>
									<a href='#' data="admin-events" class="navbar-link"><span class='glyphicon glyphicon-calendar' aria-hidden='true'></span>&nbsp;ניהול אירועים</a>
								</li>
								<li>
									<a href='#' data="admin-players" class="navbar-link"><span class='glyphicon glyphicon-user' aria-hidden='true'></span>&nbsp;ניהול שחקנים</a>
								</li>
								<li>
									<a href='#' data="admin-arma" urlAttr="mode=dutysItem" class="navbar-link"><span class='glyphicon glyphicon-king' aria-hidden='true'></span>&nbsp;ניהול שרת משחק</a>
								</li>
								<li>
									<a href='#' data="admin-general" class="navbar-link"><span class='glyphicon glyphicon-cog' aria-hidden='true'></span>&nbsp;ניהול כללי</a>
								</li>
								<li class='active'>
									<a href='#' data="admin" class="navbar-link"><span class='glyphicon glyphicon-certificate' aria-hidden='true'></span>&nbsp;ראשי</a>
								</li>
						</ul>
					</div>
				</div>
			</nav>
		</div>
IsrTG;
