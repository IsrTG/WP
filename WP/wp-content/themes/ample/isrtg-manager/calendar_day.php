<?php
/*
 * Template Name: isrtg_calendar_day
 */
require "header.php";
?>

<link rel="stylesheet" type="text/css" href="/mngr/css/IsrTG_Calendar.css">
<script src="/mngr/js/IsrTG_Calendar.js" type="text/javascript"></script>


<?php
	$db = db_Connect();
	print("<div id='IsrTG_Content'>");
	if (getFieldg("eid",$db)=="" && getField("eid",$db)=="") 
	{
		$currDay = (int) getFieldg("tday",$db);
		$currMonth = (int) getFieldg("tmonth",$db);
		$currYear = (int) getFieldg("tyear",$db);
		
		print "<table id='calander_events_day_list'>";
		$to_print = <<<IsrTG
		<tr class='TableTitle'>
			<td class='TitleCell'>שם האירוע</td>
			<td class='TitleCell'>שעה</td>
			<td class='TitleCell'>סוג</td>
		</tr>
IsrTG;
		echo $to_print;
		if ($result = $db->query("SELECT * FROM mngr_Events WHERE DateDiff('".$currYear."-".$currMonth."-".$currDay."',EventDate)=0 ORDER BY EventDate"))
		{
			while ($row = $result->fetch_assoc())
			{
				unset($to_print);
				$to_print = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell' id='eventName'><a href='?eid={$row['EventID']}'>{$row['Name']}</a></td>
					<td class='RowCell'>
IsrTG;
				$to_print .= displayTime($row['EventDate']);
				
				$to_print .= <<<IsrTG
					</td>
					<td class='RowCell'>{$row['Type']}</td>
				</tr>
IsrTG;
				echo $to_print;
			}
			$result->free();
		}
		print "</table>";
	} 
	else {
		$eid = (int)getFieldg("eid",$db);
		$adminChange = 0;
		$updated  = '';
		if ($eid=='') $eid = getField("eid",$db);
		$status = getField("status",$db);
		$pid = getField("pid",$db);
		if (PageRank($perm_Admin_Segel,""))
		{
			if ($status=="" && getFieldg("status",$db)!="" && getFieldg("pid",$db)!="")
			{
				$status = getFieldg("status",$db);
				$pid = getFieldg("pid",$db);
				$adminChange = 1;
			}
		}
		
		if ($eid!='' && $status!='') //Player status submitted
		{
			if ($result = $db->query("SELECT * FROM mngr_Events WHERE EventID=".$eid))
			{
				$row = $result->fetch_assoc();
				$players_yes = $row['PlayersAccept'];
				$players_maybe = $row['PlayersMaybe'];
				$players_no = $row['PlayersDecline'];
				$players_un_print = '';
				$e_name = GetValue("Name","mngr_Events","EventID=".$eid);
				$e_date = DisplayFullDate(GetValue("EventDate","mngr_Events","EventID=".$eid));
				$players_yes = RemoveFromMulti($pid,$players_yes);
				$players_maybe = RemoveFromMulti($pid,$players_maybe);
				$players_no = RemoveFromMulti($pid,$players_no);
				if ($status=="3" && $row["CountMethod"]=="dutys") $status="4";
				switch ($status) {
					case "1": //decline
						if ($players_no!='') $players_no .= ",";
						$players_no .= $pid;
						addlog($pid,"לוג אירוע:".$eid.":התפקד כ\"לא מגיע\"");
						break;
					case "2": //maybe
						if ($players_maybe!='') $players_maybe .= ",";
						$players_maybe .= $pid;
						addlog($pid,"לוג אירוע:".$eid.":התפקד כ\"אולי\"");
						break;
					case "3": //approve
					case "4": //approve, no specific duty
						if ($players_yes!='') $players_yes .= ",";
						$players_yes .= $pid;
						addlog($pid,"לוג אירוע:".$eid.":התפקד כ\"מגיע\"");
						break;
				}
				print $status."-".$players_yes."-".$pid;
				if ($row["CountMethod"]=="dutys")
                {
                    if ($status!="1" && $status != "2" && $status != "4")
                    {
                        if ($players_yes!='') $players_yes .= ",";
                        $players_yes .= $pid;
                        addlog($pid,"לוג אירוע:".$eid.":בחר תפקיד");
                    }
                    $infantryStruct = unserialize($row["DutyStruct"]);
                    changePlayerHitpakdut($infantryStruct,$pid,$status,$adminChange);
                }
                print "<BR>".$status."-".$players_yes."-".$pid;
				$db->query("UPDATE mngr_Events SET PlayersAccept='".$players_yes."', PlayersMaybe='".$players_maybe."', PlayersDecline='".$players_no."', DutyStruct='".serialize($infantryStruct)."' WHERE EventID=".$eid);
				
				$updated = 1;
				if ($adminChange==1)
                {
                    header("Location: admin-events?eventid=".$eid."#more");
                }
                else
                {
                    header("Location: calendar-day?eid=".$eid."");
                }
			}
		}
		
		if ($result = $db->query("SELECT * FROM mngr_Events WHERE EventID=".$eid))
		{
			if ($result->num_rows==0)
			{
				print "האירוע לא נמצא.";
			} 
			else 
			{
			$row = $result->fetch_assoc();
			
			if ($updated==1) print "<div class='Approve'>הנוכחות עודכנה בהצלחה!</div>";
			
			//event details//
			$to_print = <<<IsrTG
			<table id="calendar_event_details">
				<tr class='TableRow'>
					<td class='RowCell'>מועד:</td>
					<td class='RowCell'>
IsrTG;
					$to_print .= displayFullDate($row['EventDate']);
				
					$to_print .= <<<IsrTG
					</td>
				</tr>
				<tr class='TableRow'>
					<td class='RowCell'>סוג האירוע:</td>
					<td class='RowCell'>{$row['Type']}</td>
				</tr>
IsrTG;

			echo $to_print;
			if (file_exists(SERVER_PATH."/mngr/briefings/".$eid.".pdf")) print "<tr class='TableRow'><td class='RowCell'>תדריך:</td><td class='RowCell' style='text-align:right;'><span id='briefing'><a href='/mngr/briefings/".$eid.".pdf' target=_blank>http://www.isrtg.com/mngr/briefings/".$eid.".pdf</a></span></td></tr>";
			if ($row['Notes']) print "<tr class='TableRow'><td class='RowCell'>הערות:</td><td class='RowCell'>".fromdbprint($row['Notes'])."</td></tr>";
			$wanted = MultiToArray(EventWantedPlayers($eid));
			
			$tmp = '';
			
			if ($row['Status']==0)
			{
				$player_yes = '';
				$player_maybe = '';
				$player_no = '';
				$disable_training_only = false;
				$disable_time = false;
				$disable_notInvited = false;
				if (SearchInMulti($row['PlayersAccept'],fm_getSessionID())) $player_yes = ' checked';
				if (SearchInMulti($row['PlayersMaybe'],fm_getSessionID())) $player_maybe = ' checked';
				if (SearchInMulti($row['PlayersDecline'],fm_getSessionID())) $player_no = ' checked';
				
				$to_print = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>התפקדות:</td>
					<td class='RowCell'>
IsrTG;
				
				$dateObj_now = date_now();
				$tdate = DateTime::createFromFormat("Y-m-d H:i:s", $row['EventDate']);
				$tdate->modify('-1 days');
				$tdate->setTime(20,0,0);
				$p_rank = GetValue("RankID","mngr_Players","JoomlaID=".fm_getSessionID());
				if ($row["CountMethod"]=="dutys")
                {
                 $infantryStruct = unserialize($row["DutyStruct"]);
                }
				if ($p_rank < 5 || $row['Type']=="אימון") //Meguyas can mark presence to trainings only
                {
                    if ($dateObj_now>$tdate)
                    {
                        $disable_time = true;
                    }
                    if (!SearchInArray($wanted,fm_getSessionID())) //player is invited so let him lehitpaked
                    {
                        $disable_notInvited = true;
                    }
                }
                else
                {
                    $disable_training_only = true;
                }
				
				$hitpakdut_disable = '';
				if ($disable_training_only || $disable_time || $disable_notInvited) $hitpakdut_disable = ' disabled';
				
				if ($rs_duty = $db->query("SELECT * FROM mngr_Dutys ORDER BY DutyID"))
				{
					$dutysArr = array();
					while ($rw_duty = $rs_duty->fetch_assoc())
					{
					    $dutysArr[$rw_duty["DutyID"]] = $rw_duty["Name"];
					}
				}
				$rs_duty->free();
				
				if ($disable_training_only) { $to_print .= "<div class='Warning'><span>אינך יכול לעדכן נוכחות לאירוע זה.</span><Br/><span>הינך רשאי להגיע לאימונים בלבד.</span></div>"; }
				else if ($disable_notInvited) { $to_print .= "<div class='Warning'><span>אינך יכול לעדכן נוכחות לאירוע זה.</span><Br/><span>מפני שאינך מוזמן לאירוע.</span></div>"; }
				else if ($disable_time) { $to_print .= "<div class='Error'><span>זמן ההתפקדות לאירוע עבר, כעת ניתן לצפות בלבד.</span></div>"; }
				
				//event attendance//
				$to_print .= <<<IsrTG
				<div id='Hitpakdut'>
					<div id='showHideHitpakdut'>
						<span id='showHideText' class='Bold Center'>לוח התפקדות</span>
						<span id='showHideImg' data-role="1">(כווץ/הרחב תפריט)&nbsp;&nbsp;<img src='/mngr/images/minus.png' style="cursor:pointer;width:20px;height:20px;"></span>
					</div>
					
					<form method=post id='updateStatus'>
IsrTG;
					$to_print .= "<input type=hidden name=pid value='".fm_getSessionID()."'><input type=hidden name=eid value='".$eid."'>";
					if ($row["CountMethod"]=="dutys")
						{
							$to_print .= "<div id='AttendanceShowDiv'>".printPlayerDutysTable($infantryStruct,$dutysArr,$hitpakdut_disable!='')."</div>";
							$to_print .= "<input type=radio name=status value=4 onClick='updateStatus.submit();'".((SearchInMulti($row['PlayersAccept'],fm_getSessionID()) && !playerSubmittedToDuty($infantryStruct,fm_getSessionID()))?" checked":"").$hitpakdut_disable."> מגיע, אין עדיפות לתפקיד<br/>";
						} 
						else
						{
							$to_print .= "<input type=radio name=status value=3".$player_yes." onClick='updateStatus.submit();'".$hitpakdut_disable."> מגיע<br>";
						}
					$to_print .= <<<IsrTG
					<input type=radio name=status value=2{$player_maybe} onClick='updateStatus.submit();'{$hitpakdut_disable}> אולי<br>
					<input type=radio name=status value=1{$player_no} onClick='updateStatus.submit();'{$hitpakdut_disable}> לא מגיע
					</form>
				</div>
IsrTG;

				if (!$disable_time && !$disable_notInvited && !$disable_training_only)
					{
					$to_print .= <<<IsrTG
					<br>
					<div class='Warning'>
						שים לב: ניתן לעדכן נוכחות עד תאריך {$tdate->format("d/m/Y")} בשעה {$tdate->format("H:i")}
						<br><br>
					</div>
IsrTG;
					}

				$to_print .= <<<IsrTG
				</td>
				</tr>
IsrTG;
				echo $to_print; //print hitpakdut div
				
				$players_yes_print = '';
				$players_yes_count = 0;
				if ($row['PlayersAccept'])
				{
					$players_yes = MultiToArray($row['PlayersAccept']);
					for ($i=0;$i<count($players_yes);$i++)
					{
						if (SearchInArray($wanted,$players_yes[$i]))
						{
							$players_yes_print .= "<img src=/mngr/images/yes.png width=20 height=20> ".PlayerName($players_yes[$i],true);
							if ($row['CountMethod']=="dutys" && !playerSubmittedToDuty($infantryStruct,$players_yes[$i])) $players_yes_print .= " - ללא עדיפות לתפקיד";
							$players_yes_print .= "<br>";
							$players_yes_count++;
						}
					}
				}
				if ($players_yes_count == 0)
					{
					$players_yes_print = 'אין';
					$Span_class='Error';
					}
					else
					{
					$Span_class='attendance';
					}
				$to_print = <<<IsrTG
							<tr class='TableRow'>
								<td class='RowCell'>שחקנים שאישרו הגעה ({$players_yes_count}):</td>
								<td class='RowCell'><span class='{$Span_class}'>{$players_yes_print}</span></td>
							</tr>
IsrTG;
				echo $to_print;
			
				$players_maybe_print = '';
				$players_maybe_count = 0;
				if ($row['PlayersMaybe'])
				{
					$players_maybe = MultiToArray($row['PlayersMaybe']);
					for ($i=0;$i<count($players_maybe);$i++)
					{
						if (SearchInArray($wanted,$players_maybe[$i]))
						{
							$players_maybe_print .= "<img src=/mngr/images/maybe.png width=20 height=20> ".PlayerName($players_maybe[$i],true)."<br>";
							$players_maybe_count++;
						}
					}
				}
				if ($players_maybe_count == 0)
					{
					$players_maybe_print = 'אין';
					$Span_class='Approve';
					}
					else
					{
					$Span_class='attendance';
					}
				$to_print = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>שחקנים שאולי יגיעו ({$players_maybe_count}):</td>
					<td class='RowCell'><span class='{$Span_class}'>{$players_maybe_print}</span></td>
				</tr>
IsrTG;

				$players_no_print = '';
				$players_no_count = 0;
				if ($row['PlayersDecline'])
				{
					$players_no = MultiToArray($row['PlayersDecline']);
					for ($i=0;$i<count($players_no);$i++)
					{
						if (SearchInArray($wanted,$players_no[$i]))
						{
							$players_no_print .= "<img src=/mngr/images/no.png width=20 height=20> ".PlayerName($players_no[$i],true)."<br>";
							$players_no_count++;
						}
					}
				}
				if ($players_no_count == 0)
					{
					$players_no_print = 'אין';
					$Span_class='Approve';
					}
					else
					{
					$Span_class='attendance';
					}
				echo $to_print;
				
				$to_print = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>שחקנים שלא אישרו הגעה ({$players_no_count}):</td>
					<td class='RowCell'><span class='{$Span_class}'>{$players_no_print}</span></td>
				</tr>
IsrTG;

				$players_un_print = '';
				$players_un_count = 0;
				$tmp = '';
				//loop through all invited
				for ($i=0;$i<count($wanted);$i++)
				{
					if (SearchInMulti($row['PlayersAccept'],$wanted[$i])==False && SearchInMulti($row['PlayersDecline'],$wanted[$i])==False && SearchInMulti($row['PlayersMaybe'],$wanted[$i])==False)
					{
						$players_un_print .= "<img src=/mngr/images/clock.png width=20 height=20> ".PlayerName($wanted[$i],true)."<br>";
						$players_un_count++;
					}
				}
				if ($players_un_count == 0)
					{
					$players_un_print = 'אין';
					$Span_class='Approve';
					}
					else
					{
					$Span_class='attendance';
					}
				echo $to_print;
				
				$to_print = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>שחקנים שטרם הגיבו ({$players_un_count}):</td>
					<td class='RowCell'><span class='{$Span_class}'>{$players_un_print}</span></td>
				</tr>
IsrTG;

				echo $to_print;
				
				if (PageRank($perm_Admin_Segel,"")) 
					{ //Show hitpakdut history for segel
					$result1 = $db->query("SELECT * FROM mngr_Logs WHERE Log LIKE 'לוג אירוע:".$eid.":%' ORDER BY LogDate DESC LIMIT 100");
					$num=$result1->num_rows;
					if ($num >= 0)
						{
						$to_print = <<<IsrTG
						<tr class='TableRow'>
							<td class='RowCell'>היסטוריית התפקדות (סגל בלבד):</td>
							<td class='RowCell'>
								<table id='Event_Attendance_HistoryLog'>
									<tr class='TableTitle'>
										<td class='TitleCell'>מבצע הפעולה</td>
										<td class='TitleCell'>פעולה</td>
										<td class='TitleCell'>תאריך</td>
									</tr>
IsrTG;

						if ($result1 !== False)
							{
							while ($row1 = $result1->fetch_assoc())
								{
								if ($row1["JoomlaID"]>0)
									{
									$tname = PlayerName($row1["JoomlaID"]);
									} 
									else
									{
									$tname = "-";
									}
								$to_print .=<<<IsrTG
									<tr class='TableRow'>
										<td class='RowCell'>{$tname}</td>
										<td class='RowCell'>
IsrTG;
										$to_print .= str_replace("לוג אירוע:".$eid.":","",$row1["Log"]);
								$to_print .=<<<IsrTG
										</td>
										<td class='RowCell'>
IsrTG;
								$to_print .= displayFullDate($row1["LogDate"]);
								$to_print .= <<<IsrTG
										</td>
									</tr>
IsrTG;
								}
							$result->free();
							}
					
						$to_print .= "</table></td></tr>";
						echo $to_print;
						}
					}
			} 
			else 
			{
			if ($row['Summary']) print "<tr class='TableRow'><td class='RowCell'>סיכום:</td><td class='RowCell'>".fromdbprint($row['Summary'])."</td></tr>";
			$players_arrived = array();
			$attend = "";
			$havraza = "";
			$ttl_attend = 0;
			if ($result1 = $db->query("SELECT e.*,d.Name FROM mngr_EventsLog e LEFT JOIN mngr_Dutys d ON e.DutyID=d.DutyID WHERE EventID=".$eid." ORDER BY DutyID"))
				{
				while ($row1 = $result1->fetch_assoc())
					{
					if ($row1['Panelty']==0) 
						{ //Player attended the event
							$duty = "";
							if ($row1["Name"]!="") $duty = "<span class='Bold'>".$row1["Name"]."</span> - ";
							
							$attend .= "<div class='Member_Attendance_Status_Yes'><img src='/mngr/images/yes.png'> ".$duty."&nbsp;&nbsp;<a href='/חברי-הקלאן/?pid=".$row1['JoomlaID']."'>".PlayerName($row1['JoomlaID'],true)."</a></div>";
							array_push($players_arrived,$row1['JoomlaID']);
							
							$ttl_attend++;
						} 
						else
						{
							$havraza .= "<div class='Member_Havraza' data-userid='".$row1['JoomlaID']."'><img src='/mngr/images/no.png'>&nbsp;&nbsp;<a href='/חברי-הקלאן/?pid=".$row1['JoomlaID']."'>".PlayerName($row1['JoomlaID'],true)."</a></div>";
						}
					}
					$result1->free();
					if ($havraza=="") $havraza = "<span class='Approve'>אין</span>";
					if ($ttl_attend==0) $attend = "<span class='Warning'>אין</span>";
				}
				
				$players_missed_print = '';
				$players_missed_count = 0;
				//loop through all invited
				//print_r($players_arrived);
				//print "A".SearchInArray($players_arrived,$row1["JoomlaID"]);
				for ($i=0;$i<count($wanted);$i++)
				{
					if (SearchInArray($players_arrived,$wanted[$i])==False) { //If Player have not arrived to event - add him to list
						$players_missed_print .= "<div class='Member_Attendance_Status_No'><img src='/mngr/images/no.png'>&nbsp;&nbsp;<a href='/חברי-הקלאן/?pid=".$row1['JoomlaID']."'>".PlayerName($row1['JoomlaID'],true)."</a></div>";
						$players_missed_count++;
					}
				}
				if ($players_missed_print == 0) $players_missed_print = "<span class='Approve'>אין</span>";
				
				$to_print = <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>נוכחות בפועל ({$ttl_attend}):</td>
					<td class='RowCell'>{$attend}</td>
				</tr>
IsrTG;

				if (PageRank($perm_Admin_Segel,"")) 
					{
						$to_print .= <<<IsrTG
						<tr class='TableRow'>
							<td class='RowCell'>שחקנים שהוזמנו ולא הגיעו (למנהלים): </td>
							<td class='RowCell'>{$players_missed_print}</td>
						</tr>
IsrTG;
					}
				$to_print .= <<<IsrTG
				<tr class='TableRow'>
					<td class='RowCell'>הברזות:</td>
					<td class='RowCell'>{$havraza}</td>
				</tr>
IsrTG;
				echo $to_print;
			}
			
			print "</table>";
			}
		}
	}
	//print("<input type=button onClick='javascript:history.back();' value='חזור'>");
	print("</div>");
	$db->close();
?>

<?php include "footer.php"; ?>