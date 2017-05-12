<?php
/*
 * Template Name: isrtg_roster
 */
include "header.php";

$db = db_Connect();
	?>
<link rel="stylesheet" type="text/css" href="/mngr/css/IsrTG_Roster.css">

	<?php
	if (getFieldg("pid")=="" && getField("pid")=="")
	{
	$ttlAge = 0;
	$ttlMembers = 0;
		
	?>
<div id='IsrTG_Content'>
	<table id="IsrTG_Roster">
	<tr>
		<td>
		<div>
		<h3>הנהלה
<?php
$result = $db->query("SELECT mngr_Players.*,mngr_Ranks.Name AS 'RankName' FROM mngr_Players INNER JOIN mngr_Ranks ON mngr_Players.RankID = mngr_Ranks.RankID WHERE mngr_Players.RankID = 1 AND Status<2 ORDER BY mngr_Players.RankID,mngr_Players.JoinDate,mngr_Players.Nickname");
	print(" ($result->num_rows)</h3>");
	if ($result->num_rows == 0)
	{
		echo "<h4 class='Error'>לא קיימים מנהלים</h4>";
	}
	else
	{
?>
		<table id='Managment_Table'>
			<tr class='TableTitle'>
				<td class='TitleCell' style='width:40%;'>כינוי</td>
				<td class='TitleCell'>שם</td>
				<td class='TitleCell'>גיל</td>
				<td class='TitleCell'>סטטוס</td>
			</tr>
			
		<?php
		if ($result)
		{
			while ($row = $result->fetch_assoc())
			{
				$diff = date_diff(date_create(),date_create($row['BirthDate']));
				$tmp = explode(" ",$row['Name']);
				$tname = $tmp[0];
				if ($row["Status"]==PLAYER_STATUS_ACTIVE) $p_status = "פעיל";
				if ($row["Status"]==PLAYER_STATUS_VACATION) $p_status = "חופשה";
				$user_data = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>
						<a href='?pid={$row['JoomlaID']}'>{$row["Nickname"]}</a>
					</td>
					<td class='RowCell'>{$tname}</td>
					<td class='RowCell'>{$diff->format("%Y")}</td>
					<td class='RowCell'>{$p_status}</td>
				</tr>
IsrTG;
				echo $user_data;
				$ttlAge += $diff->format("%Y");
				$ttlMembers++;
			}
			$result->free();
			//print("$ttlAge<br/>");
		}
		?>
		</table>
<?php
	}
?>
	
		<h3>סגל
<?php
$result = $db->query("SELECT mngr_Players.*,mngr_Ranks.Name AS 'RankName' FROM mngr_Players INNER JOIN mngr_Ranks ON mngr_Players.RankID = mngr_Ranks.RankID WHERE mngr_Players.RankID = 2 AND Status<2 ORDER BY mngr_Players.RankID,mngr_Players.JoinDate,mngr_Players.Nickname");
	print(" ($result->num_rows)</h3>");
	if ($result->num_rows == 0)
	{
		echo "<h4 class='Error'>לא קיימים חברי סגל</h4>";
	}
	else
	{
?>
		<table id='Staff_Table'>
			<tr class='TableTitle'>
				<td class='TitleCell' style='width:40%;'>כינוי</td>
				<td class='TitleCell'>שם</td>
				<td class='TitleCell'>גיל</td>
				<td class='TitleCell'>תאריך הצטרפות</td>
				<td class='TitleCell'>סטטוס</td>
			</tr>
		<?php
		if ($result !== False)
		{
			unset($user_data);
			while ($row = $result->fetch_assoc())
			{
				$diff = date_diff(date_create(),date_create($row['BirthDate']));
				$tmp = @explode(" ",$row['Name']);
				$tname = $tmp[0];
				if ($row["Status"]==PLAYER_STATUS_ACTIVE) $p_status = "פעיל";
				if ($row["Status"]==PLAYER_STATUS_VACATION) $p_status = "חופשה";
				$user_data = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>
						<a href='?pid={$row['JoomlaID']}'>{$row['Nickname']}</a>
					</td>
					<td class='RowCell'>{$tname}</td>
					<td class='RowCell'>{$diff->format("%Y")}</td>
					<td class='RowCell'>
IsrTG;
					$user_data .= displayDate($row["JoinDate"]);
$user_data .= <<<IsrTG
					</td>
					<td class='RowCell'>{$p_status}</td>
				</tr>
IsrTG;
				echo $user_data; //print Row data
				
				$ttlAge += $diff->format("%Y");
				$ttlMembers++;
			}
		}
		$result->free();
		//print("$ttlAge<br/>");
		?>
		</table>
<?php
	}
?>
	
		<h3>חברי קלאן ותיקים
<?php
$result = $db->query("SELECT mngr_Players.*,mngr_Ranks.Name AS 'RankName' FROM mngr_Players INNER JOIN mngr_Ranks ON mngr_Players.RankID = mngr_Ranks.RankID WHERE mngr_Players.RankID=3 AND Status<2 ORDER BY mngr_Players.RankID,mngr_Players.JoinDate,mngr_Players.Nickname");
	print(" ($result->num_rows)</h3>");
	if ($result->num_rows == 0)
	{
		echo "<h4 class='Warning'>לא קיימים חברי קלאן ותיקים - כולם פה צ'ונגים</h4>";
	}
	else
	{
?>
		<table id='Senior_Members_Table'>
			<tr class='TableTitle'>
				<td class='TitleCell' style='width:40%;'>כינוי</td>
				<td class='TitleCell'>שם</td>
				<td class='TitleCell'>גיל</td>
				<td class='TitleCell'>תאריך הצטרפות</td>
				<td class='TitleCell'>סטטוס</td>
			</tr>
		<?php
		if ($result !== False)
		{
			while ($row = $result->fetch_assoc())
			{
				$diff = date_diff(date_create(),date_create($row['BirthDate']));
				$tmp = explode(" ",$row['Name']);
				$tname = $tmp[0];
				if ($row["Status"]==PLAYER_STATUS_ACTIVE) $p_status = "פעיל";
				if ($row["Status"]==PLAYER_STATUS_VACATION) $p_status = "חופשה";
				$user_data = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>
						<a href='?pid={$row['JoomlaID']}'>{$row['Nickname']}</a>
					</td>
					<td class='RowCell'>{$tname}</td>
					<td class='RowCell'>{$diff->format("%Y")}</td>
					<td class='RowCell'>
IsrTG;
				$user_data .= displayDate($row["JoinDate"]);

				$user_data .= <<<IsrTG
					</td>
					<td class='RowCell'>{$p_status}</td>
				</tr>
IsrTG;
				echo $user_data; //print Row data
				
				$ttlAge += $diff->format("%Y");
				$ttlMembers++;
			}
		}
		
		$result->free();
		?>
		</table>
<?php
	}
?>
	
		<h3>חברי קלאן
<?php
$result = $db->query("SELECT mngr_Players.*,mngr_Ranks.Name AS 'RankName' FROM mngr_Players INNER JOIN mngr_Ranks ON mngr_Players.RankID = mngr_Ranks.RankID WHERE mngr_Players.RankID = 4 AND Status<2 ORDER BY mngr_Players.RankID,mngr_Players.JoinDate,mngr_Players.Nickname");
	print(" ($result->num_rows)</h3>");
	if ($result->num_rows == 0)
	{
		echo "<h4 class='Warning'>לא קיימים חברי קלאן</h4>";
	}
	else
	{
?>
		<table id='Members_Table'>
			<tr class='TableTitle'>
				<td class='TitleCell' style='width:40%;'>כינוי</td>
				<td class='TitleCell'>שם</td>
				<td class='TitleCell'>גיל</td>
				<td class='TitleCell'>תאריך הצטרפות</td>
				<td class='TitleCell'>סטטוס</td>
			</tr>
		<?php
		if ($result !== False)
		{
			while ($row = $result->fetch_assoc())
			{
				$diff = date_diff(date_create(),date_create($row['BirthDate']));
				$tmp = explode(" ",$row['Name']);
				$tname = $tmp[0];
				if ($row["Status"]==PLAYER_STATUS_ACTIVE) $p_status = "פעיל";
				if ($row["Status"]==PLAYER_STATUS_VACATION) $p_status = "חופשה";
				$user_data = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>
						<a href='?pid={$row['JoomlaID']}'>{$row['Nickname']}</a>
					</td>
					<td class='RowCell'>{$tname}</td>
					<td class='RowCell'>{$diff->format("%Y")}</td>
					<td class='RowCell'>
IsrTG;
					$user_data .= displayDate($row["JoinDate"]);
				$user_data .= <<<IsrTG
					</td>
					<td class='RowCell'>{$p_status}</td>
				</tr>
IsrTG;
				echo $user_data; //print Row data
				
				$ttlAge += $diff->format("%Y");
				$ttlMembers++;
			}
		}
		
		$result->free();
		//print("$ttlAge<br/>");
		?>
		</table>
<?php
	}
?>
	
		<h3>מגויסים
	<?php
	$result = $db->query("SELECT mngr_Players.*,mngr_Ranks.Name AS 'RankName' FROM mngr_Players INNER JOIN mngr_Ranks ON mngr_Players.RankID = mngr_Ranks.RankID WHERE mngr_Players.RankID = 5 AND Status<2 ORDER BY mngr_Players.RankID,mngr_Players.JoinDate,mngr_Players.Nickname");
	print(" ($result->num_rows)</h3>");
	if ($result->num_rows == 0)
	{
		echo "<h4 class='Warning'>אין מגוייסים חדשים</h4>";
	}
	else
	{
	?>
		<table id='Recruits_Table'>
			<tr>
				<td class='TitleCell' style='width:40%;'>כינוי</td>
				<td class='TitleCell'>שם</td>
				<td class='TitleCell'>גיל</td>
				<td class='TitleCell'>תאריך הצטרפות</td>
				<td class='TitleCell'>סטטוס</td>
			</tr>
		<?php
		if ($result !== false)
		{
			while ($row = $result->fetch_assoc())
			{
				$diff = date_diff(date_create(),date_create($row['BirthDate']));
				$tmp = explode(" ",$row['Name']);
				$tname = $tmp[0];
				if ($row["Status"]==PLAYER_STATUS_ACTIVE) $p_status = "פעיל";
				if ($row["Status"]==PLAYER_STATUS_VACATION) $p_status = "חופשה";
				$user_data = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>
						<a href='?pid={$row['JoomlaID']}'>{$row['Nickname']}</a>
					</td>
					<td class='RowCell'>{$tname}</td>
					<td class='RowCell'>{$diff->format("%Y")}</td>
					<td class='RowCell'>
IsrTG;
					$user_data .= displayDate($row["JoinDate"]);
$user_data .= <<<IsrTG
					</td>
					<td class='RowCell'>{$p_status}</td>
				</tr>
IsrTG;
				echo $user_data; //print Row data
				
				$ttlAge += $diff->format("%Y");
				$ttlMembers++;
			}
		}
		
		$result->free();
		//print("$ttlAge<br/>");
		?>
		</table>
	<?php
	}
	$avrg = round($ttlAge / $ttlMembers);
	?>
	</div>
	
			<p style='font-weight:bold;text-align:center;'>גיל שחקנים ממוצע: <?php print $avrg ?></p>
			<p style='font-weight:bold;text-align:center;'>סה"כ שחקנים: <?php print $ttlMembers ?></p>
			
		</div>
		
		</td>
	</tr>
	</table>
	<?php
	
	} 
	else
	{
		$pid = getFieldg("pid");
		if ($pid=="") $pid = getField("pid");
		$pid = mysqli_real_escape_string($db,$pid);
		
		if (getField("mode")=="vac" && $pid == fm_getSessionID() && fm_getSessionID()>0)
		{
			if (GetValue("Status","mngr_Players","JoomlaID=".$pid)==0)
			{
				$db->query("UPDATE mngr_Players SET Status=1 WHERE JoomlaID=".$pid);
				addlog($pid,"נכנס למצב חופשה");
			} else {
				$db->query("UPDATE mngr_Players SET Status=0 WHERE JoomlaID=".$pid);
				addlog($pid,"יצא ממצב חופשה");
			}
		}
		
		if (getField("mode")=="settings" && $pid == fm_getSessionID())
		{
			if (getField("notifyemail")=="1")
			{
				$db->query("UPDATE mngr_Players SET NotifyEmail=1 WHERE JoomlaID=".$pid);
			} else {
				$db->query("UPDATE mngr_Players SET NotifyEmail=0 WHERE JoomlaID=".$pid);
			}
			addlog($pid,"עדכן את הגדרות החשבון שלו");
		}
		
		if ($result = $db->query("SELECT mngr_Players.*,mngr_Ranks.Name AS 'RankName' FROM mngr_Players INNER JOIN mngr_Ranks ON mngr_Players.RankID = mngr_Ranks.RankID WHERE JoomlaID=".$pid))
		{
			if ($result->num_rows==0)
			{
				print "Player not found!";
			}
			$row = $result->fetch_assoc();
			$myProfile = false;
			$managerProfile = false;
			if ($pid==fm_getSessionID()) $myProfile = true;
			if ($row["RankID"]==3) $managerProfile = true;
			
			/*
			if ($myProfile)
			{
				print "<h1 align=center><u>כרטיס השחקן שלי</u></h1><br>";
			} else {
				//print "<h1 align=center><u>כרטיס שחקן ".$row['Nickname']."</u></h1><br>";
			}
			*/

			$diff = date_diff(date_create(),date_create($row['BirthDate']));
			$age = $diff->format("%Y");
			
			if ($result->num_rows==0)
			{
				print "Player not found!";
			}
			else
			{
		?>
		
		<form method=post><input type=hidden name=mode value='vac'><input type=hidden name=pid value='<?php print $pid ?>'>
		<table>
			<tr>
				<td>
					<table id="userProfile">
						<tr class='TableRow'>
							<td class='RowCell'>דרגה</td>
							<td class='RowCell'><?php echo $row['RankName']?></td>
						</tr>
						<tr class='TableRow'>
							<td class='RowCell' style='width:15%;'>שם</td>
							<?php
							$tmp = explode(" ",$row['Name']);
							$tname = $tmp[0];
							print "<td class='RowCell'>".$tname."</td>";
							?>
						</tr>
						<tr class='TableRow'>
							<td class='RowCell'>גיל</td>
							<td class='RowCell'><?php echo $age?></td>
						</tr>
						<?php
						if ($row["RankID"]>3) 
							{
						?>
							<tr class='TableRow'>
								<td class='RowCell'>תאריך הצטרפות</td>
								<td class='RowCell'><?php echo displayDate($row['JoinDate'])?></td>
							</tr>
						<?php
							}
						?>
						<tr class='TableRow'>
							<td class='RowCell' style='width:15%;'>הכשרות</td>
							<td class='RowCell'>
							
			<?php
			if ($row['Qulifications']=="")
			{
				print "אין";
			} else {
				if ($result1 = $db->query("SELECT * FROM mngr_Quli"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						if (SearchInMulti($row['Qulifications'],$row1['QuliID']))
						{
							print $row1['Name']."<br>";
						}
					}
				}
				$result1->free();
			}
			?>
			
							</td>
						</tr>
						<tr class='TableRow'>
							<td class='RowCell'>עיטורים</td>
							<td class='RowCell'>
							
			<?php
			if ($row['Badges']=="")
			{
				print "אין";
			} else {
				if ($result1 = $db->query("SELECT * FROM mngr_Badges"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						if (SearchInMulti($row['Badges'],$row1['BadgeID']))
						{
							print "<b>".$row1['Name']."</b><br><img src='/mngr/images/badges/".$row1['BadgeID'].".".$row1['ImageExt']."' onmouseover=\"Tip('".toTip($row1['Description'])."')\" onmouseout=\"UnTip()\"><br><br>".PHP_EOL;
						}
					}
				}
				$result1->free();
			}
			?>
			
							</td>
						</tr>
						
		<?php
		if ($row['Naat']!="")
		{
			print "<tr class='TableRow'><td class='RowCell'>תפקידים נוספים בקלאן:</td><td class='RowCell'>".$row['Naat']."</td></tr>";
		}
		?>
						<tr class='TableRow'>
							<td class='RowCell'>סטטוס</td>
							<td class='RowCell'>
							
			<?php
			switch (playerStatus($row['Status']))
			{
				case "פעיל":
					if ($myProfile)
					{
						print "פעיל <input type=submit value='הכנס למצב חופשה'><br><br><span style='font-size:12px;'>* מצב חופשה גורם למערכת להתפקד בשמך לאירועים חדשים כ\"לא מגיע\".<br>יש להשתמש באופציה זו כאשר הינך יודע שלא תיהיה זמין להתפקד עצמאית.</font>";
					} else {
						print "פעיל";
					}
					break;
				case "חופשה":
					if ($myProfile)
					{
						print "חופשה <input type=submit value='צא ממצב חופשה'>";
					} else {
						print "חופשה";
					}
					break;
				case "לא פעיל":
					print "לא פעיל";
					break;
			}
			?>
							</td>
						</tr>
						<tr class='TableRow'>
							<td class='SecondTitle Center' colspan=2>נתוני תפקוד השחקן</td>
						</tr>
						<tr class='TableRow'>
							<td class='RowCell'>פעילות השחקן<br>(חצי שנה אחרונה)</td>
							<td class='RowCell'>
							
			<?php
				$last_ev = "";
				$curr_diff = "";
				$diff_sum = 0;
				$ttl_events = 0;
				$diff = 0;
				$sixmonthsAgo = date('Y-m-d', strtotime('-6 month'));
				if ($result1 = $db->query("SELECT mngr_EventsLog.*,mngr_Events.Type,mngr_Events.EventDate FROM mngr_EventsLog INNER JOIN mngr_Events ON mngr_EventsLog.EventID = mngr_Events.EventID WHERE Panelty=False AND (mngr_Events.Type='משימת שישי' OR mngr_Events.Type='אימון') AND JoomlaID=".$row['JoomlaID']." AND mngr_Events.EventDate>'".$sixmonthsAgo."' ORDER BY mngr_Events.EventDate"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						$ev_name = toTip(GetValue("Name","mngr_Events","EventID=".$row1['EventID']));
						$ev_date = dateObj(GetValue("EventDate","mngr_Events","EventID=".$row1['EventID']));
						//$tmp .= "<img src=/mngr/images/sword.png width=35 onmouseover=\"Tip('שם: ".$ev_name."<br>מועד: ".$ev_date."',WIDTH,200);\" onmouseout=\"UnTip()\"> ";
						if ($last_ev)
						{
							$diff_sum += $ev_date->diff($last_ev)->format("%a");
							$diff = $ev_date->diff($last_ev)->format("%a");
						}
						//print $ev_date->format("d/m/Y H:i")."<BR>".$diff."<BR><BR>";
						$last_ev = $ev_date;
						$ttl_events++;
					}
					$ttl_events -= 1;
					$result1->free();
					if ($ttl_events > 3)
					{
						$score = round(7 / ($diff_sum / $ttl_events) * 100);
						if ($score>100) $score = 100;
						if ($score<0) $score = 0;
						print $score."%";
					} else {
						print "לא ניתן לחשב";
					}
				}
			?>
			
							</td>
						</tr>
						<tr class='TableRow'>
							<td class='RowCell'>תפקידים מועדפים</td>
							<td class='RowCell'>
							
			<?php
			if ($result1 = $db->query("SELECT JoomlaID,DutyID,COUNT(DutyID) AS EventsNumber FROM mngr_EventsLog WHERE JoomlaID=".$row['JoomlaID']." AND Panelty=0 AND DutyID>0 GROUP BY DutyID ORDER BY EventsNumber DESC LIMIT 3"))
			{
				while ($row1 = $result1->fetch_assoc())
				{
					$dID = $row1["DutyID"];
					$dName = GetValue("Name","mngr_Dutys","DutyID=".$dID);
					print "&#8226; ".$dName." (".$row1["EventsNumber"]." משימות)<BR>";
				}
				$result1->free();
			}
			?>
			
							</td>
			
		<?php
		foreach ($eventTypes as $type) {
			$tmp = '';
			$ttl = 0;
			print "<tr class='TableRow'>";
			if ($result1 = $db->query("SELECT mngr_EventsLog.*,mngr_Events.Type,mngr_Events.EventDate FROM mngr_EventsLog INNER JOIN mngr_Events ON mngr_EventsLog.EventID = mngr_Events.EventID WHERE mngr_Events.Type='".$type."' AND JoomlaID=".$row['JoomlaID']." AND Panelty=False ORDER BY mngr_Events.EventDate"))
				{
				while ($row1 = $result1->fetch_assoc())
					{
					(isset($eventTypesIcons[$type]) & !empty($eventTypesIcons[$type]))	?	$icon=$eventTypesIcons[$type]:$icon=$eventTypesIcons['default']; //set default icon for unknow events
					$ev_name = toTip(GetValue("Name","mngr_Events","EventID=".$row1['EventID']));
					$ev_date = displayFullDate(GetValue("EventDate","mngr_Events","EventID=".$row1['EventID']));
					$ev_duty = GetValue("Name","mngr_Dutys","DutyID=".$row1['DutyID']);
					if ($ev_duty=="") $ev_duty = "לא תועד";
					$tmp .= "<a href='calendar-day?eid=".$row1['EventID']."'><img src='/mngr/images/".$icon."' width=35 onmouseover=\"Tip('שם: ".$ev_name."<br>תפקיד: ".$ev_duty."<br>מועד: ".$ev_date."',WIDTH,200);\" onmouseout=\"UnTip()\" border=0></a> ";
					$ttl++;
					}
				$result1->free();
				}
			print "<td class='RowCell'>".$type." (".$ttl.")</td>";
			print "<td class='RowCell'>".$tmp."</td>";
		}
		
		if ($managerProfile!=true) {
			print "<tr class='TableRow'>";
			$tmp = '';
			$ttl = 0;
			if ($result1 = $db->query("SELECT mngr_EventsLog.*,mngr_Events.Type,mngr_Events.EventDate FROM mngr_EventsLog INNER JOIN mngr_Events ON mngr_EventsLog.EventID = mngr_Events.EventID WHERE Panelty=True AND JoomlaID=".$row['JoomlaID']." ORDER BY mngr_Events.EventDate"))
			{
				while ($row1 = $result1->fetch_assoc())
				{
				$ev_name = toTip(GetValue("Name","mngr_Events","EventID=".$row1['EventID']));
				$ev_date = displayFullDate(GetValue("EventDate","mngr_Events","EventID=".$row1['EventID']));
				$ev_type = GetValue("Type","mngr_Events","EventID=".$row1['EventID']);
				$tmp .= "<a href='calendar-day?eid=".$row1['EventID']."'><img src=/mngr/images/warning.png width=35 onmouseover=\"Tip('שם: ".$ev_name."<br>מועד: ".$ev_date."',WIDTH,200)\" onmouseout=\"UnTip()\" border=0></a> ";
				$ttl++;
				}
				$result1->free();
			}
			print "<td class='RowCell'>הברזות (".$ttl.")</td>";
			print "<td class='RowCell'>".$tmp."</td>";
			print "</tr>";
		}
		?>

		</table>
		</form>
		
		<?php
		if ($myProfile)
		{
			$notify = "";
			if ($row["NotifyEmail"]) { $notify = " checked"; }
			print "<form method=post><input type=hidden name=mode value='settings'><input type=hidden name=pid value='".$pid."'><input type=checkbox name=notifyemail value='1'".$notify.">עדכן אותי באימייל כאשר ישנו אירוע חדש שאני מזומן אליו.";
			print "<br><br><input type=submit value='עדכן'></form>";
		}
		?>
		
		<br><br><p align=center><input type=button onClick="window.location.href='חברי-הקלאן/';" value="חזור"></p>
	<?php } } }?>
</div>
<?php 
	$db->close();
?>

<?php include "footer.php"; ?>