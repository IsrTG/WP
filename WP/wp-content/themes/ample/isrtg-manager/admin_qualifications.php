<?php
/*
 * Template Name: isrtg_admin_qualifications
 */
include "admin_Menu.php";
include "header.php";
?>

<?php
if (PageRank($perm_Admin_Manager,"אינך מורשה להיכנס לעמוד זה")) {

$db = db_Connect();

$insert = '';
$update = '';
$mode = getFieldg("mode",$db);
$QuliID = getFieldg("qID",$db);

if ($mode =="new" && getField("action",$db)=="add")
{   $Dutysl = ArrayToMulti(toDBp("duty_IDS",$db));
    $db->query("INSERT INTO mngr_Quli (Name,Description,Dutys) VALUES ('".toDBp("Name",$db)."','".toDBp("Description",$db)."','".$Dutysl."')");
    addlog(fm_getSessionID(),"הוסיף הכשרה חדשה: ".toDBp("Name",$db));
    $insert = true;
    $mode= "";
}
if ($mode =="update" && getField("action",$db)=="edit")
{
    $Dutysl = ArrayToMulti(toDBp("duty_IDS",$db));
    $db->query("UPDATE mngr_Quli SET Name='".toDBp("Name",$db)."', Description='".toDBp("Description",$db)."' ,Dutys='".$Dutysl."' WHERE QuliID=".toDBp("qID",$db));
    addlog(fm_getSessionID(),"ערך את ההכשרה: ".toDBp("Name",$db));
    $update = true;
    $mode= "";
}

?>

<script language=javascript>
	function modeSel(choose)
	{

			window.location.href='?mode='+choose;
	}
	function quliSel(id)
	{

			window.location.href='?qID='+id+'&mode=update';
	}
	
</script>
<div id="IsrTG_Content">
<?php
print("$MenuToPrint");
?>

<div id="Main_Qualification_Managment">
	<?php if ($update) print "<div class='Approve'>ההכשרה עודכנה בהצלחה</div>"; ?>
	<?php if ($insert) print "<div class='Approve'>הכשרה הוכנסה בהצלחה</div>"; ?>
	
	<div class='Title'>
		<h1>ניהול הכשרות</h1>
	</div>
	<div class="MainContent">
		<table border=2 style="border-collapse: collapse;" align=center width=95%>
			<tr>
				<td colspan=2 style="font-weight:bold;text-align:center;">אנא בחר אופציה:
					<input type=button onClick="modeSel('new');" value='הוסף הכשרה'>
					<input type=button onClick="modeSel('update');" value='עדכן הכשרה'>
				</td>
			</tr>
		</table>
	</div>
			
	
	<form method=post>
	<?php
        if ($mode=='new')
		{
		?>
		
		<div class='Title'>
			<h2>הוספת הכשרה</h2>
		</div>
		<div class="MainContent">
			<input type=hidden name=action value='add'>
			<table border=2 style="border-collapse: collapse;" align=center width=95%>
				<tr>
					<td width=20%>שם ההכשרה:</td>
					<td><input type=text name=Name value=''></td>	
				</tr>
				<tr>
					<td>פרטים על ההכשרה:</td>
					<td><textarea name=Description rows="4" cols="30"onfocus="if(this.value == 'תיאור ההכשרה') {this.value=''}" onblur="if(this.value == ''){this.value ='תיאור ההכשרה'}">תיאור ההכשרה</textarea>
				</tr>
				<tr>
					<td> תפקידים שההכשרה מאפשרת: </td>
					<td>
						<?php
							if ($result1 = $db->query("SELECT * FROM mngr_Dutys"))
								{
								while ($row1 = $result1->fetch_assoc())
									{
									print "<input type=checkbox name='duty_IDS[]' value=".$row1['DutyID']."> ".$row1['Name']."<br>";
									}
								}
							$result1->free();
						?>
					</td>
				</tr>
				<tr>
					<td colspan=2 align=center><input type=submit value='הוסף הכשרה'></td>
				</tr>
			</table>
		</div>
		
		<?php
		}
		
		if ($mode=='update')
		{
		?>
		<div class='Title'>
			<h2>עדכון הכשרה</h2>
		</div>
		<div class="MainContent">
			<input type=hidden name=action value='edit'><input type=hidden name=qID value='<?php print $QuliID; ?>'>
			<table border=2 style="border-collapse: collapse;" align=center width=95%>
				<tr>
					<td width=20%>בחר הכשרה:</td>
					<td>
						<select name="Quli" onChange="quliSel(this.options[this.selectedIndex].value);"><option value="">בחר הכשרה</option>
						<?php
						if ($result = $db->query("SELECT * FROM mngr_Quli"))
							{
							while ($row = $result->fetch_assoc())
								{
								if ($row['QuliID']==getFieldg("qID",$db))
									{
									$options .= "<option value=".$row['QuliID']." selected>".$row['Name']."</option>";
									} 
									else
									{
									$options .= "<option value=".$row['QuliID'].">".$row['Name']."</option>";
									}
								}
							}
						$result->free();
						print $options;
						?>
						</select>
					</td>
				</tr>
				
				<?php
				if (!$QuliID=='')
					{
					if ($result = $db->query("SELECT * FROM mngr_Quli WHERE QuliID=".toDBg("qID")))
						{
						$row = $result->fetch_assoc();
						}
					$result->free();

				?>
		<tr>
		<td>שם הכשרה</td>
		 <td><input type=text name=Name value='<?php echo $row['Name']?>'></td>	
		
		</tr>
		<tr>
	     <td>פרטים על ההכשרה:</td>
        <td><textarea name=Description rows="4" cols="30"onfocus="if(this.value == 'תיאור ההכשרה') {this.value=''}" onblur="if(this.value == ''){this.value ='תיאור ההכשרה'}"><?php print fromdb($row['Description'])?></textarea>
		</tr>
		<tr>
			<td> תפקידים שההכשרה מאפשרת: </td>
		<td>
				<?php
					//if ($result1 = $db->query("SELECT * FROM mngr_Dutys"))
					//{
					//	while ($row1 = $result1->fetch_assoc())
					//	{
					//		print "<input type=checkbox class=job_checkbox' name='jobID[]' value=".$row1["DutyID"]."> ".$row1["Name']."<br>";
					//	}
					//}
					//$result1->free();
				if ($result1 = $db->query("SELECT * FROM mngr_Dutys"))
				{
				    $sel = "";
					while ($row1 = $result1->fetch_assoc())
					{
						if (SearchInMulti($row['Dutys'],$row1['DutyID']))
						{   $sel = " checked";
							print "<input type=checkbox  name='duty_IDS[]' value='".$row1['DutyID']."'" .$sel."> ".$row1['Name']."<br>";

						} else {
							print "<input type=checkbox  name='duty_IDS[]' value=".$row1['DutyID']."> ".$row1['Name']."<br>";
						}
					}
				}
				$result1->free();
					
					
					
				?>
		</td>
		</tr>
		<tr>
		<td colspan=2 align=center><input type=submit  value='עדכן הכשרה'></td>
		</tr>
		<?php
		}
		?>
		</table>
		
		<?php
		}

		?>
		</form>
		</div>
</div>
<?php
	$db->close();
	}
?>

<?php include "footer.php"; ?>
