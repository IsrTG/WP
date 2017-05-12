<?php
/*
 * Template Name: isrtg_admin_players
 */
include "admin_Menu.php";
include "header.php";
?>

<?php
	if (PageRank($perm_Admin_Manager,"אינך מורשה להיכנס לעמוד זה")) {
	
	$db = db_Connect();
	$msg = getFieldg("msg",$db);
	
	$pid = getFieldg("pid",$db);
	
	if (getField("mode",$db)=="edit")
	{
		$pass = getField("pass",$db);
		$pname = GetValue("Nickname","mngr_Players","JoomlaID=".toDBp("pid",$db));
		
		if ($pname != toDBp("nick",$db)) {
			// Nickname has changed
			fm_setUsername(toDBp("pid",$db), toDBp("nick",$db));
		}
		
		if (toDBp("status",$db)==PLAYER_STATUS_KICKED && GetValue("Status","mngr_Players","JoomlaID=".toDBp("pid",$db))!=PLAYER_STATUS_KICKED) { //Change password for kicked player so he could not log back in. If Unkicked - manager needs to reset player's password and notify him
            fm_blockPlayerAccess(toDBp("pid",$db));
			addlog(fm_getSessionID(),"הדיח את השחקן ".$pname);
		}
		$db->query("UPDATE mngr_Players SET Nickname='".toDBp("nick",$db)."', Name='".toDBp("tname",$db)."', NameENG='".toDBp("nameeng",$db)."', BirthDate='".toDBp("tyear",$db)."-".toDBp("tmonth",$db)."-".toDBp("tday",$db)."', Email='".toDBp("email",$db)."', Skype='".toDBp("skype",$db)."', Steam='".toDBp("steam",$db)."', ArmaID='".toDBp("armaid",$db)."', RankID=".toDBp("rank",$db).", Status=".toDBp("status",$db).", Qulifications='".ArrayToMulti(toDBp('quli',$db))."', Badges='".ArrayToMulti(toDBp('badge',$db))."', RegInfo='".toDBp("reginfo",$db)."', Naat='".toDBp("naat",$db)."', Remarks='".toDBp("remarks",$db)."' WHERE JoomlaID=".toDBp("pid",$db));
		if ($pass) { //Need to change password
			fm_setPassword(toDBp("pid",$db), $pass);
			addlog(fm_getSessionID(),"ערך את פרטי השחקן ".$pname." ואיפס את סיסמתו");
		} else {
			addlog(fm_getSessionID(),"ערך את פרטי השחקן ".$pname);
		}
		header("Location: admin-players?msg=1");
	}
	
	if (getField("mode",$db)=="pass")
	{
		$pname = GetValue("Nickname","mngr_Players","JoomlaID=".toDBp("pid",$db));
		$db->query("UPDATE mngr_Players SET Nickname='".toDBp("nick",$db)."', Name='".toDBp("tname",$db)."', NameENG='".toDBp("nameeng",$db)."', BirthDate='".toDBp("tyear",$db)."-".toDBp("tmonth",$db)."-".toDBp("tday",$db)."', Email='".toDBp("email",$db)."', Skype='".toDBp("skype",$db)."', Steam='".toDBp("steam",$db)."', ArmaID='".toDBp("armaid",$db)."', RankID=".toDBp("rank",$db).", Status=".toDBp("status",$db).", Qulifications='".ArrayToMulti(toDBp('quli',$db))."', Badges='".ArrayToMulti(toDBp('badge',$db))."', RegInfo='".toDBp("reginfo",$db)."', Naat='".toDBp("naat",$db)."' WHERE JoomlaID=".toDBp("pid",$db));
		addlog(fm_getSessionID(),"ערך את פרטי השחקן ".$pname);
		header("Location: admin-players?msg=1");
	}
	
	if (getField("action",$db)=="interview" && getField("newid",$db)!="") //Proceed new player to interview
	{
		$pname = GetValue("Nickname","mngr_Players","JoomlaID=".toDBp("newid",$db));
		$email = GetValue("Email","mngr_Players","JoomlaID=".toDBp("newid",$db));
		$db->query("UPDATE mngr_Players SET Status=4 WHERE JoomlaID=".toDBp("newid",$db));
		sendmail($email,"הצטרפות לקלאן - ראיון אישי",$email_body_player_to_interview);
		addlog(fm_getSessionID(),"הזמין את השחקן ".$pname." לראיון קבלה");
		header("Location: admin-players?msg=2");
	}
	
	if (getField("action",$db)=="approve" && getField("newid",$db)!="") //Approve new player
	{
        header("Location: admin-players"); // TODO: Fix
		$pname = GetValue("Nickname","mngr_Players","JoomlaID=".toDBp("newid",$db));
		$email = GetValue("Email","mngr_Players","JoomlaID=".toDBp("newid",$db));
		$db->query("UPDATE mngr_Players SET Status=0 WHERE JoomlaID=".toDBp("newid",$db));
		//add joomla permissions to access site
		$db->query("INSERT INTO ".JOOMLA_PREFIX."_user_usergroup_map (user_id,group_id) VALUES (".toDBp("newid",$db).",2)");
		sendmail($email,"הצטרפות לקלאן - הבקשה אושרה",$email_body_player_approved);
		addlog(fm_getSessionID(),"אישר את השחקן ".$pname." (".getField("newid",$db).") לקלאן");
		header("Location: admin-players?msg=3");
	}
	
	
	
	if (getField("action",$db)=="decline" && getField("newid",$db)!="") //Decline new player
	{
		//set player to disqualified
		$pname = GetValue("Nickname","mngr_Players","JoomlaID=".toDBp("newid",$db));
		$email = GetValue("Email","mngr_Players","JoomlaID=".toDBp("newid",$db));
		$status = GetValue("Status","mngr_Players","JoomlaID=".toDBp("newid",$db));
		$db->query("UPDATE mngr_Players SET Status=3 WHERE JoomlaID=".toDBp("newid",$db));
		if ($status==5)
		{
			sendmail($email,"הצטרפות לקלאן - הבקשה נדחתה",$email_body_player_decline_before_interview);
			addlog(fm_getSessionID(),"דחה את השחקן ".$pname." מהצטרפות לקלאן");
			header("Location: admin-players?msg=4");
		}
		if ($status==4)
		{
			sendmail($email,"הצטרפות לקלאן - הבקשה נדחתה",$email_body_player_decline);
			addlog(fm_getSessionID(),"דחה את השחקן ".$pname." מהצטרפות לקלאן");
			header("Location: admin-players?msg=4");
		}
		print "סטטוס לא מוכר";
	}
	
	if (getField("action",$db)=="delete" && getField("newid",$db)!="") //delete new player
	{
        header("Location: admin-players"); // TODO: Fix
		$pname = GetValue("Nickname","mngr_Players","JoomlaID=".toDBp("newid",$db));
		$db->query("DELETE FROM mngr_Players WHERE JoomlaID=".toDBp("newid",$db));
		$db->query("DELETE FROM ".JOOMLA_PREFIX."_users WHERE id=".toDBp("newid",$db));
		addlog(fm_getSessionID(),"מחק את השחקן ".$pname." מתהליך ההצטרפות לקלאן");
		header("Location: admin-players?msg=5");
	}
	
?>
<html dir=rtl>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>

<script language=javascript>
	function playerSel(id)
	{
		if (id)
		{
			window.location.href='?pid='+id;
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
<body>

<div id="IsrTG_Content">
<?php
print("$MenuToPrint");
?>

<div id="Player_Managment">
	<div class='Title'>
		<h2>ניהול שחקנים</h1>
	</div>
	
	<div class="MainContent">
		<center><a href=#1>ממתינים לראיון</a> | <a href=#2>השחקנים המקצועיים ביותר עפ"י תפקיד</a> | <a href=#4>לא מתפקדים סדרתיים (החודש)</a> | <a href=#5>מעקב חוסר פעילות</a> | <a href=#6>שחקנים לא פעילים</a> | <a href=#7>מודחים</a></center><br><br><br>
		<?php 
		if ($msg==1) print "<div style='font-weight:bold;text-align:center;color:green;'>הפרטים עודכנו בהצלחה</div><br>"; 
		if ($msg==2) print "<div style='font-weight:bold;text-align:center;color:green;'>השחקן הוזמן לראיון בהצלחה</div><br>";
		if ($msg==3) print "<div style='font-weight:bold;text-align:center;color:green;'>השחקן אושר בהצלחה</div><br>";
		if ($msg==4) print "<div style='font-weight:bold;text-align:center;color:green;'>השחקן נדחה בהצלחה</div><br>";
		if ($msg==5) print "<div style='font-weight:bold;text-align:center;color:green;'>השחקן נמחק בהצלחה</div><br>";
		?>
		<form method=post>
		<table border=1 style="border-collapse: collapse;" align=center width=60%>
		<tr>
			<td colspan=2 style="font-weight:bold;text-align:center;">
			עריכת פרטי שחקן בסטטוס מודח:
			<select onChange="playerSel(this.options[this.selectedIndex].value);" <?php if (!PageRank($perm_Admin_Manager,"")) print "disabled"?>><option value="">בחר שחקן</option>
			<?php
				$options = '';
				if ($result = $db->query("SELECT * FROM mngr_Players WHERE Status=3 ORDER BY Status, Name"))
				{
					while ($row = $result->fetch_assoc())
					{
						if ($row['JoomlaID']==$pid)
						{
							$options .= "<option value=".$row['JoomlaID']." selected>".PlayerName($row['JoomlaID'])."</option>";
						} else {
							$options .= "<option value=".$row['JoomlaID'].">".PlayerName($row['JoomlaID'])."</option>";
						}
					}
					$result->free();
				}
				print $options;
			?>
			</select></td>
		</tr>
		</table>
		</form><br><br>
		
		<form method=post>
		<table border=1 style="border-collapse: collapse;" align=center width=60%>
		<tr>
			<td colspan=2 style="font-weight:bold;text-align:center;">
			עריכת פרטי שחקן בסטטוס לא פעיל:
			<select onChange="playerSel(this.options[this.selectedIndex].value);" <?php if (!PageRank($perm_Admin_Manager,"")) print "disabled"?>><option value="">בחר שחקן</option>
			<?php
				$options = '';
				if ($result = $db->query("SELECT * FROM mngr_Players WHERE Status=2 ORDER BY Status, Name"))
				{
					while ($row = $result->fetch_assoc())
					{
						if ($row['JoomlaID']==$pid)
						{
							$options .= "<option value=".$row['JoomlaID']." selected>".PlayerName($row['JoomlaID'])."</option>";
						} else {
							$options .= "<option value=".$row['JoomlaID'].">".PlayerName($row['JoomlaID'])."</option>";
						}
					}
					$result->free();
				}
				print $options;
			?>
			</select></td>
		</tr>
		</table>
		</form><br><br>
		
		<form method=post id="tform"><input type=hidden name=mode value='edit'><input type=hidden name=pid value='<?php echo $pid;?>'>
		<table border=1 style="border-collapse: collapse;" align=center width=60%>
		<tr>
			<td colspan=2 style="font-weight:bold;text-align:center;">
			עריכת פרטי שחקן:
			<select name="player" onChange="playerSel(this.options[this.selectedIndex].value);" <?php if (!PageRank($perm_Admin_Manager,"")) print "disabled"?>><option value="">בחר שחקן</option>
			<?php
				print options_Players($pid);
			?>
			</select></td>
		</tr>
		<?php
		if ($pid=='')
		{
		?>
		</table>
		</form><br>
		
		<br><br><a name=1></a><center><h3 style="display:inline;">ממתינים לראיון</h3> (<a href=#>למעלה</a>)</center><br>
		
		<?php
			if ($result = $db->query("SELECT * FROM mngr_Players p WHERE Status=4 OR Status=5 ORDER BY Status DESC, JoomlaID DESC"))
			{
				while ($row = $result->fetch_assoc())
				{
					$ArmaID_AlreadyExists = GetValue("JoomlaID","mngr_Players","ArmaID='".$row["ArmaID"]."' AND JoomlaID!=".$row["JoomlaID"]);
					$diff = date_diff(date_create(),date_create($row['BirthDate']));
					print "<table border=1 style='border-collapse: collapse;' align=center width=40%>";
					print "<tr><td style='font-weight:bold;text-align:center;'>כינוי</td><td style='font-weight:bold;text-align:center;'>שם</td><td style='font-weight:bold;text-align:center;'>סטטוס</td></tr>";
					print "<tr><td style='text-align:center;'>".$row["Nickname"]."</td><td style='text-align:center;'>".$row["Name"]."</td><td style='text-align:center;'>".PlayerStatus($row["Status"])."</td></tr>";
					print "<tr><td colspan=3><u><b>פרטי הרשמה:</b></u><br>";
					print "תאריך הרשמה: ".DisplayDate($row["JoinDate"])."<BR>";
					print "תאריך לידה: ".DisplayDate($row["BirthDate"])." (".$diff->format("%Y").")<BR>";
					if ($ArmaID_AlreadyExists=='') //Check if ArmaID is already in system
						print "קוד ארמא: ".$row["ArmaID"]."<BR>";
					else {
						$ArmaID_ExistedUser = GetValue("Nickname","mngr_Players","JoomlaID=".$ArmaID_AlreadyExists);
						print "<font color=red><b>קוד ארמא: ".$row["ArmaID"]." - כבר קיים במערכת (<a href='roster?pid=".$ArmaID_AlreadyExists."'>".$ArmaID_ExistedUser."</a>)</b></font><BR>";
					}
					print "אימייל: ".$row["Email"]."<BR>";
					print "סקייפ: ".$row["Skype"]."<BR>";
					print "סטים: ".$row["Steam"]."<BR><BR>".$row["RegInfo"]."<br></td></tr>";
					print "<tr><td>בחר פעולה:</td><td style='text-align:center;' colspan=2>";
					switch (playerStatus($row["Status"]))
					{
						case "נרשם חדש":
							print "<form method=post style='display: inline;' onSubmit=\"if (!confirm('האם אתה בטוח שברצונך לזמן את ".$row["Name"]." לראיון?')) return false;\"><input type=hidden name=action value='interview'><input type=hidden name=newid value='".$row["JoomlaID"]."'><input type=submit value='      זמן שחקן לראיון      '></form><br><br>";
							break;
					}
					print "<form method=post style='display: inline;' onSubmit=\"if (!confirm('האם אתה בטוח שברצונך לאשר את השחקן ".$row["Name"]."?')) return false;\"><input type=hidden name=action value='approve'><input type=hidden name=newid value='".$row["JoomlaID"]."'><input type=submit value='      אשר שחקן וצרף לקלאן      '></form> <form method=post style='display: inline;' onSubmit=\"if (!confirm('האם אתה בטוח שברצונך לדחות את השחקן ".$row["Name"]."?')) return false;\"><input type=hidden name=action value='decline'><input type=hidden name=newid value='".$row["JoomlaID"]."'><input type=submit value='דחה שחקן'></form><br><form method=post style='display: inline;' onSubmit=\"if (!confirm('האם אתה בטוח שברצונך למחוק את השחקן ".$row["Name"]." ללא תגובה?')) return false;\"><input type=hidden name=action value='delete'><input type=hidden name=newid value='".$row["JoomlaID"]."'><input type=submit value='מחק ללא תגובה'></form></td></tr></table><br>";
				}
				$result->free();
			}
		?>
		
		<br><br><a name=2></a><center><h3 style="display:inline;">השחקנים המקצועיים ביותר עפ"י תפקיד (3 חודשים אחרונים)</h3> (<a href=#>למעלה</a>)</center><br>
		<table border=1 style="border-collapse: collapse;" align=center width=50%>
		<tr><td style="font-weight:bold;text-align:center;">כינוי</td><td style="font-weight:bold;text-align:center;">שם</td><td style="font-weight:bold;text-align:center;">כמות אירועים</td></tr>
		<?php
			$sixMonthsAgo = date('Y-m-d', strtotime('-3 month'));
			if ($result = $db->query("SELECT * FROM mngr_Dutys ORDER BY DutyID"))
			{
				while ($row = $result->fetch_assoc())
				{
					print "<tr><td colspan=3 style='font-weight:bold;text-align:center;background-color:#808080;'>".$row["Name"]."</td></tr>";
					//show top 5 players in specific duty
					//OLD SQL - SELECT DutyID,e.JoomlaID,p.Nickname,p.Name,Count(e.JoomlaID) AS EventsNumber FROM mngr_EventsLog e INNER JOIN mngr_Players p ON e.JoomlaID = p.JoomlaID WHERE DutyID=".$row["DutyID"]." AND Panelty=0 GROUP BY JoomlaID ORDER BY DutyID, EventsNumber DESC, RankID LIMIT 5
					if ($result1 = $db->query("SELECT DutyID,el.JoomlaID,p.Nickname,p.Name,Count(el.JoomlaID) AS EventsNumber,e.EventDate FROM mngr_EventsLog el INNER JOIN mngr_Players p ON el.JoomlaID = p.JoomlaID INNER JOIN mngr_Events e ON el.EventID = e.EventID WHERE e.EventDate > '".$sixMonthsAgo."' AND DutyID=".$row["DutyID"]." AND Panelty=0 AND p.Status<".PLAYER_STATUS_NOT_ACTIVE." GROUP BY JoomlaID ORDER BY DutyID, EventsNumber DESC, RankID LIMIT 5"))
					{
						if ($result1->num_rows==0)
						{
							print "<tr><td colspan=3>לא נמצאו שחקנים בתקופה זו.</td></tr>";
						}
						while ($row1 = $result1->fetch_assoc())
						{
							print "<tr><td style='text-align:center;'><a href='roster?pid=".$row1["JoomlaID"]."'><font color=#8DE02E>".$row1["Nickname"]."</font></a></td><td style='text-align:center;'>".$row1["Name"]."</td><td style='text-align:center;'>".$row1["EventsNumber"]."</td></tr>";
						}
						$result1->free();
					}
				}
				$result->free();
			}
		?>
		</table>
		
		
		<br><br><a name=4></a><center><h3 style="display:inline;">לא מתפקדים סדרתיים (החודש) - מעל 3</h3> (<a href=#>למעלה</a>)</center><br>
		<table border=1 style="border-collapse: collapse;" align=center width=30%>
		<tr><td style="font-weight:bold;text-align:center;">כינוי</td><td style="font-weight:bold;text-align:center;">שם</td><td style="font-weight:bold;text-align:center;">מספר אירועים שלא התפקד</td></tr>
		<?php
			$monthAgo = date('Y-m-d', strtotime('-1 month'));
			if ($result = $db->query("SELECT * FROM mngr_Events e WHERE Status=1 AND EventDate>'".$monthAgo."'"))
			{
				while ($row = $result->fetch_assoc())
				{
					$wanted = MultiToArray(EventWantedPlayers($row['EventID']));
					if (count($wanted)>0)
					{
						for ($i=0;$i<count($wanted);$i++)
						{
							if (SearchInMulti($row['PlayersAccept'],$wanted[$i])==false && SearchInMulti($row['PlayersMaybe'],$wanted[$i])==false && SearchInMulti($row['PlayersDecline'],$wanted[$i])==false)
							{
								if (!isset($IgnoreCount[$wanted[$i]])) $IgnoreCount[$wanted[$i]] = 0;
								$IgnoreCount[$wanted[$i]]++;
							}
						}
					}
				}
				$result->free();
			}

			if ($IgnoreCount != null) {
                arsort($IgnoreCount);
                foreach ($IgnoreCount as $player => $count) {
                    if ($count > 3) {
                        if ($result = $db->query("SELECT * FROM mngr_Players WHERE JoomlaID=" . $player)) {
                            $row = $result->fetch_assoc();
                            print "<tr><td style='text-align:center;'><a href='?pid=" . $player . "'><font color=#8DE02E>" . $row["Nickname"] . "</font></a></td><td style='text-align:center;'>" . $row["Name"] . "</td><td style='text-align:center;'>" . $count . "</td></tr>";
                            $result->free();
                        }
                    }
                }
            }
		?>
		</table>
		
		<br><br><a name=5></a><center><h3 style="display:inline;">מעקב חוסר פעילות</h3> (<a href=#>למעלה</a>)</center><br>
		<table border=1 style="border-collapse: collapse;" align=center width=40%>
		<tr><td style="font-weight:bold;text-align:center;">כינוי</td><td style="font-weight:bold;text-align:center;">שם</td><td style="font-weight:bold;text-align:center;">ת. אירוע אחרון*</td><td style="font-weight:bold;text-align:center;">ימים ללא פעילות</td><td style="font-weight:bold;text-align:center;">סטטוס</td></tr>
		<?php
			if ($result = $db->query("SELECT *,(SELECT Events.EventDate FROM mngr_EventsLog Log JOIN mngr_Events Events ON Log.EventID = Events.EventID WHERE Log.JoomlaID=p.JoomlaID ORDER BY Events.EventDate DESC LIMIT 1) AS 'LastEvent' FROM mngr_Players p WHERE p.Status<2 ORDER BY LastEvent"))
			{
				while ($row = $result->fetch_assoc())
				{
					if ($row['LastEvent']=="")
					{
						$tdate = $row['JoinDate'];
					} else {
						$tdate = $row['LastEvent'];
					}
					$tmp = date_diff(date_create($tdate),date_create());
					$daysUnactive[$row["JoomlaID"]] = $tmp->format("%d");
					$lastDate[$row["JoomlaID"]] = displayDate($tdate);
				}
				$result->free();
			}			
			arsort($daysUnactive);
			
			foreach ($daysUnactive as $player=>$count)
			{
				if ($count>10)
				{
					if ($result = $db->query("SELECT * FROM mngr_Players WHERE JoomlaID=".$player))
					{
						$row = $result->fetch_assoc();
						print "<tr><td style='text-align:center;'><a href='?pid=".$player."'><font color=#8DE02E>".$row["Nickname"]."</font></a></td><td style='text-align:center;'>".$row["Name"]."</td><td style='text-align:center;'>".$lastDate[$player]."</td><td style='text-align:center;'>".$count."</td><td style='text-align:center;'>".playerStatus($row["Status"])."</td></tr>";
					}
				}
			}
		?>
		</table><div style="text-align:center;font-size:12px;">* במידה ואין אירועים לשחקן, יחושב עפ"י תאריך הצטרפותו לקלאן.</div>
		
		<br><br><a name=6></a><center><h3 style="display:inline;">שחקנים לא פעילים</h3> (<a href=#>למעלה</a>)</center><br>
		<table border=1 style="border-collapse: collapse;" align=center width=40%>
		<tr><td style="font-weight:bold;text-align:center;">כינוי</td><td style="font-weight:bold;text-align:center;">שם</td><td style="font-weight:bold;text-align:center;">ת. אירוע אחרון*</td><td style="font-weight:bold;text-align:center;">ימים ללא פעילות</td><td style="font-weight:bold;text-align:center;">סטטוס</td></tr>
		<?php
			if ($result = $db->query("SELECT *,(SELECT Events.EventDate FROM mngr_EventsLog Log JOIN mngr_Events Events ON Log.EventID = Events.EventID WHERE Log.JoomlaID=p.JoomlaID ORDER BY Events.EventDate DESC LIMIT 1) AS 'LastEvent' FROM mngr_Players p WHERE Status=2 ORDER BY LastEvent"))
			{
				while ($row = $result->fetch_assoc())
				{
					if ($row['LastEvent']=="")
					{
						$tdate = $row['JoinDate'];
					} else {
						$tdate = $row['LastEvent'];
					}
					$daysUnactive = date_diff(date_create($tdate),date_create());
					print "<tr><td style='text-align:center;'><a href='?pid=".$row['JoomlaID']."'><font color=#8DE02E>".$row["Nickname"]."</font></a></td><td style='text-align:center;'>".$row["Name"]."</td><td style='text-align:center;'>".displayDate($tdate)."</td><td style='text-align:center;'>".$daysUnactive->format("%d")."</td><td style='text-align:center;'>".playerStatus($row["Status"])."</td></tr>";
				}
				$result->free();
			}
		?>
		</table><div style="text-align:center;font-size:12px;">* במידה ואין אירועים לשחקן, יחושב עפ"י תאריך הצטרפותו לקלאן.</div>
		
		<br><br><p align=center><input type=button onClick="window.location.href='admin';" value='חזור'></p>
		<?php
		} else {
		if (!PageRank($perm_Admin_Manager,"אינך מורשה להיכנס לעמוד זה")) 
		{
			print "</table>";
			print "</form>";
		} else {
		unset($result);
		if (!$result = $db->query("SELECT * FROM mngr_Players WHERE JoomlaID=".$pid))
		{
			print "Player not found!";
			exit;
		}
		$row = $result->fetch_assoc();
		?>
		<script language="javascript">
		function updatePlayer() {
			if (isDate(document.getElementById('tday').value+'/'+document.getElementById('tmonth').value+'/'+document.getElementById('tyear').value)) {
				//if (tinyMCE.get('jform_articletext').getContent()=='' && $("select[name='status']").val()=='3') {
					//alert('חובה להכניס סיבת הדחה');
				//} else {
					tform.submit();
				//}
			} else {
				alert('תאריך לא חוקי');
			}
		}
		</script>
		<tr>
			<td>כינוי:</td>
			<td><input type=text name=nick value='<?php echo $row['Nickname']?>' size=30 dir=ltr></td>
		</tr>
		<tr>
			<td>שם משתמש:</td>
			<td><?php echo fm_getUsername($row["JoomlaID"])?></td>
		</tr>
		<tr>
			<td>איפוס סיסמא:<br>(מלא רק במידה ונדרש)</td>
			<td><input type=text name=pass size=30 dir=ltr></td>
		</tr>
		<tr>
			<td>שם:</td>
			<td><input type=text name=tname value='<?php echo $row['Name']?>' size=30></td>
		</tr>
		<tr>
			<td>שם פרטי באנגלית:</td>
			<td><input type=text name=nameeng value='<?php echo $row['NameENG']?>' size=30 dir=ltr></td>
		</tr>
		<tr>
			<td width=20%>תאריך לידה:</td>
			<td>
			<select name=tyear id=tyear>
			<?php
			$minAge = 14;
			$maxAge = 60;
			$bDay = $row['BirthDate'];
			for ($i=date("Y")-$minAge; $i>=date("Y")-$maxAge; $i--)
			{
				$sel="";
				if ($i==date_format(date_create($bDay),"Y")) $sel=" selected";
				print "<option value='".$i."'".$sel.">".$i."</option>";
			}
			?>
			</select> <select name=tmonth id=tmonth>
			<?php
			for ($i=1; $i<=12; $i++)
			{
				$sel="";
				if ($i==date_format(date_create($bDay),"n")) $sel=" selected";
				print "<option value='".$i."'".$sel.">".$i."</option>";
			}
			?>
			</select> <select name=tday id=tday>
			<?php
			for ($i=1; $i<=31; $i++)
			{
				$sel="";
				if ($i==date_format(date_create($bDay),"j")) $sel=" selected";
				print "<option value='".$i."'".$sel.">".$i."</option>";
			}
			?>
			</select> 
			</td>
		</tr>
		<tr>
			<td>אימייל:</td>
			<td><input type=text name=email value='<?php echo $row['Email']?>' size=30 dir=ltr></td>
		</tr>
		<tr>
			<td>סקייפ:</td>
			<td><input type=text name=skype value='<?php echo $row['Skype']?>' size=30 dir=ltr></td>
		</tr>
		<tr>
			<td>סטים:</td>
			<td><input type=text name=steam value='<?php echo $row['Steam']?>' size=30 dir=ltr></td>
		</tr>
		<tr>
			<td>קוד שחקן:</td>
			<td><input type=text name=armaid value='<?php echo $row['ArmaID']?>'></td>
		</tr>
		<tr>
			<td>דרגה:</td>
			<td>
			<select name=rank>
			<?php echo options_Ranks($row['RankID']); ?>
			</select>
			</td>
		</tr>
		<tr>
			<td>הכשרות:</td>
			<td>
			<?php
				if ($result1 = $db->query("SELECT * FROM mngr_Quli"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						if (SearchInMulti($row['Qulifications'],$row1['QuliID']))
						{
							print "<input type=checkbox name='quli[]' value=".$row1['QuliID']." checked> ".$row1['Name']."<br>";
						} else {
							print "<input type=checkbox name='quli[]' value=".$row1['QuliID']."> ".$row1['Name']."<br>";
						}
					}
				}
				$result1->free();
			?>
			</td>
		</tr>
		<tr>
			<td>עיטורים:</td>
			<td>
			<?php
				
				if ($result1 = $db->query("SELECT * FROM mngr_Badges"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
						if (SearchInMulti($row['Badges'],$row1['BadgeID']))
						{
							print "<input type=checkbox name='badge[]' value=".$row1['BadgeID']." checked> ".$row1['Name']."<br>";
						} else {
							print "<input type=checkbox name='badge[]' value=".$row1['BadgeID']."> ".$row1['Name']."<br>";
						}
					}
				}
				$result1->free();
			?>
			</td>
		</tr>
		<tr>
			<td>סטטוס:</td>
			<td>
			<select name=status>
				<option value=0 <?php if (playerStatus($row['Status'])=="פעיל") echo "selected";?>>פעיל</option>
				<option value=1 <?php if (playerStatus($row['Status'])=="חופשה") echo "selected";?>>חופשה</option>
				<option value=2 <?php if (playerStatus($row['Status'])=="לא פעיל") echo "selected";?>>לא פעיל</option>
				<option value=3 <?php if (playerStatus($row['Status'])=="הודח") echo "selected";?>>הודח</option>
			</select>
			</td>
		</tr>
		<tr>
			<td>תפקידים נוספים בקלאן:</td>
			<td><input type=text name=naat value='<?php echo $row['Naat']?>' size=30></td>
		</tr>
		<tr>
			<td>הערות למנהלים / סיבת הדחה:</td>
			<td><textarea id="jform_articletext" name="remarks" cols="0" rows="0" style="width:100%;height:250px;"><?php print $row["Remarks"]; ?></textarea></td>
		</tr>
		<tr>
			<td>פרטי הרשמה:</td>
			<td><?php echo $row['RegInfo']?></td>
		</tr>
		<tr>
			<td>תאריך הצטרפות:</td>
			<td><?php echo displayDate($row['JoinDate'])?></td>
		</tr>
		<tr>
			<td colspan=2 align=center><input type=button onClick="updatePlayer();" value='עדכן פרטים'></td>
		</tr>
		<tr>
			<td colspan=2 style="font-weight:bold;text-align:center;">נתוני תפקוד השחקן:</td>
		</tr>
		<tr>
			<td>פעילות השחקן:</td>
			<td>מרווח ממוצע בין משימות שישי בשבועות<br>(כמה שיותר קטן יותר טוב)</td>
		</tr>
		<tr>
			<?php
				$tmp = '';
				$ttl = 0;
				if ($result1 = $db->query("SELECT mngr_EventsLog.*,mngr_Events.Type,mngr_Events.EventDate FROM mngr_EventsLog INNER JOIN mngr_Events ON mngr_EventsLog.EventID = mngr_Events.EventID WHERE Type='משימת שישי' AND JoomlaID=".$row['JoomlaID']))
				{
					while ($row1 = $result1->fetch_assoc())
					{
					$ev_name = GetValue("Name","mngr_Events","EventID=".$row1['EventID']);
					$ev_date = GetValue("EventDate","mngr_Events","EventID=".$row1['EventID']);
					$tmp .= "<img src=/mngr/images/sword.png width=35 title='שם: ".$ev_name."\nמועד: ".$ev_date."'> ";
					$ttl++;
					}
				}
				$result1->free();
				print "<td>משימות שישי (".$ttl."):</td>";
				print "<td>".$tmp."</td>";
			?>
		</tr>
		<tr>
			<?php
				$tmp = '';
				$ttl = 0;
				if ($result1 = $db->query("SELECT mngr_EventsLog.*,mngr_Events.Type,mngr_Events.EventDate FROM mngr_EventsLog INNER JOIN mngr_Events ON mngr_EventsLog.EventID = mngr_Events.EventID WHERE Type='אימון' AND Panelty=False AND JoomlaID=".$row['JoomlaID']))
				{
					while ($row1 = $result1->fetch_assoc())
					{
					$ev_name = GetValue("Name","mngr_Events","EventID=".$row1['EventID']);
					$ev_date = GetValue("EventDate","mngr_Events","EventID=".$row1['EventID']);
					$tmp .= "<img src=/mngr/images/training.png width=35 title='שם: ".$ev_name."\nמועד: ".$ev_date."'> ";
					$ttl++;
					}
				}
				$result1->free();
				print "<td>אימונים (".$ttl."):</td>";
				print "<td>".$tmp."</td>";
			?>
		</tr>
		<tr>
			<?php
				$tmp = '';
				$ttl = 0;
				if ($result1 = $db->query("SELECT mngr_EventsLog.*,mngr_Events.Type,mngr_Events.EventDate FROM mngr_EventsLog INNER JOIN mngr_Events ON mngr_EventsLog.EventID = mngr_Events.EventID WHERE Panelty=True AND JoomlaID=".$row['JoomlaID']." ORDER BY mngr_Events.EventDate"))
				{
					while ($row1 = $result1->fetch_assoc())
					{
					$ev_name = GetValue("Name","mngr_Events","EventID=".$row1['EventID']);
					$ev_type = GetValue("Type","mngr_Events","EventID=".$row1['EventID']);
					$ev_date = GetValue("EventDate","mngr_Events","EventID=".$row1['EventID']);
					$tmp .= "<img src=/mngr/images/warning.png width=35 title='שם: ".$ev_name."\nסוג: ".$ev_type."\nמועד: ".$ev_date."'> ";
					$ttl++;
					}
				}
				$result1->free();
				print "<td>נקודות אזהרה (".$ttl."):</td>";
				print "<td>".$tmp."</td>";
			?>
		</tr>
		</table>
		</form>
		
		<?php $result->free(); }
		print "<br><br><p align=center><input type=button onClick=\"window.location.href='admin-players';\" value='חזור'></p>";
		}?>
	</div>
</div>
</body>
</html>

<?php
	$db->close();
	}
?>

<?php include "footer.php"; ?>
