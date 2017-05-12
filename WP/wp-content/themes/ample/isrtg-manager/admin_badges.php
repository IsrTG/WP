<?php
/*
 * Template Name: isrtg_admin_badges
 */
include "admin_Menu.php";
include "header.php";
?>

	<?php
	if (PageRank($perm_Admin_Manager,"אינך מורשה להיכנס לעמוד זה")) {
	$db = db_Connect();
	$mode = getFieldg('mode'); 
	$BadgeID = getFieldg('bid');
	$msg = 0;

    if ($mode =="new" && getField("action")=="add")
	{
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		$temp = explode(".", $_FILES["file"]["name"]);
		$extension = end($temp);
		//$_FILES["file"]["size"] < 20000 size limitation
		//Verify file is an Image
		if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/png")) && in_array($extension, $allowedExts))
		{
			if ($_FILES["file"]["error"] > 0)
			{
				$msg = 3;
			} else {
				$db->query("INSERT INTO mngr_Badges (Name,Description,ImageExt) VALUES ('".toDBp('Name')."','".toDBp('Description')."','".$extension."')");
				$fname = $db->insert_id.".".$extension;
				//check if file already exists
				if (file_exists($server_local_path."/mngr/images/badges/".$fname))
				{
					unlink($server_local_path."/mngr/images/badges/".$fname); //delete file
				}
				move_uploaded_file($_FILES["file"]["tmp_name"],dirname(__FILE__)."/images/badges/".$fname); //copy new uploaded file to correct dir
				addlog(fm_getSessionID(),"הוסיף עיטור חדש: ".toDBp('Name'));
				$msg = 1;
				$mode= "";
			}
		} else {
			$msg = 4;
		}
	}
	if ($mode =="update" && getField("action")=="edit")
	{
		if ($_FILES["file"]["name"])
		{
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			$temp = explode(".", $_FILES["file"]["name"]);
			$extension = end($temp);
			if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/png")) && in_array($extension, $allowedExts))
			{
				if ($_FILES["file"]["error"] > 0)
				{
					$msg = 3;
				} else {
					$fname = $BadgeID.".".$extension;
					//check if file already exists
					if (file_exists($server_local_path."/mngr/images/badges/".$fname))
					{
						unlink($server_local_path."/mngr/images/badges/".$fname); //delete file
					}
					move_uploaded_file($_FILES["file"]["tmp_name"],dirname(__FILE__)."/images/badges/".$fname); //copy new uploaded file to correct dir
					$db->query("UPDATE mngr_Badges SET Name='".toDBp('Name')."', Description='".toDBp('Description')."', ImageExt='".$extension."' WHERE BadgeID=".$BadgeID);
					$msg = 2;
					$mode= "";
				}
			} else {
				$msg = 4;
			}
		} else {
			$db->query("UPDATE mngr_Badges SET Name='".toDBp('Name')."', Description='".toDBp('Description')."' WHERE BadgeID=".$BadgeID);
		}
		addlog(fm_getSessionID(),"ערך את העיטור: ".toDBp('Name'));
		$msg = 2;
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

			window.location.href='?bid='+id+'&mode=update';
	}
	
</script>


<div id="IsrTG_Content">
<?php
print("$MenuToPrint");
?>

<div id="Main_Clan_Managment">

	<?php
		if ($msg==1) print "<div class='Approve'>העיטור נוסף בהצלחה</div>";
		if ($msg==2) print "<div class='Approve'>העיטור עודכן בהצלחה</div>";
		if ($msg==3) print "<div class='Error'>שגיאה בהעלאת התמונה</div>";
		if ($msg==4) print "<div class='Error'>הקובץ חייב להיות תמונה</div>";
	?>

	<div class='Title'>
		<h1>ניהול עיטורים</h1>
	</div>
	<div class="MainContent">
		<table border=2 style="border-collapse: collapse;" align=center width=95%>
			<tr>
				<td colspan=2 style="font-weight:bold;text-align:center;">אנא בחר אופציה:
					<input type=button onClick="modeSel('new');" value='הוסף עיטור'>
					<input type=button onClick="modeSel('update');" value='עדכן עיטור'>
				</td>
			</tr>
		</table>
	</div>
			
			
	<?php
        if ($mode=='new')
		{
		?>
		<div class='Title'>
			<h2>הוספת עיטור</h2>
		</div>
		<div class="MainContent">
			<form method=post id="tform" enctype="multipart/form-data"><input type=hidden name=action value='add'>
			<table border=2 style="border-collapse: collapse;" align=center width=95%>
				<tr>
					<td width=20%>שם העיטור:</td>
					<td><input type=text name=Name value=''></td>	
				</tr>
				<tr>
					<td>פרטים על העיטור:</td>
					<td><textarea name=Description rows="4" cols="30"></textarea>
				</tr>
				<tr>
					<td>תמונה:</td>
					<td><input type=file name='file' id='file'></td>
				</tr>
				<tr>
					<td colspan=2 align=center><input type=button onClick="tform.submit();"  value='הוסף עיטור'></td>
				</tr>
			</table>
		</div>
		<?php
		}
		
		if ($mode=='update')
		{
		?>
		<div class='Title'>
			<h2>עריכת עיטור</h2>
		</div>
		<div class="MainContent">
			<form method=post id="tform" enctype="multipart/form-data"><input type=hidden name=action value='edit'><input type=hidden name=bid value='<?php print $BadgeID; ?>'>
			<table border=2 style="border-collapse: collapse;" align=center width=95%>
				<tr>
					<td width=20%>בחר עיטור:</td>
					<td>
						<select name="badge" onChange="quliSel(this.options[this.selectedIndex].value);"><option value="">בחר עיטור</option>
						<?php
						if ($result = $db->query("SELECT * FROM mngr_Badges"))
							{
							while ($row = $result->fetch_assoc())
								{
								if ($row['BadgeID']==$BadgeID)
									{
									print "<option value=".$row['BadgeID']." selected>".$row['Name']."</option>";
									} 
									else 
									{
									print "<option value=".$row['BadgeID'].">".$row['Name']."</option>";
									}
								}
							}
						$result->free();
						?>
						</select>
					</td>	
				</tr>
				<?php
				if (!$BadgeID=='')
					{
					if ($result = $db->query("SELECT * FROM mngr_Badges WHERE BadgeID=".$BadgeID))
						{
						$row = $result->fetch_assoc();
				?>
				<tr>
					<td>שם הכשרה</td>
					<td><input type=text name=Name value='<?php echo $row['Name']?>'></td>	
				</tr>
				<tr>
					<td>פרטים על ההכשרה:</td>
					<td><textarea name=Description rows="4" cols="30"><?php print $row['Description'] ?></textarea>
				</tr>
				<tr>
					<td>תמונה:</td>
					<td><img src='/mngr/images/badges/<?php print $row['BadgeID'].".".$row['ImageExt']?>'><br><input type=file name='file' id='file'></td>
				</tr>
				<tr>
					<td colspan=2 align=center><input type=submit value='עדכן עיטור'></td>
				</tr>
				<?php
				$result->free();
				}
				}
				?>
			</table>
			</form>
		</div>
		<?php
		}
		?>
	</div>
</div>
<?php
	$db->close();
	}
?>

<?php include "footer.php"; ?>
