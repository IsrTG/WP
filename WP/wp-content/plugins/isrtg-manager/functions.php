<?php
	include 'ClassDutyEquipment.php';

	//General
	function getField($field,$db="")
	{
		if (isset($_POST[$field]))
		{
			if ($db!="" && !is_array($_POST[$field]))
				return $db->real_escape_string($_POST[$field]);
			else
				return $_POST[$field];
		}
		return '';
	}
	
	function getFieldg($field,$db="") {
		if (isset($_GET[$field])) {
			if ($db!="" && !is_array($_GET[$field])) {
				return $db->real_escape_string($_GET[$field]);
			} else  {
				return $_GET[$field];
			}
		}
		return '';
	}

	function preparetodb($txt)
	{
		$tmp = $txt;
		$tmp = str_replace("'","`",$tmp);
		$tmp = str_replace("\"","&#34;",$tmp);
		$tmp = str_replace("|","&#124;",$tmp);
		$tmp = str_replace("\r\n","<br>",$tmp);
		return $tmp;
	}
	
	function fromdb($txt)
	{
		$tmp = $txt;
		$tmp = str_replace("&#39;","'",$tmp);
		$tmp = str_replace("`","'",$tmp);
		$tmp = str_replace("&#34;","\"",$tmp);
		$tmp = str_replace("&#124;","|",$tmp);
		$tmp = str_replace("<br>","\r\n",$tmp);
		return $tmp;
	}
	
	function fromdbprint($txt)
	{
		$tmp = $txt;
		$tmp = str_replace("`","'",$tmp);
		return $tmp;
	}
	
	function toDB($str)
	{
		if (isset($str))
		{
			return preparetodb($str);
		}
		return '';
	}
	
	function toDBp($field,$db="")
	{
		if (isset($_POST[$field]))
		{
			if ($db!="" && !is_array($_POST[$field]))
				return preparetodb($db->real_escape_string($_POST[$field]));
			else
				return preparetodb($_POST[$field]);
		}
		return '';
	}
	
	function toDBg($field,$db="")
	{
		if (isset($_GET[$field]))
		{
			if ($db!="" && !is_array($_GET[$field]))
				return preparetodb($db->real_escape_string($_GET[$field]));
			else
				return preparetodb($_GET[$field]);
		}
		return '';
	}

	function toTip($txt)
	{
		$tmp = $txt;
		$tmp = str_replace("'","`",$tmp);
		$tmp = str_replace("\'","`",$tmp);
		$tmp = str_replace("&#39;","`",$tmp);
		$tmp = str_replace("\"","&#34;",$tmp);
		$tmp = str_replace("&#8203;","",$tmp);
		return $tmp;
	}
	
	//Date
	function displayDate($dbdate)
	{
		$tmp = '';
		$tmp = date_format(date_create($dbdate),"d")."/".date_format(date_create($dbdate),"m")."/".date_format(date_create($dbdate),"Y");
		return $tmp;
	}
	
	function displayTime($dbdate)
	{
		$tmp = '';
		$tmp = date_format(date_create($dbdate),"H").":".date_format(date_create($dbdate),"i");
		return $tmp;
	}
	
	function displayFullDate($dbdate,$format='d/m/Y H:i')
	{
		$tmp = '';
		$tmp = date_format(date_create($dbdate),$format);
		//$tmp = date_format(date_create($dbdate),"d")."/".date_format(date_create($dbdate),"m")."/".date_format(date_create($dbdate),"Y")." ".date_format(date_create($dbdate),"H").":".date_format(date_create($dbdate),"i");
		return $tmp;
	}
	
	function date_now()
	{
		$dateObj = DateTime::createFromFormat("Y-m-d H:i:s", date("Y")."-".date("m")."-".date("d")." ".date("H").":".date("i").":".date("s"));
		$dateObj->modify(getSetting("HourDifference")." hours");
		return $dateObj;
	}

	function dateObj($tdate)
	{
		$dateObj = DateTime::createFromFormat("Y-m-d H:i:s", $tdate);
		return $dateObj;
	}
	
	function MonthName($num)
	{
		switch ($num)
		{
			case 1:
				$tmp = "ינואר";
				break;
			case 2:
				$tmp = "פברואר";
				break;
			case 3:
				$tmp = "מרץ";
				break;
			case 4:
				$tmp = "אפריל";
				break;
			case 5:
				$tmp = "מאי";
				break;
			case 6:
				$tmp = "יוני";
				break;
			case 7:
				$tmp = "יולי";
				break;
			case 8:
				$tmp = "אוגוסט";
				break;
			case 9:
				$tmp = "ספטמבר";
				break;
			case 10:
				$tmp = "אוקטובר";
				break;
			case 11:
				$tmp = "נובמבר";
				break;
			case 12:
				$tmp = "דצמבר";
				break;
		}
		return $tmp;
	}
	
	//Wordpress
	if (defined("FM_WORDPRESS")) {
		function fm_getSessionID() {
			return get_current_user_id();
		}

        function fm_getUsername($pid)
        {
            $user = get_user_by('id', $pid);
            return $user->user_login;
        }

        function fm_setUsername($pid, $newUser)
        {
            wp_update_user( array ( 'ID' => $pid, 'user_login' => $newUser ) );
        }

        function fm_setPassword($pid, $newPass)
        {
            wp_update_user( array ( 'ID' => $pid, 'user_pass' => $newPass ) );
        }

        function fm_blockPlayerAccess($pid)
        {
            fm_setPassword($pid, "defKickedPlayer@#$%@^#^PasswordThisIsVerySecret^%$#&@*%");
        }
	}

	//Joomla
	if (defined("FM_JOOMLA")) {
		function EncodePassword($pass,$salt=null)
		{
			if ($salt==null)
			{
				for ($i=0; $i<=32; $i++)
				{
					$d=rand(1,30)%2;
					$salt .= $d ? chr(rand(65,90)) : chr(rand(48,57));
				}
			} else {
				$salt = $salt;
			}
			$hashed = md5($pass . $salt);
			$encrypted = $hashed . ':' . $salt;
			return $encrypted;
		}
		
		function fm_getSessionID()
		{
			if (defined('JPATH_BASE')) {
				require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
				require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
				$mainframe =& JFactory::getApplication('site');
				$user =& JFactory::getUser();
				$tmp = $user->id;
				if ($tmp=='') $tmp = 0;
				return $tmp;
			}
			return 0;
		}
		
		function JoomlaCheckCredentials($user, $pass)
		{
			if (!defined('JPATH_BASE')) {
				define( '_JEXEC', 1 );
				define('JPATH_BASE', dirname(dirname(__FILE__)) );//this is when we are in the root
				define( 'DS', DIRECTORY_SEPARATOR );
			}
			require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
			require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
			$mainframe =& JFactory::getApplication('site');
			jimport( 'joomla.user.authentication');
			$auth = & JAuthentication::getInstance();
			$credentials = array( 'username' => $user, 'password' => $pass );
			$response = $auth->authenticate($credentials, array());
			if ($response->status == 1) {
				return true;
			} else {
				return false;
			}
		}
		
		function fm_getUsername($pid)
		{
			return GetValue("username",JOOMLA_PREFIX."_users","id=".$pid);
		}
		
		function fm_setUsername($pid, $newUser)
		{
			$dataBase = db_Connect();
			$dataBase->query("UPDATE ".JOOMLA_PREFIX."_users SET username='".$newUser."' WHERE id=".$pid);
			$dataBase->close();
		}

        function fm_setPassword($pid, $newPass)
        {
            $dataBase = db_Connect();
            $dataBase->query("UPDATE ".JOOMLA_PREFIX."_users SET password='".EncodePassword($newPass)."' WHERE id=".$pid);
            $dataBase->close();
        }

        function fm_blockPlayerAccess($pid)
        {
            $dataBase = db_Connect();
            $dataBase->query("UPDATE ".JOOMLA_PREFIX."_users SET password='".EncodePassword('defkickedisrtgplayer')."' WHERE id=".$pid);
            $dataBase->close();
        }
	}
	
	//Database
	function db_Connect()
	{
		$result = new mysqli("127.0.0.1", "IsrTG2DB", "RSVep2UON", "IsrTG_DataBase");
		$result->set_charset("utf8");
		
		return $result;
	}
	
	function GetValue($column,$table,$where)
	{
		$dataBase = db_Connect();
		if ($result = $dataBase->query("SELECT ".$column." FROM ".$table." WHERE ".$where))
		{
			$row = $result->fetch_assoc();
			return $row[$column];
			$result->free();
		}
		$dataBase->close();
		return '';
	}
	
	function AddAmount($amount,$column,$table,$condition)
	{
		$dataBase = db_Connect();
		if ($result = $dataBase->query("SELECT ".$column." FROM ".$table." WHERE ".$condition))
		{
			$row = $result->fetch_assoc();
			$newVal = $row[$column];
			$newVal = $newVal + $amount;
			$dataBase->query("UPDATE ".$table." SET ".$column."=".$newVal." WHERE ".$condition);
		}
		$result->free();
		$dataBase->close();
	}

	//String functions
	function SearchInMulti($multi,$toCheck)
	{
		if ($multi)
		{
			$tmp = explode(",",$multi);
		} else {
			return False;
		}
		
		if (count($tmp)==0)
		{
			return False;
		}
		elseif (count($tmp)==1)
		{
			if ($tmp[0]==$toCheck)
			{
				return True;
			} else {
				return False;
			}
		} else {
			$i = 0;
			for ($i==0; $i<count($tmp); $i++)
			{
				if ($tmp[$i]==$toCheck)
				{
					return True;
				}
			}
			return False;
		}
		return False;
	}
	
	function RemoveFromMulti($toRem,$multi)
	{
		if ($multi)
		{
			$tmp = explode(",",$multi);
		} else {
			return '';
		}
		
		if (count($tmp)==0)
		{
			return '';
		}
		elseif (count($tmp)==1)
		{
			if ($tmp[0]==$toRem)
			{
				return '';
			} else {
				return $multi;
			}
		} else {
			$tNew = '';
			$i=0;
			for ($i==0; $i<count($tmp); $i++)
			{
				if ($tmp[$i]!=$toRem)
				{
					if ($tNew!='') $tNew .= ',';
					$tNew .= $tmp[$i];
				}
			}
			return $tNew;
		}
	}
	
	function SearchInArray($arry,$toCheck)
	{
		if (!$arry)
		{
			return False;
		}
		elseif (count($arry)==0)
		{
			return False;
		}
		elseif (count($arry)==1)
		{
			if ($arry[0]==$toCheck)
			{
				return True;
			} else {
				return False;
			}
		} else {
			$i = 0;
			for ($i=0; $i<count($arry); $i++)
			{
				if ($arry[$i]==$toCheck)
				{
					
					return True;
				}
			}
			return False;
		}
		return False;
	}
	
	function MultiToArray($multi)
	{
		if ($multi)
		{
			return explode(",",$multi);
		}
		return '';
	}

	function ArrayToMulti($arr)
	{
		if ($arr)
		{
			if (count($arr)==1)
			{
				return $arr[0];
			} else {
				return implode(",",$arr);
			}
		}
		return '';
	}
	
	function GetSetting($name)
	{
		$dataBase = db_Connect();
		if ($result = $dataBase->query("SELECT Value FROM mngr_General WHERE Name='".$name."'"))
		{
			$row = $result->fetch_assoc();
			return $row["Value"];
			$result->free();
		}
		$dataBase->close();
		return '';
	}
	
	function SetSetting($name,$value)
	{
		$dataBase = db_Connect();
		$dataBase->query("UPDATE mngr_General SET Value='".$value."' WHERE Name='".$name."'");
		$dataBase->close();
	}
	
	//Misc
	function CreatePlayersAsJsArray()
	{
		$tplayers = "";
		$dataBase = db_Connect();
		if ($result = $dataBase->query("SELECT * FROM mngr_Players"))
		{
			while ($row = $result->fetch_assoc())
			{
				if ($tplayers!="") $tplayers .= ",";
				$tplayers .= "\"".strtolower($row["Nickname"])."\"";
			}
			$result->free();
		}
		$dataBase->close();
		print "var nicks=new Array(".$tplayers.");\r\n";
	}
	
	function PageRank($rank,$msg,$sid = null)
	{
		$denie = true;
		$SessionID = $sid;
		if (!$sid) $SessionID = fm_getSessionID();
		if ($SessionID == 200) return true;
		if ($SessionID > 0)
		{
			$prank = GetValue("RankID","mngr_Players","JoomlaID=".$SessionID);
			if ($prank>0 && $prank<=$rank)
			{
				$denie = false;
			}
		}
		
		if ($denie)
		{
			if ($msg) print "<div style='font-size:16px;font-weight:bold;text-align:center;color:red;'>".$msg."</div><br>";
			return false;
		} else {
			return true;
		}
	}
	
	function PageDev($rank,$msg,$sid = null)
	{
		$SessionID = fm_getSessionID();
		if ($SessionID == 200) return true;
		print "<div style='font-size:16px;font-weight:bold;text-align:center;color:red;'>בעמוד בפיתוח. לא ניתן להיכנס אליו כרגע.</div><br>";
		return false;
	}

	function options_Ranks($currID)
	{
		$options = '';
		$dataBase = db_Connect();
		if ($result = $dataBase->query("SELECT * FROM mngr_Ranks ORDER BY RankID"))
		{
			while ($row = $result->fetch_assoc())
			{
				if ($row['RankID']==$currID)
				{
					$options .= "<option value=".$row['RankID']." selected>".$row['Name']."</option>";
				} else {
					$options .= "<option value=".$row['RankID'].">".$row['Name']."</option>";
				}
			}
		}
		$result->free();
		$dataBase->close();
		return $options;
	}
	
	function options_Players($currID)
	{
		$options = '';
		$dataBase = db_Connect();
		$last_rank = 0;
		if ($result = $dataBase->query("SELECT mngr_Players.*,mngr_Ranks.Name AS 'RankName' FROM mngr_Players INNER JOIN mngr_Ranks ON mngr_Players.RankID = mngr_Ranks.RankID WHERE Status<2 ORDER BY RankID, Name"))
		{
			while ($row = $result->fetch_assoc())
			{
				if ($last_rank!=$row['RankID']) $options .= "<option value=''>======".$row["RankName"]."======</option>";
				if ($row['JoomlaID']==$currID)
				{
					$options .= "<option value=".$row['JoomlaID']." selected>".PlayerName($row['JoomlaID'])."</option>";
				} else {
					$options .= "<option value=".$row['JoomlaID'].">".PlayerName($row['JoomlaID'])."</option>";
				}
				$last_rank = $row['RankID'];
			}
			$result->free();
		}
		$dataBase->close();
		return $options;
	}
	
	function PlayerName($pid, $hidden=false)
	{
		$dataBase = db_Connect();
		if ($result = $dataBase->query("SELECT * FROM mngr_Players WHERE JoomlaID=".$pid))
		{
			$row = $result->fetch_assoc();
			//print $hidden;
			if ($hidden==0)
			{
				$tname = $row['Name'];
			} else {
				$tmp = explode(" ",$row['Name']);
				$tname = $tmp[0];
			}
			if ($row['JoomlaID']==fm_getSessionID())
			{
				return "<span id='playerNameLink' class='CalendarSelfUser Bold'><a href='חברי-הקלאן/?pid={$pid}' target='_blank'>".$tname." (".$row['Nickname'].")</a></span>";
			} else {
				return "<span id='playerNameLink' class='CalendarOtherUser'><a href='חברי-הקלאן/?pid={$pid}' target='_blank'>".$tname." (".$row['Nickname'].")</a></span>";
			}
			$result->free();
		}
		$dataBase->close();
		return '';
	}
	
	function Player_AddQuli($pid,$quli)
	{
		$dataBase = db_Connect();
		if ($result = $dataBase->query("SELECT Qulifications FROM mngr_Players WHERE JoomlaID=".$pid))
		{
			$row = $result->fetch_assoc();
			$currq = $row["Qulifications"];
			if (!SearchInMulti($currq,$quli)) //if Qulification isn't already owned, add it
			{
				if ($currq) $currq .= ",";
				$currq .= $quli;
				$dataBase->query("UPDATE mngr_Players SET Qulifications='".$currq."' WHERE JoomlaID=".$pid);
			}
		}
		$result->free();
		$dataBase->close();
	}
	
	function Player_AddBadge($pid,$badge)
	{
		$dataBase = db_Connect();
		if ($result = $dataBase->query("SELECT Badges FROM mngr_Players WHERE JoomlaID=".$pid))
		{
			$row = $result->fetch_assoc();
			$currb = $row["Badges"];
			if (!SearchInMulti($currb,$badge)) //if Badge isn't already owned, add it
			{
				if ($currb) $currb .= ",";
				$currb .= $badge;
				$dataBase->query("UPDATE mngr_Players SET Badges='".$currb."' WHERE JoomlaID=".$pid);
			}
		}
		$result->free();
		$dataBase->close();
	}
	
	function playerStatus($status)
	{
		switch ($status)
		{
			case 0:
				return "פעיל";
				break;
			case 1:
				return "חופשה";
				break;
			case 2:
				return "לא פעיל";
				break;
			case 3:
				return "הודח";
				break;
			case 4:
				return "ממתין לראיון";
				break;
			case 5:
				return "נרשם חדש";
				break;
			default:
				return "סטטוס לא מוכר";
				break;
		}
	}

	function checkPlayerQuliForDuty($dutyid,$pid) {
		$dataBase = db_Connect();
		if (!($result = $dataBase->query("SELECT mngr_Players.JoomlaID,mngr_Players.Nickname,mngr_Quli.QuliID,mngr_Quli.Dutys FROM mngr_Players LEFT JOIN mngr_Quli ON FIND_IN_SET(mngr_Quli.QuliID,mngr_Players.Qulifications) WHERE JoomlaID=".$pid." AND FIND_IN_SET(".$dutyid.",mngr_Quli.Dutys)")))
		{
			return;
		}
		
		$dataBase->close();
		if ($result->num_rows > 0) {
			$result->free();
			return true;
		} else {
			return false;
		}
	}
	
	function getModList() {
		$arr = array();
		$data = file_get_contents(SERVER_PATH."/launcher/server.yml");
		$data = explode(":required_mods: ",$data);
		$data = trim($data[1]);
		$data = explode("\n",$data);
		for ($i=0;$i<count($data);$i++) {
			if (strstr($data[$i],"- "))
				array_push($arr,substr($data[$i],3,strlen($data[$i])-5));
		}
		return $arr;
	}
	
	function setModList($mods) {
		$fcontent = "--- \r\n";
		$fcontent .= ":name: IsrTG\r\n";
		$fcontent .= ":ip: ".GAME_SERVER_IP."\r\n";
		$fcontent .= ":port: ".GAME_SERVER_PORT."\r\n";
		$fcontent .= ":motd: \r\n";
		$fcontent .= "- IsrTG Server\r\n";
		$fcontent .= ":rules: []\r\n";
		$fcontent .= ":force_server_name: false\r\n";
		$fcontent .= ":game: 9DE199E3-7342-4495-AD18-195CF264BA5B\r\n";
		$fcontent .= ":guid: 98c42884-dde3-4caa-a6e2-3dd227329ae5\r\n";
		$fcontent .= ":open: false\r\n";
		$fcontent .= ":hidden: false\r\n";
		$fcontent .= ":force_mod_update: false\r\n";
		$fcontent .= ":required_mods: \r\n";
		for ($i=0;$i<count($mods);$i++) {
			$fcontent .= "- \"".$mods[$i]."\"\r\n";
		}
		$fcontent .= ":allowed_mods: []\r\n";
		$fcontent .= ":apps: []\r\n";
		$fcontent .= ":missions: []\r\n";
		$fcontent .= ":mpmissions: []\r\n";
		$data = file_put_contents(dirname(__FILE__)."/../launcher/server.yml",$fcontent);
	}
	
	function sendmail($to,$subject,$msg,$file="")
	{
		include_once("class.phpmailer.php");
		$mail = new PHPMailer();
		$mail->isSMTP();
		$mail->IsHTML(true);
		$mail->SMTPDebug = 0;
		$mail->Debugoutput = 'html';
		$mail->Host = 'smtp.gmail.com';
		$mail->Port = 587;
		$mail->SMTPSecure = 'tls';
		$mail->SMTPAuth = true;
		$mail->Username = "israelitacticalgaming@gmail.com";
		$mail->Password = "isrtg1313";
		$mail->setFrom('info@isrtg.com', 'IsrTG No-Reply');
		//$mail->addReplyTo('', '');
		$toArr = explode(";",$to); //split recipments (delim ;)
		for($i=0; $i<count($toArr);$i++)
			$mail->addAddress($toArr[$i], '');
		$mail->CharSet = 'utf-8';
		$mail->Subject = $subject;
		$body = "<html dir=rtl><body><table style=\"border: 2px solid #8DE02E; background-color: #ffffff; width: 540px; text-align: right; line-height: 115%;\" dir=rtl align=center><tr><td><img src=http://www.isrtg.com/mngr/images/logo.png align=center width=1000></td></tr><tr><td>".$msg."</td></tr></table><br><font size=1><center>מייל זה נשלח באופן אוטומטי על ידי מערכת האתר. אין להשיב לכתובת מייל זו מאחר ולא יתקבל מענה.<br><a href=http://www.isrtg.com>IsrTG - Israeli Tactical Gaming</a></center></font></body></html>";
		$mail->Body = $body;
		if ($file!="")
			$mail->AddAttachment($file);
		
		if (!$mail->send()) {
			echo "Mailer Error: " . $mail->ErrorInfo;
		}
	}
	
	function addlog($pid,$log)
	{
		$dataBase = db_Connect();
		$dateObj = date_now();
		$dataBase->query("INSERT INTO mngr_Logs (JoomlaID,Log,LogDate,RemoteIP) VALUES (".$pid.",'".$log."','".$dateObj->format('Y-m-d H:i:s')."','".$_SERVER['REMOTE_ADDR']."')");
		$dataBase->close();
	}
	
	function EventWantedPlayers($eid)
	{
		$wanted = array();
		$db = db_Connect();
		if ($result = $db->query("SELECT * FROM mngr_Events WHERE EventID=".$eid))
		{
			$row = $result->fetch_assoc();
			if ($result->num_rows > 0)
			{
				if ($row['PlayersLock']!="")
				{
					return $row['PlayersLock'];
				} else {
					$byRank = $row['PlayersByRank'];
					$byName = $row['PlayersByName'];
					$byQuli = $row['PlayersByQuli'];
					
					//Add players by Rank
					if ($byRank!="")
					{
						if ($result1 = $db->query("SELECT * FROM mngr_Players WHERE Status<2 AND RankID IN(".$byRank.")"))
						{
							while ($row1 = $result1->fetch_assoc())
							{
								if (!SearchInArray($wanted,$row1['JoomlaID'])) array_push($wanted,$row1['JoomlaID']);
							}
							$result1->free();
						}
					}

					//Add players by Quli
					if ($byQuli!="")
					{
						$byQuli = MultiToArray($byQuli);
						if ($result1 = $db->query("SELECT * FROM mngr_Players WHERE Status<2"))
						{
							while ($row1 = $result1->fetch_assoc())
							{
								for ($i=0;$i<count($byQuli);$i++)
								{
									if (SearchInMulti($row1['Qulifications'],$byQuli[$i]))
									{
										if (!SearchInArray($wanted,$row1['JoomlaID'])) array_push($wanted,$row1['JoomlaID']);
										break;
									}
								}
							}
							$result1->free();
						}
					}
					
					//Add players by Name
					if ($byName!="")
					{
						$byName = MultiToArray($byName);
						for ($i=0;$i<count($byName);$i++)
						{
							if (!SearchInArray($wanted,$byName[$i])) array_push($wanted,$byName[$i]);
						}
					}
					
					if (($byRank=="" && $byQuli=="" && $byName=="") || count($wanted)==0) //No Special mark or special mark not found. Add all players
					{
						if ($result1 = $db->query("SELECT * FROM mngr_Players WHERE Status<2"))
						{
							while ($row1 = $result1->fetch_assoc())
							{
								array_push($wanted,$row1['JoomlaID']);
							}
							$result1->free();
						}
					}
					
					$wanted = ArrayToMulti($wanted);
					return $wanted;
				}
			}
			$result->free();
		}
		$db->close();
		return "";
	}
	
	function LockEvents()
	{
		$db = db_Connect();
		if ($result = $db->query("SELECT * FROM mngr_Events WHERE Status=0"))
		{
			if ($result->num_rows > 0)
			{
				while ($row = $result->fetch_assoc())
				{
						$dateObj_now = date_now();
						$edate = DateTime::createFromFormat("Y-m-d H:i:s", $row['EventDate']);
						$edate->modify('-1 days');
						$edate->setTime(19,30,0); //30 minutes before hitpakdut is over - Lock players
						if ($dateObj_now>$edate) //if time passed
						{
							$db->query("UPDATE mngr_Events SET PlayersLock='".EventWantedPlayers($row['EventID'])."' WHERE EventID=".$row['EventID']);
						} else {
							$db->query("UPDATE mngr_Events SET PlayersLock='' WHERE EventID=".$row['EventID']);
						}
				}
			}
			$result->free();
		}
		$db->close();
	}
	
	function userLastLogin() {
		global $fm_sessionID;
		//$SessionID = $fm_sessionID();
		$SessionID = 0;
		if ($SessionID > 0) {
			$dataBase = db_Connect();
			$dateObj = date_now();
			$dataBase->query("UPDATE mngr_Players SET LastLogin='".$dateObj->format('Y-m-d H:i:s')."' WHERE JoomlaID=".$SessionID);
			$dataBase->close();
		}
	}
	
	function printDutysTable($arr,$dutys,$countMethod = "") {
		print "<table border=0><tr valign=top>";
		for ($i=0;$i<count($arr);$i++) {
			print "<td>".printDutysTableHelper($arr[$i],$dutys,$i,0,0,$countMethod)."</td>";
		}
		print "</tr></table>";
	}
	
	function printDutysTableHelper($obj,$dutys,$grpCount,$lowerGrp,$dutyCount,$countMethod="",$level = 0) {
		$res = "";
		$res = "<table border=0><tr valign=top><td style='padding-right: ".(30*$level)."px;'>";
		if ($lowerGrp==0) {
			$res .= "<input type=checkbox name='grp_markall' ".(($countMethod!="dutys")?"disabled":"")." onClick=\"$('input[name*=\'grp".$grpCount."\']').prop('checked',$(this).is(':checked'));\"> <b><u>".$obj->Name."</u></b><br>";
		} else {
			$res .= "<input type=checkbox name='grp".$grpCount."' ".(($countMethod!="dutys")?"disabled":"")." onClick=\"$('input[name*=\'grp".$grpCount."_lwr".$lowerGrp."\']').prop('checked',$(this).is(':checked'));\"> <b><u>".$obj->Name."</u></b><br>";
		}
		for ($i=0;$i<=(count($obj->dutysArr)-1);$i++) { //Loop through dutys in group
			$checkedTxt = '';
			
			if ($obj->dutysArr[$i]->available==True) {
				$checkedTxt = ' Checked';
				$dutyName = "<font color=#009900><B>".$dutys[$obj->dutysArr[$i]->id]."</B></font>";
			} else {
				$dutyName = $dutys[$obj->dutysArr[$i]->id];
			}
			$res .= "<input type=checkbox name=grp".$grpCount."_lwr".$lowerGrp."_dty".$dutyCount.$checkedTxt." ".(($countMethod!="dutys")?"disabled":"")."> ".$dutyName."<br>";
			$dutyCount++;
		}
		$res .= "</td></tr></table>";
		
		$dutyCount = 0;
		if (count($obj->lowerGroupsArr)>0) {
			for ($i=0;$i<=(count($obj->lowerGroupsArr)-1);$i++) {
				$res .= printDutysTableHelper($obj->lowerGroupsArr[$i],$dutys,$grpCount,$i+1,$dutyCount,$countMethod,$level+1);
			}
		}
		return $res;
	}
	
	function printPlayerDutysTable($arr,$dutys,$disable)
		{
		unset($to_print);
		$to_print = "<table id='Calendar_Dutys_table'><tr>";
		for ($i=0;$i<count($arr);$i++)
			{
			$to_print .= "<td class='Table_Col'>".printPlayerDutysTableHelper($arr[$i],$dutys,$i,0,0,$disable)."</td>";
			}
		$to_print .= "</tr></table>";
		
		return $to_print;
		}
	
	function printPlayerDutysTableHelper($obj,$dutys,$grpCount,$lowerGrp,$dutyCount,$disable,$level = 0) 
	{
		unset($res);
		$res = "<table id='Calendar_Inner_Dutys_table'>";
		//count available dutys
		$availableDutysNum = 0;
		for ($i=0;$i<=(count($obj->dutysArr)-1);$i++)
		{
			if ($obj->dutysArr[$i]->available==True)
			{
				$availableDutysNum++;
			}
		}
		
		$right_padding=30*$level;
		$res .= "<tr><td class='GroupTd'>";

		if ($lowerGrp==0)
			{
				$res .= "<div class='groupName'>".$obj->Name."</div>";
			}
			else
			{
				$res .= "<div class='groupName'>".$obj->Name."</div>";
			}
			
		$res .= "<table id='innerGroupTable' cellspacing=5 cellpadding=0>";
		for ($i=0;$i<=(count($obj->dutysArr)-1);$i++)
			{ //Loop through dutys in group
				$checkedTxt = '';
				$res .= "<tr class='GroupDutyRow'><td class='GroupDutyCol'>";
				if ($obj->dutysArr[$i]->available==True)
					{
					//when duty available
					if ($obj->dutysArr[$i]->playerID==fm_getSessionID())
						{
						$checkedTxt = ' Checked';
						$dutyName = "<span class='dutyName Bold'>".$dutys[$obj->dutysArr[$i]->id]."</span>";
						}
						else
						{
						$dutyName = "<span class='dutyName'>".$dutys[$obj->dutysArr[$i]->id]."</span>";
						}
						
					if ($obj->dutysArr[$i]->playerID!=Null)
						{
						$player_name=PlayerName($obj->dutysArr[$i]->playerID,true);

						$res .= <<<IsrTG
						<div id="HitpakdutDivs">
							<div id="HitpakdutDiv1">
								<span><input type=radio name='status'{$checkedTxt} value='grp{$grpCount}_lwr{$lowerGrp}_dty{$dutyCount}' id='grp{$grpCount}_lwr{$lowerGrp}_dty{$dutyCount}' onClick='updateStatus.submit();' disabled></span>
							</div>
							<div id="HitpakdutDiv2">
								<div id="HitpakdutDivDuty">
									<span class='dutyName'>{$dutyName}</span>
								</div>
								<div id="HitpakdutDivPName">
									&#187;&nbsp;{$player_name}
								</div>
							</div>
						</div>
IsrTG;
						}
						else
						{
						//Check if player can play duty before allowing it
						if (checkPlayerQuliForDuty($obj->dutysArr[$i]->id,fm_getSessionID()))
							{
							//player allow to choose the duty
							$res .= <<<IsrTG
							<input type=radio name='status'{$checkedTxt} value='grp{$grpCount}_lwr{$lowerGrp}_dty{$dutyCount}' id='grp{$grpCount}_lwr{$lowerGrp}_dty{$dutyCount}' onClick='updateStatus.submit();
IsrTG;
							if ($disable) $res .= "disabled";
							$res .= <<<IsrTG
								'>
								<label for='grp{$grpCount}_lwr{$lowerGrp}_dty{$dutyCount}'>
									<span class='OpenDuty'>{$dutyName}</span>
								</label>
IsrTG;
							}
							else
							{
							//player not allow to choose this duty
							$res .= <<<IsrTG
							<input type=radio name='status'{$checkedTxt} value='grp{$grpCount}_lwr{$lowerGrp}_dty{$dutyCount}' id='grp{$grpCount}_lwr{$lowerGrp}_dty{$dutyCount}' onClick='updateStatus.submit();' disabled>
								<label for='grp{$grpCount}_lwr{$lowerGrp}_dty{$dutyCount}'>
									<span class='DutyDisable Strike'>{$dutyName}</span>
								</label>
IsrTG;
							}
						}
					}
					else
					{
					//when duty not available
					$res .= <<<IsrTG
					<input type=radio disabled>
						<span class='DutyDisable'>{$dutys[$obj->dutysArr[$i]->id]}</span>
IsrTG;
					}
				$dutyCount++;
				$res .= "</td></tr>";
			}
			$res .= "</table></td></tr>";
			$res .= "</table>";
		
			$dutyCount = 0;
			if (count($obj->lowerGroupsArr)>0)
				{
				for ($i=0;$i<=(count($obj->lowerGroupsArr)-1);$i++)
					{
					//$res .= '<hr>'; //spacer between groups
					$res .= printPlayerDutysTableHelper($obj->lowerGroupsArr[$i],$dutys,$grpCount,$i+1,$dutyCount,$disable,$level+1);
					}
				}
		
	return $res;
	}
	
	function changePlayerHitpakdut($arr,$pid,$status,$admin)	{
		for ($i=0;$i<count($arr);$i++) {
			changePlayerHitpakdutHelper($arr[$i],$i,0,0,$pid,$status,$admin);
		}
	}
	
	function changePlayerHitpakdutHelper($obj,$grpCount,$lowerGrp,$dutyCount,$pid,$status,$admin) {
		for ($i=0;$i<=(count($obj->dutysArr)-1);$i++) { //Loop through dutys in group
			if ($obj->dutysArr[$i]->playerID == $pid) {
				$obj->dutysArr[$i]->playerID = Null;
			}
			if ($status=="grp".$grpCount."_lwr".$lowerGrp."_dty".$dutyCount) {
				if ($obj->dutysArr[$i]->playerID != Null) { //Duty is already occupied
					if ($admin==1) { //Change was requested by an admin
						$obj->dutysArr[$i]->playerID = $pid;
						return true;
					} else {
						return false;
					}
				} else {
					$obj->dutysArr[$i]->playerID = $pid;
				}
			}
			$dutyCount++;
		}
		
		$dutyCount = 0;
		if (count($obj->lowerGroupsArr)>0) {
			for ($i=0;$i<=(count($obj->lowerGroupsArr)-1);$i++) {
				changePlayerHitpakdutHelper($obj->lowerGroupsArr[$i],$grpCount,$i+1,$dutyCount,$pid,$status,$admin);
			}
		}
		return true;
	}
	
	function playerSubmittedToDuty($arr,$pid) {
		for ($i=0;$i<count($arr);$i++) {
			if (playerSubmittedToDutyHelper($arr[$i],$i,0,0,$pid)) return true;
		}
		return false;
	}
	
	function playerSubmittedToDutyHelper($obj,$grpCount,$lowerGrp,$dutyCount,$pid) {
		for ($i=0;$i<=(count($obj->dutysArr)-1);$i++) { //Loop through dutys in group
			if ($obj->dutysArr[$i]->playerID == $pid) return true;
			$dutyCount++;
		}
		
		$dutyCount = 0;
		if (count($obj->lowerGroupsArr)>0) {
			for ($i=0;$i<=(count($obj->lowerGroupsArr)-1);$i++) {
				if (playerSubmittedToDutyHelper($obj->lowerGroupsArr[$i],$grpCount,$i+1,$dutyCount,$pid)) return true;
			}
		}

	}
	
	function showMessage($msg) {
		switch ($msg) {
			case 1:
				return getGoodMessage("עודכן בהצלחה");
			case 2:
				return getGoodMessage("המדריך נוסף בהצלחה");
			case 3:
				return getBadMessage("שגיאה בהעלאת התמונה");
			case 4:
				return getBadMessage("הקובץ חייב להיות תמונה");
			case 5:
				return getGoodMessage("המדריך עודכן בהצלחה");
			case 6:
				return getGoodMessage("המדריך נמחק בהצלחה");
		}
	}
	
	function getGoodMessage($msg) {
		return "<div class='Approve Bold Center'>".$msg."</div><br>";
	}
	
	function getBadMessage($msg) {
		return "<div class='Error Bold Center'>".$msg."</div><br>";
	}

	LockEvents();
	
	if (isset($FUNC_ONLY)) return;
?>