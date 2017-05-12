<?php
/*
 * Template Name: isrtg_admin
 */
include "admin_Menu.php";
include "header.php";
?>

<?php
	if (PageRank($perm_Admin_Segel,"אינך מורשה להיכנס לעמוד זה")) {

	$db = db_Connect();
	$perm_top_dis = '';
	$perm_top = PageRank($perm_Admin_Manager,"");
	if (!$perm_top) $perm_top_dis = ' disabled';
?>

<div id="IsrTG_Content">
<?php
print("$MenuToPrint");
?>
	
<div id="Main_Clan_Managment">
	<div class='Title'>
		<h2>משימות</h2>
	</div>
	<div class="MainContent">
		<?php
		//Check cron job and notify managers if has problems
		$dateObjNow = date_now();
		$lastCronJobDate = GetValue("LogDate","mngr_Logs","LogID=1");
		$lastCronJobDateObj = dateObj(GetValue("LogDate","mngr_Logs","LogID=1"));
		$nowHour = $dateObj;
		$interval = $dateObjNow->diff($lastCronJobDateObj);
		$intervalHours = $interval->h + ($interval->days * 24);
		$toPrint = "";
		if ($intervalHours > 2)
		{
			$lastCronJobDateShow=displayFullDate($lastCronJobDate);
			$toPrint .= <<<IsrTG
			<div>
				<span class="glyphicon glyphicon-warning-sign" aria-hidden="true" style='color:red'></span>
				&nbsp;
				<span style='color:red;font-weight:bold'>משימות מתוזמנות מאחר בביצוע (לאחרונה בוצע ב {$lastCronJobDateShow})</span>
			</div>
IsrTG;
		}
		
		//Find if there are dutys exists which does not have any quli that allow it
		$res = "";
		if ($result = $db->query("SELECT mngr_Dutys.*,mngr_Quli.QuliID FROM mngr_Dutys LEFT JOIN mngr_Quli ON FIND_IN_SET(mngr_Dutys.DutyID,mngr_Quli.Dutys) WHERE ISNULL(QuliID) AND DutyID NOT IN (".DUTIES_SET_ALLOWED_NO_QULI.")"))
		{
			while ($row = $result->fetch_assoc())
				{
				if ($res!="")
					{
					$res .= ", ";
					}
				$res .= $row["Name"];
				}
			$result->free();
			if ($res!="")
				{
				$toPrint .= <<<IsrTG
						<div class='Error'>
							<span class="glyphicon glyphicon-warning-sign" aria-hidden="true" style='color:red'></span>
							&nbsp;
							<span style='color:red;font-weight:bold'>לתפקידים הבאים אין אף הכשרה המאפשרת אותם: {$res}. דאג לתקנם בהקדם.</span>
						</div>
IsrTG;
				}
		}
		
		$dateObj_now = date_now();
		if ($result = $db->query("SELECT * FROM mngr_Events WHERE Status=0 AND EventDate<'".$dateObj_now->format("Y-m-d H:i:s")."' ORDER BY EventDate"))
		{
			while ($row = $result->fetch_assoc())
			{
			$event_date=displayFullDate($row['EventDate']);
			$toPrint .= <<<IsrTG
						<div>
							<span class="glyphicon glyphicon-check" aria-hidden="true" style='color:orange;'></span>
							&nbsp;
							<span>האירוע {$row['Name']} הסתיים אך טרם הוזן סיכום ({$event_date})</span>
						</div>
IsrTG;
				//print "<li> האירוע ".$row['Name']." הסתיים אך טרם הוזן סיכום (".$event_date.")";
			}
			$result->free();
		}
		
		if ($result = $db->query("SELECT * FROM mngr_Players WHERE Status=4 OR Status=5"))
		{
			if ($result->num_rows>0)
			{
			$toPrint .= <<<IsrTG
						<div>
							<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
							&nbsp;
							<span>ישנם {$result->num_rows} שחקנים הנמצאים בתהליכי הרשמה. דאג לקדמם.</span>
						</div>
IsrTG;
			}
			$result->free();
		}
		
		echo $toPrint;
		?>
	</div>
	
	
	<div id='EventLog'>
		<div class='Title'>
			<h2 align=center>לוג פעילות</h2>
		</div>
		<div class="MainContent">
			<table id='EventLogTable' class='table table-striped table-bordered dataTable'>
				<thead>
					<tr>
						<th style="font-weight:bold;text-align:center;">מבצע הפעולה</th>
						<th style="font-weight:bold;text-align:center;">פעולה</th>
						<th style="font-weight:bold;text-align:center;">תאריך</th>
						<th style="font-weight:bold;text-align:center;">שעה</th>
					</tr>
				</thead>
				<tbody>
				<?php
				if ($result = $db->query("SELECT * FROM mngr_Logs WHERE Log NOT LIKE 'לוג אירוע:%' ORDER BY LogDate DESC LIMIT 100"))
					{
					while ($row = $result->fetch_assoc())
						{
						if ($row["JoomlaID"]>0) $tname = PlayerName($row["JoomlaID"]);
						else $tname = "-";
						
						print "<tr><td style='text-align:center;'>{$tname}</td><td style='text-align:center;'>{$row["Log"]}</td><td style='text-align:center;'>".displayFullDate($row["LogDate"],'Y/m/d')."</td><td style='text-align:center;'>".displayFullDate($row["LogDate"],'H:i')."</td></tr>";
						}
					$result->free();
					}
				?>
				</tbody>
			</table>
		</div>
	</div>
</div>


<?php 
$db->close();
} 
?>

<?php include "footer.php"; ?>