<?php
/*
 * Template Name: isrtg_admin_events
 */
include "admin_Menu.php";
include "header.php";
?>

<?php
	if (PageRank($perm_Admin_Segel,"אינך מורשה להיכנס לעמוד זה")) {
	
	$db = db_Connect();
	$updated = '';
	
	$eventid = getFieldg("eventid");
	$close = getFieldg("close");
	$delete = getFieldg("delete");
	
	function saveSelectedDutysHelper($obj,$grpCount,$lowerGrp,$dutyCount) {
		for ($i=0;$i<=(count($obj->dutysArr)-1);$i++) { //Loop through dutys in group
			if (getField("grp".$grpCount."_lwr".$lowerGrp."_dty".$dutyCount)==True) {
				$obj->dutysArr[$i]->available = True;
			} else {
				$obj->dutysArr[$i]->available = False;
			}
			$dutyCount++;
		}
		
		$dutyCount = 0;
		if (count($obj->lowerGroupsArr)>0) {
			for ($i=0;$i<=(count($obj->lowerGroupsArr)-1);$i++) {
				saveSelectedDutysHelper($obj->lowerGroupsArr[$i],$grpCount,$i+1,$dutyCount);
			}
		}
		return;
	}
	
	function printDutysSelectOptions($arr,$dutys,$pid)	{
		$res = "";
		for ($i=0;$i<count($arr);$i++) {
			$res .= printDutysSelectOptionsHelper($arr[$i],$dutys,$i,0,0,$pid);
		}
		return $res;
	}
	
	function printDutysSelectOptionsHelper($obj,$dutys,$grpCount,$lowerGrp,$dutyCount,$pid,$parentName = "") {
		$res = "";
		//$res = "<table border=0 width=100% >";
		//count available dutys
		$availableDutysNum = 0;
		for ($i=0;$i<=(count($obj->dutysArr)-1);$i++) {
			if ($obj->dutysArr[$i]->available==True) {
				$availableDutysNum++;
			}
		}
		
		//if ($availableDutysNum>0) {
			//$res .= "<tr valign=top><td width=100% style=' padding-right: ".(30*$level)."px;'>";
			if ($parentName!="") $parentName .= " >> ";
			$parentName .= $obj->Name;
			for ($i=0;$i<=(count($obj->dutysArr)-1);$i++) { //Loop through dutys in group
				$checkedTxt = '';
				if ($obj->dutysArr[$i]->available==True && checkPlayerQuliForDuty($obj->dutysArr[$i]->id,$pid)) {
					if ($obj->dutysArr[$i]->playerID==$pid) {
						$checkedTxt = ' selected';
					}
					//Check if player can play duty before allowing it
					$res .= "<option value='grp".$grpCount."_lwr".$lowerGrp."_dty".$dutyCount."' id='grp".$grpCount."_lwr".$lowerGrp."_dty".$dutyCount."'".$checkedTxt.">".$parentName." >> ".$dutys[$obj->dutysArr[$i]->id]."</option>";
				}
				$dutyCount++;
			}
			//$res .= "</td></tr>";
		//}
		//$res .= "</table>";
		
		$dutyCount = 0;
		if (count($obj->lowerGroupsArr)>0) {
			for ($i=0;$i<=(count($obj->lowerGroupsArr)-1);$i++) {
				$res .= printDutysSelectOptionsHelper($obj->lowerGroupsArr[$i],$dutys,$grpCount,$i+1,$dutyCount,$pid,$parentName);
			}
		}
		return $res;
	}
	
	if (getField("mode")=="new") //Add new event
	{
		//Auto decline event for members in vacation
		$players_no = '';
		if ($result = $db->query("SELECT JoomlaID FROM mngr_Players WHERE Status=".PLAYER_STATUS_VACATION))
		{
			while ($row = $result->fetch_assoc())
				{
					if ($players_no!='') $players_no .= ",";
					$players_no .= $row['JoomlaID'];
				}
				$result->free();
		}
		
		$PlayersRank = "";
		$PlayersName = "";
		$PlayersQuli = "";
		if (getField("invite_all")!="1")
		{
			if (getField("byRank")=="1") $PlayersRank = ArrayToMulti(toDBp("rank_select"));
			if (getField("byPlayer")=="1") $PlayersName = ArrayToMulti(toDBp("specific_select"));
			if (getField("byQuli")=="1") $PlayersQuli = ArrayToMulti(toDBp("quli_select"));
		}
		
		//Dutys Structure Handler
		for ($i=0;$i<count($infantryStruct);$i++) { //Loop through all 
			saveSelectedDutysHelper($infantryStruct[$i],$i,0,0);
		}
		
		$db->query("INSERT INTO mngr_Events (Name,Type,Notes,PlayersByRank,PlayersByName,PlayersByQuli,Badges,Qulifications,EventDate,PlayersDecline,DutyStruct,CountMethod) VALUES ('".toDBp('tname')."','".toDBp('type')."','".toDBp('notes')."','".$PlayersRank."','".$PlayersName."','".$PlayersQuli."','".ArrayToMulti(toDBp('badge'))."','".ArrayToMulti(toDBp('quli'))."','".toDBp('tyear')."-".toDBp('tmonth')."-".toDBp('tday')." ".toDBp('thour').":".toDBp('tmin').":00','".$players_no."','".serialize($infantryStruct)."','".toDBp('countMethod')."')");
		$eid = $db->insert_id;
		$wanted = MultiToArray(EventWantedPlayers($eid));
		
		if ($_FILES["file"]["name"]) //there is a file to upload
		{
			$allowedExts = array("pdf");
			$temp = explode(".", $_FILES["file"]["name"]);
			$extension = end($temp);
			$fname = $db->insert_id.".".$extension;
			
			if ($_FILES["file"]["type"]=="application/pdf" && in_array($extension, $allowedExts))
			{
				if ($_FILES["file"]["error"] > 0)
				{
					header("Location: admin-events?msg=3");
				} else {
					//check if file already exists
					if (file_exists(SERVER_PATH."/mngr/briefings/".$fname))
					{
						unlink(file_exists(SERVER_PATH."/mngr/briefings/".$fname)); //delete file
					}
					move_uploaded_file($_FILES["file"]["tmp_name"],SERVER_PATH."/mngr/briefings/".$fname); //copy new uploaded file to correct dir
				}
			} else {
					header("Location: admin-events?msg=4");
			}
		}
		
		//Send notification email to lehitpaked
		for ($i=0;$i<count($wanted);$i++)
		{
			$status = GetValue("Status","mngr_Players","JoomlaID=".$wanted[$i]);
			$notifyEmail = GetValue("NotifyEmail","mngr_Players","JoomlaID=".$wanted[$i]);
			if ($status==0 && $notifyEmail)
			{
				$email = GetValue("Email","mngr_Players","JoomlaID=".$wanted[$i]);
				$etime = displayFullDate(GetValue("EventDate","mngr_Events","EventID=".$eid));
				$body = "שלום,<br>הוזמנת להגיע לאירוע חדש - ".toDBp('tname')." (".toDBp('type').") שיתקיים ב: ".$etime."<br>כדי להגיע לעמוד האירוע באפשרותך ללחוץ <a href='http://www.isrtg.com/index.php/calendar-day?eid=".$eid."'>כאן</a>.<br>שים לב: ניתן לעדכן נוכחות עד יום לפני האירוע בשעה 20:00<br><br>בברכה,<br>צוות IsrTG‬‎.";
				sendmail($email,"הוזמנת לאירוע חדש",$body);
			}
		}
		
		addlog(fm_getSessionID(),"יצר את האירוע ".toDBp('tname')." בתאריך ".toDBp('tday')."/".toDBp('tmonth')."/".toDBp('tyear')." בשעה ".toDBp('thour').":".toDBp('tmin'));
		header("Location: admin-events?msg=2");
	}
	
	if (getField("mode")=="edit") //Update event details
	{
		//Dutys Structure Handler
		//TODO: if a duty was canceled and a player was already registered to it -> notify to player by email and alert manager before update
		
		$infantryStruct = unserialize(GetValue("DutyStruct","mngr_Events","EventID=".toDBp('eventid')));
		for ($i=0;$i<count($infantryStruct);$i++) { //Loop through all 
			saveSelectedDutysHelper($infantryStruct[$i],$i,0,0);
		}
		
		$db->query("UPDATE mngr_Events SET Name='".toDBp('tname')."', Type='".toDBp('type')."', Notes='".toDBp('notes')."', Badges='".ArrayToMulti(toDBp('badge'))."', Qulifications='".ArrayToMulti(toDBp('quli'))."', DutyStruct='".serialize($infantryStruct)."', EventDate='".toDBp('tyear')."-".toDBp('tmonth')."-".toDBp('tday')." ".toDBp('thour').":".toDBp('tmin').":00' WHERE EventID=".toDBp('eventid'));

		$playersLock = GetValue("PlayersLock","mngr_Events","EventID=".toDBp('eventid'));
		if ($playersLock=="") //If event is not locked, update invites
		{
			$PlayersRank = "";
			$PlayersName = "";
			$PlayersQuli = "";
			if (getField("invite_all")!="1")
			{
				if (getField("byRank")=="1") $PlayersRank = ArrayToMulti(toDBp("rank_select"));
				if (getField("byPlayer")=="1") $PlayersName = ArrayToMulti(toDBp("specific_select"));
				if (getField("byQuli")=="1") $PlayersQuli = ArrayToMulti(toDBp("quli_select"));
			}
			$db->query("UPDATE mngr_Events SET PlayersByRank='".$PlayersRank."', PlayersByName='".$PlayersName."', PlayersByQuli='".$PlayersQuli."' WHERE EventID=".toDBp('eventid'));
		}
		
		if ($_FILES["file"]["name"]) //there is a file to upload
		{
			$allowedExts = array("pdf");
			$temp = explode(".", $_FILES["file"]["name"]);
			$extension = end($temp);
			$fname = toDBp('eventid').".".$extension;
			
			if ($_FILES["file"]["type"]=="application/pdf" && in_array($extension, $allowedExts))
			{
				if ($_FILES["file"]["error"] > 0)
				{
					header("Location: admin-events?msg=3");
				} else {
					//check if file already exists
					if (file_exists(SERVER_PATH."/mngr/briefings/".$fname))
					{
						unlink(file_exists(SERVER_PATH."/mngr/briefings/".$fname)); //delete file
					}
					move_uploaded_file($_FILES["file"]["tmp_name"],SERVER_PATH."/mngr/briefings/".$fname); //copy new uploaded file to correct dir
				}
			} else {
					header("Location: admin-events?msg=4&type=".$_FILES["file"]["type"]);
			}
		}
		
		if (getField("notify_change")=="1") //User selected to clear hitpakduyot and send notification email (date/time of event changed)
		{
			$db->query("UPDATE mngr_Events SET PlayersAccept='', PlayersMaybe='', PlayersDecline='' WHERE EventID=".toDBp('eventid')); //reset hitpakdut
			$wanted = MultiToArray(EventWantedPlayers(toDBp('eventid')));
			//Send notification email to lehitpaked *AGAIN*
			for ($i=0;$i<count($wanted);$i++)
			{
				$status = GetValue("Status","mngr_Players","JoomlaID=".$wanted[$i]);
				if ($status<=1)
				{
					$email = GetValue("Email","mngr_Players","JoomlaID=".$wanted[$i]);
					$etime = displayFullDate(GetValue("EventDate","mngr_Events","EventID=".toDBp('eventid')));
					$body = "שלום,<br>מועד האירוע שהוזמנת אליו - ".toDBp('tname')." (".toDBp('type').") עודכן. מועד האירוע החדש הוא: ".$etime."<br><b>כלל ההתפקדויות של האירוע אופסו ולכן יש להזין התפקדות מחדש, בהתאם למועד החדש!</b><br>כדי להגיע לעמוד האירוע באפשרותך ללחוץ <a href='http://www.isrtg.com/index.php/calendar-day?eid=".$eid."'>כאן</a>.<br>שים לב: ניתן לעדכן נוכחות עד יום לפני האירוע בשעה 20:00<br><br>בברכה,<br>צוות IsrTG‬‎.";
					sendmail($email,"אירוע שהוזמנת אליו עודכן!",$body);
				}
			}
			addlog(fm_getSessionID(),"עדכן את האירוע ".toDBp('tname')." (".toDBp('type').") ואיפס את ההתפקדויות שלו");
		} else {
			addlog(fm_getSessionID(),"עדכן את האירוע ".toDBp('tname')." (".toDBp('type').")");
		}
		
		header("Location: admin-events?msg=5");
	}
	
	if (getField("mode")=="close") //close selected event
	{
		$db->query("UPDATE mngr_Events SET Name='".toDBp('tname')."', Type='".toDBp('type')."', Notes='".toDBp('notes')."', Badges='".ArrayToMulti(toDBp('badge'))."', Qulifications='".ArrayToMulti(toDBp('quli'))."', Summary='".toDBp('summary')."', EventDate='".toDBp('tyear')."-".toDBp('tmonth')."-".toDBp('tday')." ".toDBp('thour').":".toDBp('tmin').":00', Status=1 WHERE EventID=".getField('eventid'));
		$players_penalty = getField('to_penalty');
		$players_participated = getField('players');
		$players_count = count($players_participated);
		if (getField('players')=="") $players_count = 0;
		$ev_name = GetValue("Name","mngr_Events","EventID=".toDBp('eventid'));
		$ev_quli = MultiToArray(GetValue("Qulifications","mngr_Events","EventID=".toDBp('eventid')));
		$ev_badges = MultiToArray(GetValue("Badges","mngr_Events","EventID=".toDBp('eventid')));
		addlog(fm_getSessionID(),"ביצע סיכום לאירוע ".$ev_name." עם ".$players_count." שחקנים");
		
		//Loop through all participating players
		if ($players_participated)
		{
			for ($i=0;$i<$players_count;$i++)
			{
				$duty = toDBp('duty'.$players_participated[$i]);
				if ($duty=="") $duty=0;
				$db->query("INSERT INTO mngr_EventsLog (EventID,JoomlaID,DutyID) VALUES(".toDBp('eventid').",".$players_participated[$i].",".$duty.")");
				//Add qulifications if needed
				if ($ev_quli)
				{
					for ($z=0;$z<count($ev_quli);$z++)
					{
						Player_AddQuli($players_participated[$i],$ev_quli[$z]);
					}
				}
				
				//Add badges if needed
				if ($ev_badges)
				{
					for ($z=0;$z<count($ev_badges);$z++)
					{
						Player_AddBadge($players_participated[$i],$ev_badges[$z]);
					}
				}
			}
		}
		
		//Loop through all penalty players
		if ($players_penalty)
		{
			for ($i=0;$i<count($players_penalty);$i++)
			{
				if (!in_array($players_penalty[$i],$players_participated))
				{
					$db->query("INSERT INTO mngr_EventsLog (EventID,JoomlaID,Panelty) VALUES(".toDBp('eventid').",".$players_penalty[$i].",True)");
				}
			}
		}
		
		//redirect to main event screen
		header("Location: admin-events?msg=1");
	}
	
	if (getField("mode")=="unclose") //unclose selected event
	{
		if (getField('eid'))
		{
			$ev_name = GetValue("Name","mngr_Events","EventID=".getField('eid'));
			$ev_date = DisplayFullDate(GetValue("EventDate","mngr_Events","EventID=".getField('eid')));
			$db->query("UPDATE mngr_Events SET Status=0 WHERE EventID=".getField('eid'));
			$db->query("DELETE FROM mngr_EventsLog WHERE EventID=".toDBp("eid"));
			addlog(fm_getSessionID(),"ביטל את סגירת האירוע ".$ev_name." (".$ev_date.")");
			
			header("Location: admin-events?eventid=".getField('eid'));
		}
	}
	
	if (getFieldg("mode")=="delete_file") //delete selected event brief file
	{
		if (file_exists(SERVER_PATH."/mngr/briefings/".getFieldg("eventid").".pdf"))
		{
			unlink(SERVER_PATH."/mngr/briefings/".getFieldg("eventid").".pdf"); //delete file
		}
		header("Location: admin-events?eventid=".getFieldg("eventid"));
	}
?>

<script language=javascript>
	function eventSel(id)
	{
		if (id)
		{
			window.location.href='?eventid='+id;
		}
	}
	
	function UpdateEvent()
	{
		if (isDate(document.getElementById('tday').value+'/'+document.getElementById('tmonth').value+'/'+document.getElementById('tyear').value))
		{
			if (document.getElementById('edate').value!=(document.getElementById('tday').value+'/'+document.getElementById('tmonth').value+'/'+document.getElementById('tyear').value+' '+document.getElementById('thour').value+':'+document.getElementById('tmin').value))
			{
				if (confirm('שים לב! ביצעת שינוי במועד האירוע.\nהאם ברצונך לאפס את ההתפקדויות לאירוע ולהתריע למוזמנים על כך במייל?'))
				{
					document.getElementById('notify_change').value = '1';
				}
			}
			tform.submit();
		} else {
			alert('תאריך לא חוקי');
		}
	}
	
function isDate(txtDate, separator) {
    var aoDate,           // needed for creating array and object
        ms,               // date in milliseconds
        month, day, year; // (integer) month, day and year
    // if separator is not defined then set '/'
    if (separator === undefined) {
        separator = '/';
    }
    // split input date to month, day and year
    aoDate = txtDate.split(separator);
    // array length should be exactly 3 (no more no less)
    if (aoDate.length !== 3) {
        return false;
    }
    // define month, day and year from array (expected format is d/m/yyyy)
    // subtraction will cast variables to integer implicitly
    day = aoDate[0] - 0;
	month = aoDate[1] - 1; // because months in JS start from 0
    year = aoDate[2] - 0;
    // test year range
    if (year < 1000 || year > 3000) {
        return false;
    }
    // convert input date to milliseconds
    ms = (new Date(year, month, day)).getTime();
    // initialize Date() object from milliseconds (reuse aoDate variable)
    aoDate = new Date();
    aoDate.setTime(ms);
    // compare input date and parts from Date() object
    // if difference exists then input date is not valid
    if (aoDate.getFullYear() !== year ||
        aoDate.getMonth() !== month ||
        aoDate.getDate() !== day) {
        return false;
    }
    // date is OK, return true
    return true;
}
</script>

<div id="IsrTG_Content">
<?php
print("$MenuToPrint");
?>

<div id="Main_Calander_Managment">

		<?php
			if (getFieldg('msg')==1) print "<div class='Approve'>האירוע נסגר בהצלחה</div>";
			if (getFieldg('msg')==2) print "<div class='Approve'>האירוע נוסף בהצלחה</div>";
			if (getFieldg('msg')==3) print "<div class='Error'>שגיאה בהעלאת קובץ התדריך</div>";
			if (getFieldg('msg')==4) print "<div class='Error'>קובץ התדריך חייב להיות מסוג PDF</div>";
			if (getFieldg('msg')==5) print "<div class='Approve'>האירוע עודכן בהצלחה</div>";
		?>

	<div class='Title'>
		<h1>ניהול אירועים</h1>
	</div>
	
	<div class="MainContent">
<?php	
		if (getFieldg("mode")!="new")
			{
		?>
		<form method=post id="tform" enctype="multipart/form-data"><input type=hidden name=eventid value='<?php echo $eventid;?>'>
		<table border=1 style="border-collapse: collapse;" align=center width=95%>
		<tr>
			<td colspan=2 style="font-weight:bold;text-align:center;">
			<?php
				if ($result = $db->query("SELECT * FROM mngr_Events WHERE Status=0 ORDER BY EventDate"))
				{
					$options = '';
					if ($result->num_rows==0)
					{
						print "אין אירועים פתוחים";
					}
					else
					{
			?>
						נהל אירוע פתוח:
						<select name="events" onChange="eventSel(this.options[this.selectedIndex].value);"><option value="">בחר אירוע</option>
			<?php
						while ($row = $result->fetch_assoc())
						{
							if ($row['EventID']==$eventid)
							{
								$options .= "<option value=".$row['EventID']." selected>".$row['Name']." (".$row['Type']." - ".displayDate($row['EventDate']).")</option>";
							} else {
								$options .= "<option value=".$row['EventID'].">".$row['Name']." (".$row['Type']." - ".displayDate($row['EventDate']).")</option>";
							}
						}
						print $options."</select>";
					}
				}
				$result->free();
			?>
			</td>
		</tr>
		<?php
		if ($eventid=='')
		{
		?>
		</table>
		</form>
		<br>
		
		<?php
			//Show all closed events in different select boxes by events type
			for ($i=0;$i<count($eventTypes);$i++) { //Loop through all events types
				$options = '';
				print "<form method=post onSubmit=\"if (!confirm('האם אתה בטוח שברצונך לבטל את סגירת האירוע?\nפעולה זו לא תבטל הענקת דרגות/הכשרות/עיטורים.')) return false;\"><input type=hidden name=mode value='unclose'>";
				print "<table border=1 style='border-collapse: collapse;' align=center width=95%>";
				print "<tr>";
					print "<td colspan=2 style='font-weight:bold;text-align:center;'>";
					print "בטל סגירת אירוע - ".$eventTypes[$i].": ";
					print "<select name='eid'><option value=''>בחר אירוע</option>";
					if ($result = $db->query("SELECT * FROM mngr_Events WHERE Status=1 AND Type='".$eventTypes[$i]."' ORDER BY EventDate DESC")) {
						while ($row = $result->fetch_assoc()) {
							$options .= "<option value='".$row['EventID']."'>".$row['Name']." (".$row['Type']." - ".displayDate($row['EventDate']).")</option>";
						}
						print $options."</select> <input type=submit value='בטל סגירה'>";
					}
					$result->free();
					print "</td>";
				print "</tr></table></form>";
			}
		?>
		
		<br><br>
		<p align=center><input type=button onClick="window.location.href='?mode=new';" value="צור אירוע חדש"> <input type=button onClick="window.location.href='admin';" value="חזור"></p>
		<?php
		} else {
		
		unset($result);
		if (!$result = $db->query("SELECT * FROM mngr_Events WHERE EventID=".$eventid))
		{
			print "Event not found!";
			exit;
		}
		
		if ($delete) //Delete Event
		{
			$ev_name = GetValue("Name","mngr_Events","EventID=".$eventid);
			$ev_date = DisplayFullDate(GetValue("EventDate","mngr_Events","EventID=".$eventid));
			$db->query("DELETE FROM mngr_Events WHERE EventID=".$eventid);
			addlog(fm_getSessionID(),"מחק את האירוע ".$ev_name. "(".$ev_date.")");
			header("Location: admin-events");
		}
		
		$row = $result->fetch_assoc();
		$invite_rank = $row["PlayersByRank"];
		$invite_name = $row["PlayersByName"];
		$invite_quli = $row["PlayersByQuli"];
		$wanted = MultiToArray(EventWantedPlayers($eventid));
		?>
		<script language=javascript>
			function checkType() {
				if ($("select[name='type']").val()=='משימת פבליק') {
					$('.quli_checkbox,.badge_checkbox').attr('checked',false);
					$('.quli_checkbox,.badge_checkbox').attr('disabled',true);
				} else {
					$('.quli_checkbox,.badge_checkbox').attr('disabled',false);
				}
			}
			
			//Run check type select box function - disable badges & quli if needed
			$(function() {
				checkType();
			});
			
		</script>
		<tr>
			<td width=20%>שם האירוע:</td>
			<td><input type=text name=tname value='<?php echo $row['Name']?>' size=30></td>
		</tr>
		<tr>
			<td>תאריך:</td>
			<td>
			
			<?php
				$eventDate = $row['EventDate'];
				$currYear = date("Y");
				$startYear = 2009;
				$lastYear = date("Y") + 1;
				print "<input type=hidden name=edate id=edate value='".displayFullDate($eventDate)."'><input type=hidden name=notify_change id=notify_change value='0'>";
			?>
			
			<select name=tyear id=tyear>
				<?php
				for ($i=$startYear; $i<=$lastYear; $i++)
				{
					$sel="";
					if ($i==date_format(date_create($eventDate),"Y")) $sel=" selected";
					print "<option value='".$i."'".$sel.">".$i."</option>";
				}
				?>
			</select> <select name=tmonth id=tmonth>
			<?php
			for ($i=1; $i<=12; $i++)
			{
				$sel="";
				$fi = $i;
				if (strlen($fi)==1) $fi="0".$fi;
				if ($i==date_format(date_create($eventDate),"n")) $sel=" selected";
				print "<option value='".$fi."'".$sel.">".$fi."</option>";
			}
			?>
			</select> <select name=tday id=tday>
			<?php
			for ($i=1; $i<=31; $i++)
			{
				$sel="";
				$fi = $i;
				if (strlen($fi)==1) $fi="0".$fi;
				if ($i==date_format(date_create($eventDate),"j")) $sel=" selected";
				print "<option value='".$fi."'".$sel.">".$fi."</option>";
			}
			?>
			</select> 
			</td>
		</tr>
		<tr>
			<td>שעה:</td>
			<td>
			<select name=tmin id=tmin>
			<?php
			for ($i=0; $i<=59; $i++)
			{
				$sel="";
				$ti = $i;
				if (strlen($i)==1) $ti = "0".$i;
				if ($i==date_format(date_create($eventDate),"i")) $sel=" selected";
				
				print "<option value='".$ti."'".$sel.">".$ti."</option>";
			}
			?>
			</select> : <select name=thour id=thour>
			<?php
			for ($i=0; $i<=23; $i++)
			{
				$sel="";
				$ti = $i;
				if (strlen($i)==1) $ti = "0".$i;
				if ($i==date_format(date_create($eventDate),"G")) $sel=" selected";
				print "<option value='".$ti."'".$sel.">".$ti."</option>";
			}
			?>
			</select> 
			</td>
		</tr>
		<tr>
			<td>סוג אירוע:</td>
			<td>
			<select name=type onChange="checkType();">
				<?php
				for ($i=0;$i<count($eventTypes);$i++) { //Loop through all events types
					print "<option value='".$eventTypes[$i]."'".(($row['Type']==$eventTypes[$i]) ? " selected":"").">".$eventTypes[$i]."</option>";
				}
				?>
			</select>
			</td>
		</tr>
		<tr>
			<td>קובץ תדריך:</td>
			<td><?php if (file_exists(SERVER_PATH."/mngr/briefings/".$eventid.".pdf")) print "<a href='/mngr/briefings/".$eventid.".pdf' target=_blank>http://www.isrtg.com/mngr/briefings/".$eventid.".pdf</a> <input type=button onClick=\"window.location.href='admin-events?eventid=".$eventid."&mode=delete_file';\" value='מחק קובץ'><br>" ?><input type=file name='file' id='file'></td>
		</tr>
		<tr>
			<td>הערות:</td>
			<td><textarea name=notes rows=10 cols=48><?php echo fromdb($row['Notes'])?></textarea></td>
		</tr>
		<tr>
			<td>הכשרות שיוענקו למשתתפים:</td>
			<td>
			<?php
				if ($result1 = $db->query("SELECT * FROM mngr_Quli"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						if (SearchInMulti($row['Qulifications'],$row1['QuliID']))
						{
							print "<input type=checkbox class='quli_checkbox' name='quli[]' value=".$row1['QuliID']." checked> ".$row1['Name']."<br>";
						} else {
							print "<input type=checkbox class='quli_checkbox' name='quli[]' value=".$row1['QuliID']."> ".$row1['Name']."<br>";
						}
					}
				}
				$result1->free();
			?>
			</td>
		</tr>
		<tr>
			<td>עיטורים שיוענקו למשתתפים:</td>
			<td>
			<?php
				
				if ($result1 = $db->query("SELECT * FROM mngr_Badges"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						if (SearchInMulti($row['Badges'],$row1['BadgeID']))
						{
							print "<input type=checkbox class='badge_checkbox' name='badge[]' value=".$row1['BadgeID']." checked> ".$row1['Name']."<br>";
						} else {
							print "<input type=checkbox class='badge_checkbox' name='badge[]' value=".$row1['BadgeID']."> ".$row1['Name']."<br>";
						}
					}
				}
				$result1->free();
			?>
			</td>
		</tr>
		<?php
		if ($row['PlayersLock']!="")
		{
		?>
			<tr>
				<td>שחקנים המוזמנים לאירוע:</td>
				<td><b><font color=red>הזמנת שחקנים ננעלה.<br>פעולה זו מתבצעת יום לפני האירוע בשעה 19:30.</font></b></td>
			</tr>
		<?php
		} else {
		?>
			<tr>
				<td>שחקנים המוזמנים לאירוע:<br><div align=center><input type=checkbox name="invite_all" id="invite_all" value="1" <?php if ($invite_rank=="" && $invite_name=="" && $invite_quli=="") print "checked";?> onClick="$('#byRank,#byPlayer,#byQuli').attr('disabled',this.checked).attr('checked', false);$('#byRank_td,#byPlayer_td,#byQuli_td').hide();"> <label for="invite_all"><b>כולם</b></label></div></td>
				<td>
					<table border=0 width=95% align=center><tr><td align=center valign=top width=30%>
						<table border=1 style="border-collapse: collapse;" width=100%>
						<tr><td><input type=checkbox id="byRank" name="byRank" value="1" onClick="$('#byRank_td').toggle('display');" <?php if ($invite_rank=="" && $invite_name=="" && $invite_quli=="") print "disabled";?> <?php if ($invite_rank!="") print "checked";?>> <label for="byRank">לפי דרגה</label> <span style="font-size: 11px;">(<a href=javascript:void(); onClick="$('input[name=rank_select[]]').attr('checked',true);">סמן הכל</a> / <a href=javascript:void(); onClick="$('input[name=rank_select[]]').attr('checked',false);">נקה הכל</a>)</span></td></tr><tr><td id="byRank_td" <?php if ($invite_rank=="") print "style='display: none;'"?>>
						<?php
						if ($result1 = $db->query("SELECT * FROM mngr_Ranks ORDER BY RankID"))
						{
							while ($row1 = $result1->fetch_assoc())
							{
								$sel = "";
								if (SearchInMulti($invite_rank,$row1["RankID"])) $sel = " checked";
								print "<input type=checkbox name='rank_select[]' value='".$row1['RankID']."'".$sel."> ".$row1["Name"]."<BR>";
							}
							$result1->free();
						}
						?>
						</td></tr></table>
					</td><td align=center valign=top width=70%>
						<table border=1 style="border-collapse: collapse;" width=100%>
						<tr><td><input type=checkbox id='byPlayer' name="byPlayer" value="1" onClick="$('#byPlayer_td').toggle('display');" <?php if ($invite_rank=="" && $invite_name=="" && $invite_quli=="") print "disabled";?> <?php if ($invite_name!="") print "checked";?>> <label for="byPlayer">לפי שחקן</label> <span style="font-size: 11px;">(<a href=javascript:void(); onClick="$('input[name=specific_select[]]').attr('checked',true);">סמן הכל</a> / <a href=javascript:void(); onClick="$('input[name=specific_select[]]').attr('checked',false);">נקה הכל</a>)</span></td></tr><tr><td id="byPlayer_td" <?php if ($invite_name=="") print "style='display: none;'"?>>
						<table border=0><tr><td>
						<?php
						if ($result1 = $db->query("SELECT * FROM mngr_Players WHERE Status<2"))
						{
							$num = $result1->num_rows;
							$num = ceil($num/2);
							$count = 1;
							while ($row1 = $result1->fetch_assoc())
							{
								$sel = "";
								if (SearchInMulti($invite_name,$row1["JoomlaID"])) $sel = " checked";
								print "<input type=checkbox name='specific_select[]' value='".$row1['JoomlaID']."'".$sel."> ".PlayerName($row1["JoomlaID"]);
								if ($count==$num)
								{
									print "</td><td>";
								} else {
									print "<br>";
								}
								$count++;
							}
							$result1->free();
						}
						?>
						</td></tr></table>
						</td></tr></table>
					</td></tr>
					<tr><td align=center valign=top>
						<table border=1 style="border-collapse: collapse;" width=100%>
						<tr><td><input type=checkbox id="byQuli" name="byQuli" value="1" onClick="$('#byQuli_td').toggle('display');" <?php if ($invite_rank=="" && $invite_name=="" && $invite_quli=="") print "disabled";?> <?php if ($invite_quli!="") print "checked";?>> <label for="byQuli">לפי הכשרה</label> <span style="font-size: 11px;">(<a href=javascript:void(); onClick="$('input[name=quli_select[]]').attr('checked',true);">סמן הכל</a> / <a href=javascript:void(); onClick="$('input[name=quli_select[]]').attr('checked',false);">נקה הכל</a>)</span></td></tr><tr><td id="byQuli_td" <?php if ($invite_quli=="") print "style='display: none;'"?>>
						<?php
						if ($result1 = $db->query("SELECT * FROM mngr_Quli ORDER BY QuliID"))
						{
							while ($row1 = $result1->fetch_assoc())
							{
								$sel = "";
								if (SearchInMulti($invite_quli,$row1["QuliID"])) $sel = " checked";
								print "<input type=checkbox name='quli_select[]' value='".$row1['QuliID']."'".$sel."> ".$row1["Name"]."<BR>";
							}
							$result1->free();
						}
						?>
						</td></tr></table>
					</td></tr></table>
				</td>
			</tr>
		<?php
		}
		if ($row["CountMethod"]=='dutys') {
		?>
			<tr>
				<td>שיטת התפקדות:</td>
				<td>
					<?php
					if ($rs_duty = $db->query("SELECT * FROM mngr_Dutys ORDER BY DutyID"))
					{
						$dutysArr = array();
						while ($rw_duty = $rs_duty->fetch_assoc())
						{
							$dutysArr[$rw_duty["DutyID"]] = $rw_duty["Name"];
						}
					}
					$rs_duty->free();
					
					$dutyStruct = GetValue("DutyStruct","mngr_Events","EventID=".$eventid);
					if ($dutyStruct!="") {
						unset($infantryStruct);
						$infantryStruct = unserialize($dutyStruct);
					}
					printDutysTable($infantryStruct,$dutysArr,$row["CountMethod"]);
					?>
				</td>
			</tr>
		<?php
		}
		if (!$close) 
		{
			print "<tr>";
				print "<td colspan=2 align=center><input type=button onClick=\"UpdateEvent();\" value='עדכן פרטי אירוע'> <input type=button onClick=\"window.location.href='?eventid=".$eventid."&close=1';\" value='סכם אירוע'> <input type=button onClick=\"if (confirm('האם אתה בטוח שברצונך למחוק את אירוע זה? שים לב: פעולה זו אינה ניתנת לביטול')) { window.location.href='?eventid=".$eventid."&delete=1'; }\" value='מחק אירוע'></td>";
			print "</tr>";
			print "<input type=hidden name=mode value='edit'><tr>";
			print "<td colspan=2 style='font-weight:bold;text-align:center;'>נתונים נוספים:</td>";
			print "</tr>";
			
			print "<tr>";
			$players_yes_print = '';
			$players_yes_count = 0;
			if ($row['PlayersAccept'])
			{
				$players_yes = MultiToArray($row['PlayersAccept']);
				for ($i=0;$i<count($players_yes);$i++)
				{
					if (SearchInArray($wanted,$players_yes[$i]))
					{
						$sel_duty = '';
						if ($row["CountMethod"]=="dutys") {
							$sel_duty = "<select onChange=\"window.location.href='calendar-day?eid=".$eventid."&pid=".$players_yes[$i]."&status='+$(this).val();\"><option value='4'>ללא תפקיד</option>".printDutysSelectOptions($infantryStruct,$dutysArr, $players_yes[$i])."</select> | ";
						}
						$link_m = "<a href='/index.php/calendar-day?eid=".$eventid."&pid=".$players_yes[$i]."&status=2'>אולי</a>";
						$link_d = " | <a href='/index.php/calendar-day?eid=".$eventid."&pid=".$players_yes[$i]."&status=1'>לא מגיע</a>";
						$players_yes_print .= "<tr><td>".PlayerName($players_yes[$i])."</td><td>(".$sel_duty.$link_m.$link_d.")</td></tr>";
						$players_yes_count++;
					}
				}
			}
			print "<td width=20%><a name=more></a>שחקנים שאישרו הגעה (".$players_yes_count."):</td>";
			print "<td dir=rtl><table border=0>".$players_yes_print."</table></td>";
			print "</tr>";
			
			print "<tr>";
			$players_maybe_print = '';
			$players_maybe_count = 0;
			if ($row['PlayersMaybe'])
			{
				$players_maybe = MultiToArray($row['PlayersMaybe']);
				for ($i=0;$i<count($players_maybe);$i++)
				{
					if (SearchInArray($wanted,$players_maybe[$i]))
					{
						$link_a = "<a href='/index.php/calendar-day?eid=".$eventid."&pid=".$players_maybe[$i]."&status=3'>מגיע</a>";
						$link_d = "<a href='/index.php/calendar-day?eid=".$eventid."&pid=".$players_maybe[$i]."&status=1'>לא מגיע</a>";
						$players_maybe_print .= "<tr><td>".PlayerName($players_maybe[$i])."</td><td>(".$link_a." | ".$link_d.")</td></tr>";
						$players_maybe_count++;
					}
				}
			}
			print "<td width=20%>שחקנים שאולי יגיעו (".$players_maybe_count."):</td>";
			print "<td dir=rtl><table border=0>".$players_maybe_print."</table></td>";
			print "</tr>";
			
			print "<tr>";
			$players_no_print = '';
			$players_no_count = 0;
			if ($row['PlayersDecline'])
			{
				$players_no = MultiToArray($row['PlayersDecline']);
				for ($i=0;$i<count($players_no);$i++)
				{
					if (SearchInArray($wanted,$players_no[$i]))
					{
						$link_m = "<a href='/index.php/calendar-day?eid=".$eventid."&pid=".$players_no[$i]."&status=2'>אולי</a>";
						$link_a = "<a href='/index.php/calendar-day?eid=".$eventid."&pid=".$players_no[$i]."&status=3'>מגיע</a>";
						$players_no_print .= "<tr><td>".PlayerName($players_no[$i])."</td><td>(".$link_a." | ".$link_m.")</td></tr>";
						$players_no_count++;
					}
				}
			}
			print "<td width=20%>שחקנים שלא אישרו הגעה (".$players_no_count."):</td>";
			print "<td dir=rtl><table border=0>".$players_no_print."</table></td>";
			print "</tr>";
			
			print "<tr>";
			$players_un_print = '';
			$players_un_count = 0;
			$tmp = '';
			
			//loop through all invited
			for ($i=0;$i<count($wanted);$i++)
			{
				if (SearchInMulti($row['PlayersAccept'],$wanted[$i])==False && SearchInMulti($row['PlayersDecline'],$wanted[$i])==False && SearchInMulti($row['PlayersMaybe'],$wanted[$i])==False)
				{
					$link_d = "<a href='/index.php/calendar-day?eid=".$eventid."&pid=".$wanted[$i]."&status=1'>לא מגיע</a>";
					$link_m = "<a href='/index.php/calendar-day?eid=".$eventid."&pid=".$wanted[$i]."&status=2'>אולי</a>";
					$link_a = "<a href='/index.php/calendar-day?eid=".$eventid."&pid=".$wanted[$i]."&status=3'>מגיע</a>";
					$players_un_print .= "<tr><td>".PlayerName($wanted[$i])."</td><td>(".$link_a." | ".$link_m." | ".$link_d.")</td></tr>";
					$players_un_count++;
				}
			}
			print "<td width=20%>שחקנים שטרם הגיבו (".$players_un_count."):</td>";
			print "<td dir=rtl><table border=0>".$players_un_print."</table></td>";
			print "</tr>";
		}
		else
		{
			print "<input type=hidden name=mode value='close'><tr>";
				print "<td colspan=2 style='font-weight:bold;text-align:center;'>סיכום אירוע:</td>";
			print "</tr><tr>";
				print "<td width=20%>סיכום:<br>(נק' לשימור, נק' לשיפור)</td><td><textarea name=summary rows=10 cols=48>".fromdb($row['Summary'])."</textarea></td>";
			print "</tr><tr>";
				$tmp = '';
				$players_yes = $row['PlayersAccept'];
				$players_maybe = $row['PlayersMaybe'];
				$players_no = $row['PlayersDecline'];
				
				$wanted_more = array();
				
				//prepare dutys list
				$dutys_options = "<option value='0'>בחר</option>";
				if ($result1 = $db->query("SELECT * FROM mngr_Dutys ORDER BY DutyID"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						$dutys_options .= "<option value='".$row1["DutyID"]."'>".$row1["Name"]."</option>";
					}
					$result1->free();
				}
				
				//print all players invited
				if ($result1 = $db->query("SELECT * FROM mngr_Players WHERE Status<2 ORDER BY RankID,Name"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						if (!in_array($row1['JoomlaID'],$wanted))
							array_push($wanted_more,$row1['JoomlaID']);
					}
					$result1->free();
				}
				
				$div_duty_content = "";
				
				$tmp .= "<br><img src=/mngr/images/yes.png width=20 height=20> <b>שחקנים שהתפקדו \"מגיע\":</b><br>";
				//Print approved players
				for ($i=0;$i<count($wanted);$i++)
				{
					if (SearchInMulti($players_yes,$wanted[$i]))
					{
						$div_display = "penalty".$wanted[$i].".style.display";
						$div_display1 = "div_duty".$wanted[$i].".style.display";
						if ($row["Type"]=="משימת שישי") $div_duty_content =  " | תפקיד: <select name=duty".$wanted[$i].">".$dutys_options."</select>";
						$tmp .= "<input type=checkbox name='players[]' value='".$wanted[$i]."' onClick=\"if (".$div_display."=='inline') { ".$div_display."='none';".$div_display1."='inline'; } else { ".$div_display."='inline';".$div_display1."='none'; }\" checked> ".PlayerName($wanted[$i])." <div id='penalty".$wanted[$i]."' style='display:none;' dir=rtl> | <input type=checkbox name='to_penalty[]' value='".$wanted[$i]."' checked> <b>החשב כהברזה</b></div><div id='div_duty".$wanted[$i]."' style='display:inline;'>".$div_duty_content."</div><br>";
					}
				}
				
				$tmp .= "<br><br><img src=/mngr/images/maybe.png width=20 height=20> <b>שחקנים שהתפקדו \" אולי\":</b><br>";
				//Print maybe players
				for ($i=0;$i<count($wanted);$i++)
				{
					if (SearchInMulti($players_maybe,$wanted[$i]))
					{
						$div_display = "div_duty".$wanted[$i].".style.display";
						if ($row["Type"]=="משימת שישי") $div_duty_content =  " | תפקיד: <select name=duty".$wanted[$i].">".$dutys_options."</select>";
						$tmp .= "<input type=checkbox name='players[]' value='".$wanted[$i]."' onClick=\"(".$div_display."=='inline') ? ".$div_display."='none' : ".$div_display."='inline';\"> ".PlayerName($wanted[$i])."<div id='div_duty".$wanted[$i]."' style='display:none;'>".$div_duty_content."</div><br>";
					}
				}
				
				$tmp .= "<br><br><img src=/mngr/images/no.png width=20 height=20> <b>שחקנים שהתפקדו \"לא מגיע\":</b><br>";
				//Print declined players
				for ($i=0;$i<count($wanted);$i++)
				{
					if (SearchInMulti($players_no,$wanted[$i]))
					{
						$div_display = "notify".$wanted[$i].".style.display";
						$div_display1 = "div_duty".$wanted[$i].".style.display";
						if ($row["Type"]=="משימת שישי") $div_duty_content =  " | תפקיד: <select name=duty".$wanted[$i].">".$dutys_options."</select>";
						$tmp .= "<input type=checkbox name='players[]' value='".$wanted[$i]."' onClick=\"if (".$div_display."=='inline') { ".$div_display."='none';".$div_display1."='none'; } else { ".$div_display."='inline';".$div_display1."='inline'; }\"> ".PlayerName($wanted[$i])." <div id='div_duty".$wanted[$i]."' style='display:none;'>".$div_duty_content."</div><div id='notify".$wanted[$i]."' style='display:none;' dir=rtl> | <b>השחקן התפקד שאינו מגיע</b></div><br>";
					}
				}
				
				$tmp .= "<br><br><b>שחקנים שלא התפקדו:</b><br>";
				//Print declined players
				for ($i=0;$i<count($wanted);$i++)
				{
					if (!SearchInMulti($players_yes,$wanted[$i]) && !SearchInMulti($players_maybe,$wanted[$i]) && !SearchInMulti($players_no,$wanted[$i]))
					{
						$div_display = "div_duty".$wanted[$i].".style.display";
						if ($row["Type"]=="משימת שישי") $div_duty_content =  " | תפקיד: <select name=duty".$wanted[$i].">".$dutys_options."</select>";
						$tmp .= "<input type=checkbox name='players[]' value='".$wanted[$i]."' onClick=\"(".$div_display."=='inline') ? ".$div_display."='none' : ".$div_display."='inline';\"> ".PlayerName($wanted[$i])."<div id='div_duty".$wanted[$i]."' style='display:none;'>".$div_duty_content."</div><br>";
					}
				}
				
				$tmp .= "<br><br><b>שאר הקלאן:</b><br>";
				for ($i=0;$i<count($wanted_more);$i++)
				{
					$div_display = "div_duty".$wanted_more[$i].".style.display";
					if ($row["Type"]=="משימת שישי") $div_duty_content =  " | תפקיד: <select name=duty".$wanted_more[$i].">".$dutys_options."</select>";
					$tmp .= "<input type=checkbox name='players[]' value='".$wanted_more[$i]."' onClick=\"(".$div_display."=='inline') ? ".$div_display."='none' : ".$div_display."='inline';\"> ".PlayerName($wanted_more[$i])."<div id='div_duty".$wanted_more[$i]."' style='display:none;'>".$div_duty_content."</div><br>";
				}
				
				$tmp .= "<br><br><b>לא פעילים:</b><br>";
				if ($result1 = $db->query("SELECT * FROM mngr_Players WHERE Status=2 ORDER BY RankID,Name"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						$div_display = "div_duty".$row1["JoomlaID"].".style.display";
						if ($row["Type"]=="משימת שישי") $div_duty_content =  " | תפקיד: <select name=duty".$row1["JoomlaID"].">".$dutys_options."</select>";
						$tmp .= "<input type=checkbox name='players[]' value='".$row1["JoomlaID"]."' onClick=\"(".$div_display."=='inline') ? ".$div_display."='none' : ".$div_display."='inline';\"> ".PlayerName($row1["JoomlaID"])."<div id='div_duty".$row1["JoomlaID"]."' style='display:none;'>".$div_duty_content."</div><br>";
					}
				}
				
				print "<td width=20%>סמן נוכחות בפועל:</td>";
				print "<td dir=rtl>".$tmp."</td>";
			print "</tr>";
			
			print "<tr>";
				print "<td colspan=2 align=center><input type=button onClick=\"if(isDate(document.getElementById('tday').value+'/'+document.getElementById('tmonth').value+'/'+document.getElementById('tyear').value)) { tform.submit(); } else { alert('תאריך לא חוקי'); }\" value='שלח סיכום'> <input type=button onClick=\"window.location.href='?eventid=".$eventid."';\" value='ביטול'></td>";
			print "</tr>";
		}
		?>
		</table>
		</form>
		<br><br>
		<p align=center><input type=button onClick="window.location.href='admin-events';" value="חזור"></p>
		<?php 
		$result->free(); 
		}
		} else {
		?>
			<form method=post enctype="multipart/form-data"><input type=hidden name=mode value='new'>
			<table border=1 style="border-collapse: collapse;" align=center width=95%>
			<tr>
				<td width=20%>שם האירוע:</td>
				<td><input type=text name=tname size=30></td>
			</tr>
			<tr>
				<td>תאריך:</td>
				<td>
				<select name=tyear>
				<?php
					$currYear = date("Y");
					$lastYear = date("Y") - 1;
					$nextYear = date("Y") + 1;
					print "<option value=2009>2009</option><option value=2010>2010</option><option value=2011>2011</option>";
					print "<option value=".$lastYear.">".$lastYear."</option>";
					print "<option value=".$currYear." selected>".$currYear."</option>";
					print "<option value=".$nextYear.">".$nextYear."</option>";
				?>
				</select> <select name=tmonth>
				<?php
				for ($i=1; $i<=12; $i++)
				{
					print "<option value='".$i."'>".$i."</option>";
				}
				?>
				</select> <select name=tday>
				<?php
				for ($i=1; $i<=31; $i++)
				{
					print "<option value='".$i."'>".$i."</option>";
				}
				?>
				</select> 
				</td>
			</tr>
			<tr>
				<td>שעה:</td>
				<td>
				<select name=tmin>
				<?php
				for ($i=0; $i<=59; $i++)
				{
					$ti = $i;
					if (strlen($i)==1) $ti = "0".$i;
					print "<option value='".$ti."'>".$ti."</option>";
				}
				?>
				</select> : <select name=thour>
				<?php
				for ($i=0; $i<=23; $i++)
				{
					$ti = $i;
					if (strlen($i)==1) $ti = "0".$i;
					print "<option value='".$ti."'>".$ti."</option>";
				}
				?>
				</select> 
				</td>
			</tr>
			<tr>
				<td>סוג אירוע:</td>
				<td>
				<select name=type onChange="if ($(this).val()=='משימת פבליק') { $('.quli_checkbox,.badge_checkbox').attr('checked',false); $('.quli_checkbox,.badge_checkbox').attr('disabled',true); } else { $('.quli_checkbox,.badge_checkbox').attr('disabled',false); }">
				<?php
					for ($i=0;$i<count($eventTypes);$i++) { //Loop through all events types
						print "<option value='".$eventTypes[$i]."'>".$eventTypes[$i]."</option>";
					}
				?>
				</select>
				</td>
			</tr>
		<tr>
			<td>קובץ תדריך:</td>
			<td><input type=file name='file' id='file'></td>
		</tr>
			<tr>
				<td>הערות:</td>
				<td><textarea name=notes rows=10 cols=48></textarea></td>
			</tr>
			<tr>
				<td>הכשרות שיוענקו למשתתפים:</td>
				<td>
				<?php
					if ($result1 = $db->query("SELECT * FROM mngr_Quli"))
					{
						while ($row1 = $result1->fetch_assoc())
						{
							print "<input type=checkbox class='quli_checkbox' name='quli[]' value=".$row1['QuliID']."> ".$row1['Name']."<br>";
						}
					}
					$result1->free();
				?>
				</td>
			</tr>
			<tr>
				<td>עיטורים שיוענקו למשתתפים:</td>
				<td>
				<?php
					if ($result1 = $db->query("SELECT * FROM mngr_Badges"))
					{
						while ($row1 = $result1->fetch_assoc())
						{
							print "<input type=checkbox class='badge_checkbox' name='badge[]' value=".$row1['BadgeID']."> ".$row1['Name']."<br>";
						}
					}
					$result1->free();
				?>
				</td>
			</tr>
			<tr>
				<td>שחקנים המוזמנים לאירוע:<br><div align=center><input type=checkbox name="invite_all" id="invite_all" value="1" checked onClick="$('#byRank,#byPlayer,#byQuli').attr('disabled',this.checked);$('#byRank,#byPlayer,#byQuli').attr('checked', false);$('#byRank_td,#byPlayer_td,#byQuli_td').hide();"> <label for="invite_all"><b>כולם</b></label></div></td>
				<td>
					<table border=0 width=95% align=center><tr><td align=center valign=top width=30%>
						<table border=1 style="border-collapse: collapse;" width=100%>
						<tr><td><input type=checkbox id="byRank" name="byRank" value="1" onClick="$('#byRank_td').toggle('display');" disabled> <label for="byRank">לפי דרגה</label> <span style="font-size: 11px;">(<a href=javascript:void(); onClick="$('input[name=rank_select[]]').attr('checked',true);">סמן הכל</a> / <a href=javascript:void(); onClick="$('input[name=rank_select[]]').attr('checked',false);">נקה הכל</a>)</span></td></tr><tr><td id="byRank_td" style="display: none;">
						<?php
						if ($result1 = $db->query("SELECT * FROM mngr_Ranks ORDER BY RankID"))
						{
							while ($row1 = $result1->fetch_assoc())
							{
								print "<input type=checkbox name='rank_select[]' value='".$row1['RankID']."'> ".$row1["Name"]."<BR>";
							}
							$result1->free();
						}
						?>
						</td></tr></table>
					</td><td align=center valign=top width=70%>
						<table border=1 style="border-collapse: collapse;" width=100%>
						<tr><td><input type=checkbox id='byPlayer' name="byPlayer" value="1" onClick="$('#byPlayer_td').toggle('display');" disabled> <label for="byPlayer">לפי שחקן</label> <span style="font-size: 11px;">(<a href=javascript:void(); onClick="$('input[name=specific_select[]]').attr('checked',true);">סמן הכל</a> / <a href=javascript:void(); onClick="$('input[name=specific_select[]]').attr('checked',false);">נקה הכל</a>)</span></td></tr><tr><td id="byPlayer_td" style="display: none;">
						<table border=0><tr><td>
						<?php
						if ($result1 = $db->query("SELECT * FROM mngr_Players WHERE Status<2"))
						{
							$num = $result1->num_rows;
							$num = ceil($num/2);
							$count = 1;
							while ($row1 = $result1->fetch_assoc())
							{
								print "<input type=checkbox name='specific_select[]' value='".$row1['JoomlaID']."'> ".PlayerName($row1["JoomlaID"]);
								if ($count==$num)
								{
									print "</td><td>";
								} else {
									print "<br>";
								}
								$count++;
							}
							$result1->free();
						}
						?>
						</td></tr></table>
						</td></tr></table>
					</td></tr>
					<tr><td align=center valign=top>
						<table border=1 style="border-collapse: collapse;" width=100%>
						<tr><td><input type=checkbox id="byQuli" name="byQuli" value="1" onClick="$('#byQuli_td').toggle('display');" disabled> <label for="byQuli">לפי הכשרה</label> <span style="font-size: 11px;">(<a href=javascript:void(); onClick="$('input[name=quli_select[]]').attr('checked',true);">סמן הכל</a> / <a href=javascript:void(); onClick="$('input[name=quli_select[]]').attr('checked',false);">נקה הכל</a>)</span></td></tr><tr><td id="byQuli_td" style="display: none;">
						<?php
						if ($result1 = $db->query("SELECT * FROM mngr_Quli ORDER BY QuliID"))
						{
							while ($row1 = $result1->fetch_assoc())
							{
								print "<input type=checkbox name='quli_select[]' value='".$row1['QuliID']."'> ".$row1["Name"]."<BR>";
							}
							$result1->free();
						}
						?>
						</td></tr></table>
					</td></tr></table>
				</td>
			</tr>
			<tr>
				<td>תפקידים פעילים:</td>
				<td>
					<?php
					if ($rs_duty = $db->query("SELECT * FROM mngr_Dutys ORDER BY DutyID"))
					{
						$dutysArr = array();
						while ($rw_duty = $rs_duty->fetch_assoc())
						{
							$dutysArr[$rw_duty["DutyID"]] = $rw_duty["Name"];
						}
					}
					$rs_duty->free();

					print "<div align=center><input type=radio name='countMethod' id='normal' value='normal' onClick=\"$('input[name*=\'grp\']').prop('disabled',true);\" checked> <label for='normal'><b>מגיע/אולי/לא מגיע</b></label> | ";
					print "<input type=radio name='countMethod' id='limited' value='limited' onClick=\"$('input[name*=\'grp\']').prop('disabled',true);\"> <label for='limited'><b>מגיע/אולי/לא מגיע כאשר ישנם </b></label> <input type=text name=countMethod_time style=\"width:40px;\"> שחקנים לכל היותר. | ";
					print "<input type=radio name='countMethod' id='dutys' value='dutys' onClick=\"$('input[name*=\'grp\']').prop('disabled',false);\"> <label for='dutys'><b>עפ\"י תפקידים</b></label></div><hr>";

					printDutysTable($infantryStruct,$dutysArr, 'normal');
					?>
				</td>
			</tr>
			
			<tr>
				<td colspan=2 align=center>
					<input type=submit value='צור אירוע'> <input type=button onClick="window.location.href='admin-events';" value='בטל'>
				</td>
			</tr>
			</table>
			</form>
			<br><br>
			<p align=center><input type=button onClick="window.location.href='admin-events';" value="חזור"></p>
		<?php
		}
		?>
		</div>
	</div>
</div>

<?php
	$db->close();
	}
?>

<?php include "footer.php"; ?>
