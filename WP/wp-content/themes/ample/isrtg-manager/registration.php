<?php
/*
 * Template Name: isrtg_registration
 */
include "header.php";
?>

<?php
	$db = db_Connect();
	$mode = getFieldg('mode');
	if ($mode=='') $mode = getField('mode');
?>
<html dir=rtl>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<script language="javascript">
	function modeSel(choose)
	{
			window.location.href='?mode='+choose;
	}
	
	function isEmail(email) { 
		return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(email);
	} 

	function valForm(but)
	{
		<?php CreatePlayersAsJsArray(); ?>
		if (tform.nickname.value=='')
		{
			alert('יש למלא כינוי במשחק');
			return;
		}
		if (nicks.indexOf(tform.nickname.value.toLowerCase())>-1)
		{
			alert('הכינוי כבר קיים. יש לבחור כינוי אחר');
			return;
		}
		if (tform.password.value=='')
		{
			alert('יש למלא סיסמא');
			return;
		}
		if (tform.password.value.length<5)
		{
			alert('על הסיסמא להיות לפחות 5 תווים');
			return;
		}
		if (tform.password1.value=='')
		{
			alert('יש למלא אימות סיסמא');
			return;
		}
		if (tform.password.value!=tform.password1.value)
		{
			alert('הסיסמא ואימות הסיסמא אינן תואמות');
			return;
		}
		if (tform.tname.value=='' || tform.tname.value.indexOf(' ')==-1)
		{
			alert('יש למלא שם מלא');
			return;
		}
		if (tform.nameeng.value=='')
		{
			alert('יש למלא שם פרטי באנגלית');
			return;
		}
		if (!isDate(document.getElementById('tday').value+'/'+document.getElementById('tmonth').value+'/'+document.getElementById('tyear').value))
		{
			alert('תאריך לידה לא חוקי');
			return;
		}
		if (tform.armaid.value=='' || tform.armaid.value.length<5)
		{
			alert('יש למלא קוד שחקן חוקי');
			return;
		}
		if (tform.email.value=='' || tform.email.value.length<5 || (isEmail(tform.email.value)==false))
		{
			alert('יש למלא אימייל חוקי');
			return;
		}
		if (tform.firdayOPS.value.length=='' || tform.exp.value.length=='' || tform.preclan.value.length=='' || tform.howhear.value.length=='' || tform.othergames.value.length=='' || tform.army.value.length=='')
		{
			alert('יש למלא את כלל השאלות האישיות');
			return;
		}
		but.value = 'אנא המתן...';
		but.disabled = 'disabled';
		tform.submit();
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

function inputLimiter(e,allow,disallow)
{
    var AllowableCharacters = '';
    var DisAllowableCharacters = '';
    if (allow == ''){allow='all';}
    if (allow == 'letters'){AllowableCharacters=' אבגדהוזחטיכלמנסעפקצרשתםףךץן';}
    if (allow == 'eng'){AllowableCharacters='1234567890 ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';}
    if (allow == 'numbers'){AllowableCharacters='1234567890';}
    if (allow == 'all'){AllowableCharacters='1234567890 ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzאבגדהוזחטיכלמנסעפקצרשתםףךץן-\.=?';}

    if (disallow)
    {
        DisAllowableCharacters = disallow;
    }

    var k;
    k=document.all?parseInt(e.keyCode): parseInt(e.which);
    if (k!=13 && k!=8 && k!=0)
    {
        if ((e.ctrlKey==false) && (e.altKey==false))
        {
            return (AllowableCharacters.indexOf(String.fromCharCode(k))!=-1 && DisAllowableCharacters.indexOf(String.fromCharCode(k))==-1);
        }
        else
        {
            return true;
        }
    }
    else
    {
        return true;
    }
}
</script>
<body>
<div id='IsrTG_Content'>
        <?php
		if ($mode=='')
		{
			if (getSetting("RegOpen")=="1") {
				print getSetting("RegText");
				?>
				<p align=center><b>
					עדיין מעוניין?
					<input type=button onClick="modeSel('accept');" value='הצטרף'>
					
				</p>
				<?php
			} else {
				print "שלום לך שחקן יקר,<br>הנהלת הקלאן מודה לך שבחרת להתחיל בתהליך הצטרפות לקלאן Israeli Tactical Gaming.<br>לצערנו, הקלאן לא מגייס שחקנים חדשים בזמן זה.<br>אנא בדוק עמוד זה בשנית בעוד מספר שבועות או צור איתנו קשר באימייל: <a href='mailto:staff@isrtg.com'>staff@isrtg.com</a>.<br><br>בברכה,<br>הנהלת הקלאן.";
			}
		}
		if ($mode=='accept')
		{
			$msg = '';
			if (getField("fill")=="1")
			{
				$regInfo = "<b>".toDBp("fridayOPS_txt")."</b><br>".toDBp("firdayOPS")."<br><br><b>".toDBp("exp_txt")."</b><br>".toDBp("exp")."<br><br><b>".toDBp("preclan_txt")."</b><br>".toDBp("preclan")."<br><br><b>".toDBp("howhear_txt")."</b><br>".toDBp("howhear")."<br><br><b>".toDBp("othergames_txt")."</b><br>".toDBp("othergames")."<br><br><b>".toDBp("army_txt")."</b><br>".toDBp("army")."<br><br><b>".toDBp("knowledge_txt")."</b><br>".ArrayToMulti(toDBp("knowledge"));
				$today = date('Y-m-d', mktime(date('H')-1, date('i'), date('s'), date('m'),date('d'),date('Y')));
				
				$db->query("INSERT INTO ".JOOMLA_PREFIX."_users (Name,username,email,password) VALUES ('".toDBp('tname')."','".toDBp('nickname')."','".toDBp('email')."','".EncodePassword(toDBp('password'))."')");
				$joomlaid = $db->insert_id;
				$db->query("INSERT INTO mngr_Players (JoomlaID,Status,Nickname,Name,NameENG,Email,ArmaID,RankID,Skype,Steam,BirthDate,RegInfo,JoinDate) VALUES (".$joomlaid.",5,'".toDBp('nickname')."','".toDBp('tname')."','".toDBp("nameeng")."','".toDBp('email')."','".toDBp('armaid')."',".PLAYER_DEF_RANK.",'".toDBp('skype')."','".toDBp('steam')."','".getField('tyear')."-".getField('tmonth')."-".getField('tday')."','".$regInfo."','".$today."')");
				$body = "שלום,<br>ברצונו להודות לך על כך שהגשת טופס הצטרפות לקלאן.<br>בימים הקרובים צוות הקלאן יעבור על הטופס ויבחן את בקשתך.<br><br>אנא עקוב אחר תיבת הדואר הנכנס שלך, על מנת לקבל הנחיות להמשך התהליך.<br><br>בברכה צוות IsrTG‬‎.";
				sendmail(toDBp('email'),"הצטרפות לקלאן",$body);
				$msg="ok";
			}
			if ($msg=="ok") print "<div style='font-weight:bold;text-align:center;color:green;'>ההרשמה בוצעה בהצלחה!</div><br>"; 
			if ($msg!="ok") print "<div style='font-weight:bold;text-align:center;color:red;'>".$msg."</div><br>"; 
		if ($msg!="ok") {
		?>
		<table border=0 align=center width=60%><tr><td><font color=red>*</font> - שדות חובה
		<form method=post id="tform"><input type=hidden name=fill value='1'>
		<table border=2 style="border-collapse: collapse;" align=center>
			<tr>
				<td>
				פרטי התחברות
				</td>
				<td>
				<h3>כינוי במשחק<font color=red>*</font>:</h3>
				במידה ותתקבל לקלאן, הכינוי ישמש עבורך כשם המשתמש ע"מ להיכנס למערכות האתר.<br>
				<input type=text name=nickname value='' size=30 dir=ltr onKeyPress='return inputLimiter(event,"","<> |/");' onPaste="return false;">
				<br><br>
				<h3>סיסמה<font color=red>*</font>:</h3>
				<input type=password name=password value='' size=30 dir=ltr>
				<br><br>
				<h3>אימות סיסמה<font color=red>*</font>:</h3>
				<input type=password name=password1 value='' size=30 dir=ltr>
				</td>
			</tr>
			<tr>
				<td>
				מידע בסיסי 
				</td>
				<td>
				<h3>שם (פרטי ומשפחה, בעברית)<font color=red>*</font>:</h3>
				<input type=text name=tname value=''>
				<br><br>
				<h3>שם פרטי (באנגלית):<font color=red>*</font>:</h3>
				<input type=text name=nameeng value='' dir=ltr onKeyPress="return inputLimiter(event,'eng','');" onPaste="return false;">
				<br><br>
				<h3>תאריך לידה<font color=red>*</font>:</h3>
				<select name=tyear id=tyear>
				<?php
				$minAge = 14;
				$maxAge = 50;
				
				for ($i=date("Y")-$minAge; $i>=date("Y")-$maxAge; $i--)
				{
					print "<option value='".$i."'>".$i."</option>";
				}
				?>
				</select> <select name=tmonth id=tmonth>
				<?php
				for ($i=1; $i<=12; $i++)
				{
					print "<option value='".$i."'>".$i."</option>";
				}
				?>
				</select> <select name=tday id=tday>
				<?php
				for ($i=1; $i<=31; $i++)
				{
					print "<option value='".$i."'>".$i."</option>";
				}
				?>
					</select> 
					<br><br>
				</td>
			</tr>
			<tr>
				<td>
					דרכי יצירת קשר
				</td>
				<td>
					<h3>אימייל<font color=red>*</font>:</h3>
					<input type=text name=email value='' dir=ltr><br><br>
					<h3>סקייפ:</h3>
					<input type=text name=skype value='' dir=ltr><br><br>
					<h3>סטים:</h3>
					<input type=text name=steam value='' dir=ltr><br>
				</td>
			</tr>
			<tr>
				<td>
					מידע המתקשר ל ArmA3
				</td>
				<td>
					<input type=hidden name=fridayOPS_txt value="תדירות ההגעה למשחקים:">
					<h3>תדירות ההגעה למשחקים<font color=red>*</font>:</h3>
					האם תוכל להגיע למשחקים הקבועים בימי שישי בין השעות  15:00-18:00? , נסה להעריך מה התדירות בה תוכל להגיע למשחקים. בנוסף- ציין אם ישנם עיסוקים שעלולים למנוע ממך להגיע (צבא, לימודים, חוג, וכו')
					<input type=text name=firdayOPS value=''>
					<br><br>
					<input type=hidden name=armaid_txt value="קוד ארמה:">
					<h3>PlayerID<font color=red>*</font>:</h3>
					הזן את ה Player ID  שלך. (בתפריט הראשי: בחרו ב Player Profile, בחרו בשם הדמות שלכם ובחרו Edit. ה Played ID יופיע בתחתית העמוד) <br>
					<input type=text name=armaid value='' dir=ltr onKeyPress="return inputLimiter(event,'numbers','');" onPaste="return false;">
					<br><br>
					<input type=hidden name=exp_txt value="האם שיחקת בעבר ב-ArmA 3, או בכל משחק אחר בסדרת ArmA?">
					<h3>האם שיחקת בעבר ב-ArmA 3, או בכל משחק אחר בסדרת ArmA?<font color=red>*</font></h3>
					אם כן, אנא פרט.<br>
					<input type=text name=exp value=''>
					<br><br>
					<input type=hidden name=preclan_txt value="האם בעבר היית חבר בקלאן ArmA 3 אחר?">
					<h3>האם בעבר היית חבר בקלאן ArmA 3 אחר?<font color=red>*</font></h3>
					אם כן, אנא פרט את שם הקלאן, משך הזמן , מעמדך (תפקיד, דרגה, וכו'), וסיבת העזיבה.<br>
					<input type=text name=preclan value=''>
					<br><br>
					<input type=hidden name=howhear_txt value="איך שמעת על IsrTG?">
					<h3>איך שמעת על IsrTG?<font color=red>*</font></h3>
					<input type=text name=howhear value=''>
					<br><br>
					<input type=hidden name=othergames_txt value="חוץ מ-ArmA 3, באיזה עוד משחקים הינך משחק?">
					<h3>חוץ מ-ArmA 3, באיזה עוד משחקים הינך משחק?<font color=red>*</font></h3>
					<input type=text name=othergames value=''>		
			</td>
			</tr>
			<tr>
				<td>
					שאלות אחרונות
				</td>
				<td>
					<input type=hidden name=army_txt value="האם אתה משרת, או שירתת בעבר בצבא?">
					<h3>האם אתה משרת, או שירתת בעבר בצבא?<font color=red>*</font></h3>
					אם כן, אנא פרט (במידה וניתן) את תפקידך בשירות.<br>
					<input type=text name=army value=''>	
					<br><br>
					<input type=hidden name=knowledge_txt value="האם הינך בעל ידע מסוים, אשר יכול לעזור בקידום ופיתוח IsrTG?">
					<h3>האם הינך בעל ידע מסוים, אשר יכול לעזור בקידום ופיתוח IsrTG?<font color=red>*</font></h3>	
					אם כן, אנא סמן את האפשרויות בהתאם.<br>
					<input type=checkbox name=knowledge[] value='עיצוב אתרים'> עיצוב אתרים<br>
					<input type=checkbox name=knowledge[] value='יצירת מפות'> יצירת מפות<br>
					<input type=checkbox name=knowledge[] value='יצירת מודלים'> יצירת מודלים<br>
					<input type=checkbox name=knowledge[] value='יצירת טקסטורות'> יצירת טקסטורות<br>
					<input type=checkbox name=knowledge[] value='יצירת אנימציות'> יצירת אנימציות<br>
					<input type=checkbox name=knowledge[] value='כתיבת סקריפטים'> כתיבת סקריפטים<br>
					<input type=checkbox name=knowledge[] value='עיצוב ועריכת תמונות'> עיצוב ועריכת תמונות<br>
					<input type=checkbox name=knowledge[] value='צילום ועריכת סירטונים'> צילום ועריכת סירטונים<br>
				</td>
			</tr>
		<tr>
		<td colspan=2 align=center><input type=button onClick="valForm(this);" value='שלח טופס'></td>
		</tr>
		</table>
		</form>
		</td></tr></table>
		<?php
		}
		}
		?>
</div>
</body>
</html>
<?php
	$db->close();
?>

<?php include "footer.php"; ?>