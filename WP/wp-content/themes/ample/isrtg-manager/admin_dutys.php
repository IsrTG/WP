<?php
/*
 * Template Name: isrtg_admin_dutys
 */
include "admin_Menu.php";
include "header.php";
?>

<?php
if (PageRank($perm_Admin_Manager,"אינך מורשה להיכנס לעמוד זה")) {

$db = db_Connect();
$msg = 0;
$mode = getFieldg('mode'); 
$DutyID = getFieldg('dID');

if (getField("action")=="add")
{
    $db->query("INSERT INTO mngr_Dutys (Name,TemplateName) VALUES ('".toDBp("tname")."','".toDBp("templateName")."')");
    addlog(fm_getSessionID(),"הוסיף תפקיד חדש: ".toDBp('tname'));
    $msg = 1;
}
if (getField("action")=="edit")
{
    $db->query("UPDATE mngr_Dutys SET Name='".toDBp('tname')."', TemplateName='".toDBp('templateName')."' WHERE DutyID=".toDBp("dID"));
    addlog(fm_getSessionID(),"ערך את התפקיד: ".toDBp('tname'));
    $msg = 2;
}

?>
	

<script language=javascript>
	function modeSel(choose)
	{

			window.location.href='?mode='+choose;
	}
	function quliSel(id)
	{

			window.location.href='?dID='+id+'&mode=update';
	}
	
</script>



<div id="IsrTG_Content">
<?php
print("$MenuToPrint");
?>

<div id="Main_Dutys_Managment">
	<?php if ($msg==1) print "<div class='Approve'>התפקיד הוכנס בהצלחה</div><br>"; ?>
	<?php if ($msg==2) print "<div class='Approve'>התפקיד עודכן בהצלחה</div><br>"; ?>
	
	<div class='Title'>
		<h1>ניהול תפקידים</h1>
	</div>
	<div class="MainContent">
		<table border=2 style="border-collapse: collapse;" align=center width=95%>
			<tr>
				<td colspan=2 style="font-weight:bold;text-align:center;">אנא בחר אופציה:
					<input type=button onClick="window.location.href='?mode=new';" value='הוסף תפקיד'>
					<input type=button onClick="window.location.href='?mode=update';" value='עדכן תפקיד'>
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
			<h2>הוספת תפקיד</h2>
		</div>
		<div class="MainContent">
			<input type=hidden name=action value='add'>
			<table border=2 style="border-collapse: collapse;" align=center width=95%>
				<tr>
					<td width=20%>שם התפקיד:</td>
					<td><input type=text name=tname value=''></td>	
				</tr>
				<tr>
					<td>שם בטמפלייט (הפרד בפסיקים אם יותר מאחד):</td>
					<td><input type=text name=templateName dir=ltr size=40></td>	
				<tr>
					<td colspan=2 align=center><input type=submit value='הוסף תפקיד'></td>
				</tr>
			</table>
		</div>
		<?php
		}
		
		if ($mode=='update')
		{
		?>
		<div class='Title'>
			<h2>עדכון תפקיד</h2>
		</div>
		<div class="MainContent">
			<input type=hidden name=action value='edit'><input type=hidden name=dID value='<?php print $DutyID; ?>'>
			<table border=2 style="border-collapse: collapse;" align=center width=95%>
				<tr>
					<td width=20%>בחר תפקיד:</td>
					<td>
						<select name="duty" dir=ltr onChange="quliSel(this.options[this.selectedIndex].value);"><option value="">בחר תפקיד</option>
						<?php
						if ($result = $db->query("SELECT * FROM mngr_Dutys ORDER BY DutyID"))
							{
							while ($row = $result->fetch_assoc())
								{
								if ($row['DutyID']==getFieldg("dID"))
									{
										print "<option value=".$row['DutyID']." selected>".$row['Name']."</option>";
									} 
									else
									{
										print "<option value=".$row['DutyID'].">".$row['Name']."</option>";
									}
								}
							}
						$result->free();
						?>
						</select>
					</td>	
				</tr>
				<?php
				if (!$DutyID=='')
					{
					if ($result = $db->query("SELECT * FROM mngr_Dutys WHERE DutyID=".toDBg("dID")))
						{
						$row = $result->fetch_assoc();
						?>
						<tr>
							<td>שם הכשרה</td>
							<td><input type=text name=tname value='<?php echo $row['Name']?>'></td>	
						</tr>
						<tr>
							<td>שם בטמפלייט (הפרד בפסיקים אם יותר מאחד):</td>
							<td><input type=text name=templateName dir=ltr size=40 value='<?php echo $row['TemplateName']?>'></td>	
						</tr>
						<tr>
							<td colspan=2 align=center><input type=submit value='עדכן תפקיד'></td>
						</tr>
						<?php
						}
					$result->free();
					}
				?>
			</table>
		</div>
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