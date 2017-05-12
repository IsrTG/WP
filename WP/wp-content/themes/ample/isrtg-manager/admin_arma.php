<?php
/*
 * Template Name: isrtg_admin_arma
 */
include "admin_Menu.php";
include "header.php";
?>

<?php
	ini_set('pcre.backtrack_limit', 300000);
	
	$gMsg = "";
	$bMsg = "";
	if (PageRank($perm_Admin_Manager,"אינך מורשה להיכנס לעמוד זה")) {
	$db = db_Connect();
?>

<script language=javascript>
	function setAmount(type,dutyID,itemID,currAmount) {
		var res = prompt('הכנס כמות',currAmount);
		//var data = {action: 'updateAmount', dutyid: dutyID, itemindex: itemID, amount: res};
		//var jq = $.post("admin-arma", data);
		window.location.href = "admin-arma?mode=dutysItem&modeType=" + type + "&action=updateAmount&dutysel=" + dutyID + "&itemindex=" + itemID + "&amount=" + res;
	}
</script>


<div id="IsrTG_Content">
<?php
print("$MenuToPrint");
?>

<div id="Admin_Server_Managment">
	<div class='Title'>
		<h1>ניהול שרת משחק</h1>
	</div>
	<div class="MainContent Center">
		<center><a href=?mode=items>הגדרת ציוד</a> | <a href=?mode=dutysItem>חלוקת ציוד</a></center>
	</div>

<?php 
		if ($gMsg!="")
			print "<div class='Approve'>".$gMsg."</div>"; 
		if ($bMsg!="")
			print "<div class='Error'>".$bMsg."</div>"; 
		?>
	
<?php
	if (getFieldg("mode",$db)=="items") {
	if (getField("action",$db)=="addItem") {
		$valid = true;
		$itemAvailLocations = getField("itemAvailLocations",$db);
		$itemType = getField("itemType",$db);
		
		if ($itemType=="בחר" || getField("itemName",$db)=="" || getField("itemHebName",$db)=="")
			$valid = false;
		if ($itemType!="מדים" && $itemType!="קסדות" && $itemType!="ווסטים" && $itemType!="תיקים" && count($itemAvailLocations)<=0)
			$valid = false;
			
		if ($valid) {
			$newItem = new DutyEquipmentItem(0,$itemType,getField("itemName",$db),getField("itemHebName",$db),0,$itemAvailLocations);
			
			//$db->query("INSERT INTO mngr_EquipmentItems(Type,Name,HebName,AvailableLocations) values('".getField("itemType")."','".getField("itemName")."','".getField("itemHebName")."','".serialize(getField("itemAvailLocations"))."')");
			if ($newItem->Save())
				$gMsg = "הציוד נוסף בהצלחה";
			else
				$bMsg = "שגיאה בהוספת ציוד";
		} else {
			$bMsg = "שגיאה בהוספת ציוד";
		}
	}
	
	if (getField("action",$db)=="editItem") {
		if ($result = $db->query("SELECT * FROM mngr_EquipmentItems ORDER BY Type,ItemID")) {
			while ($row = $result->fetch_assoc()) {
				$currItem = DutyEquipmentItem::BuildItem($row);
				if (getField("item".$currItem->id."_delete",$db)=="1") {
					if ($currItem->Delete())
						$gMsg = "'".$currItem->hebName."' נמחק בהצלחה";
					else
						$bMsg = "שגיאה במחיקת '".$currItem->hebName."'";
				} else {
					$currItem->name = getField("item".$currItem->id."_name",$db);
					$currItem->hebName = getField("item".$currItem->id."_hebName",$db);
					$currItem->availLocations = getField("item".$currItem->id."_AvailLocations",$db);
					$currItem->Save();
				}
			}
			$result->free();
		}
	}
	
	//$msg = getFieldg("msg");
	
?>	
	<div class='Title'>
		<a name=1></a><h2>הגדרת ציוד</h2>
	</div>
	<div class="MainContent">
		<form method=post><input type=hidden name=action value='editItem'>
		<table border=1 style="border-collapse: collapse;" align=center width=70%>
	<?php
		$lastType = "";
		if ($result = $db->query("SELECT * FROM mngr_EquipmentItems ORDER BY Type,name"))
		{
			while ($row = $result->fetch_assoc()) {
				$currItem = DutyEquipmentItem::BuildItem($row);
				if ($row["Type"]!=$lastType) {
					print "<tr><td colspan=4 style='height: 20px; font-weight:bold;text-align:center;'></td></tr>";
					print "<tr><td colspan=4 style='font-weight:bold;text-align:center;background-color:black;'>".$row["Type"]."</td></tr>";
					print "<tr><td style='font-weight:bold;text-align:center;'>שם</td><td style='font-weight:bold;text-align:center;'>שם במשחק</td><td style='font-weight:bold;text-align:center;'>מקומות אפשריים</td><td style='font-weight:bold;text-align:center;'>מחיקה</td></tr>";
					
				}
				print $currItem->DoAdminPrint();
				$lastType = $row["Type"];
			}
			$result->free();
		}
	?>
		</table><br>
		<input type=submit value='עדכן ציוד'>
		</form>
	</div>
	
	<div class='Title'>
		<h2>הוספת ציוד חדש:</h2>
	</div>
	<div class="MainContent">
	
		<form method=post><input type=hidden name=action value='addItem'>
		<table border=1 style="border-collapse: collapse;" align=center width=50%>
			<tr>
				<td>שם: <input type=text name=itemHebName value='<?php if($bMsg!="") print getField("itemHebName") ?>'></td>
				<td>שם במשחק: <input type=text name=itemName dir=ltr value='<?php if($bMsg!="") print getField("itemName")?>'></td>
				<td>קטגוריה: <select name=itemType><option value='בחר'>בחר</option><option value='חפצים'>חפצים</option><option value='נשקים'>נשקים</option><option value='תוספות לנשקים עיקריים'>תוספות לנשקים עיקריים</option><option value='תוספות לנשקים משניים'>תוספות לנשקים משניים</option><option value='מחסניות'>מחסניות</option><option value='מדים'>מדים</option><option value='קסדות'>קסדות</option><option value='ווסטים'>ווסטים</option><option value='תיקים'>תיקים</option></select></td>
			</tr>
			<tr>
				<td colspan=3>
					אפשרויות מיקום לפריט:
					<input type=checkbox name=itemAvailLocations[] value='גוף'> בגוף
					<input type=checkbox name=itemAvailLocations[] value='במדים'> במדים
					<input type=checkbox name=itemAvailLocations[] value='ווסט'> בווסט
					<input type=checkbox name=itemAvailLocations[] value='תיק'> בתיק
					<br>
					(קטגוריות: מדים, קסדות, ווסטים ותיקים יסומנו במיקומם אוטומטית ולכן אין חובה לבחור מיקום)
					<br><br>
					<b>בחר ווסט</b> - אם ניתן לבצע addItemToVest על הפריט.<br>
					<b>בחר תיק</b> - אם ניתן לבצע addItemCargoGlobal על הפריט.<br>
				</td>
			</tr>
		</table>
		<br>
		<input type=submit value='הוסף ציוד'>
		</form>
	</div>
<?php
	}
	
	if (getFieldg("mode",$db)=="dutysItem" || getField("mode",$db)=="dutysItem") {
		if (getFieldg("action",$db)=="updateAmount") {
			if (is_numeric(getFieldg("dutysel",$db)) && is_numeric(getFieldg("itemindex",$db)) && is_numeric(getFieldg("amount",$db))) {
				$currEquipment = DutyEquipment::BuildItem(getFieldg("dutysel",$db),getFieldg("modeType",$db));
				$currEquipment->EditItemAmount(getFieldg("itemindex",$db),getFieldg("amount",$db));
			}
			header("Location: admin-arma?mode=dutysItem&modeType=".getFieldg("modeType",$db)."&dutysel=".getFieldg("dutysel",$db));
		}
		
		if (getFieldg("action",$db)=="move") {
			if (is_numeric(getFieldg("dutysel",$db)) && is_numeric(getFieldg("itemindex",$db))) {
				$currEquipment = DutyEquipment::BuildItem(getFieldg("dutysel",$db),getFieldg("modeType",$db));
				$currEquipment->EditItemLoc(getFieldg("itemindex",$db),getFieldg("loc",$db));
			}
			header("Location: admin-arma?mode=dutysItem&modeType=".getFieldg("modeType",$db)."&dutysel=".getFieldg("dutysel",$db));
		}
		
		if (getFieldg("action",$db)=="copy") {
			if (is_numeric(getFieldg("fromDutysel",$db))) {
				$currEquipment = DutyEquipment::BuildItem(getFieldg("fromDutysel",$db),getFieldg("fromModeType",$db));
				$currEquipment->Copy(getFieldg("dutysel",$db),getFieldg("modeType",$db));
			}
			header("Location: admin-arma?mode=dutysItem&modeType=".getFieldg("modeType",$db)."&dutysel=".getFieldg("dutysel",$db));
		}
		
		if (getFieldg("action",$db)=="delete") {
			if (is_numeric(getFieldg("dutysel",$db)) && is_numeric(getFieldg("itemindex",$db))) {
				$currEquipment = DutyEquipment::BuildItem(getFieldg("dutysel",$db),getFieldg("modeType",$db));
				$currEquipment->DeleteItem(getFieldg("itemindex",$db));
			}
			header("Location: admin-arma?mode=dutysItem&modeType=".getFieldg("modeType",$db)."&dutysel=".getFieldg("dutysel",$db));
		}

		if (getField("action",$db)=="dutyItem_add") {
			$currEquipment = DutyEquipment::BuildItem(getField("dutyid",$db),getField("modeType",$db));
			$currEquipment->AddItem(getField("itemToAdd",$db),getField("loc",$db),getField("amount",$db));
		}
		?>
		
		
		
		
		<div class='Title'>
			<a name=2></a><h2>חלוקת ציוד</h2>
		</div>
		
		<div class="MainContent">
			<form method=get>
			<input type=hidden name='mode' value='dutysItem'>
			<center>בחר מצב: <select name=modeType>
								<option value='Day' <?php print ((getFieldg("modeType",$db)=="Day") ? "selected":"")?>>יום</option>
								<option value='Night' <?php print ((getFieldg("modeType",$db)=="Night") ? "selected":"")?>>לילה</option>
								<option value='Dive' <?php print ((getFieldg("modeType",$db)=="Dive") ? "selected":"")?>>צלילה</option>
							</select>
							
			בחר תפקיד: <select name='dutysel'>
		<?php
		if ($result = $db->query("SELECT * FROM mngr_Dutys ORDER BY DutyID")) {
			while ($row = $result->fetch_assoc()) {
				print "<option value='".$row["DutyID"]."'".((getFieldg("dutysel",$db)==$row["DutyID"])?" selected":"").">".$row["Name"]."</option>";
			}
			$result->free();
		}
		print "</select> <input type=submit value='הצג'></center></form>";
?>
		
		
<?php
		if (getFieldg("dutysel",$db)!="") {
			//Get list of items for 'item to add' selection in each duty
			$itemsOptions = "";
			if ($result = $db->query("SELECT * FROM mngr_EquipmentItems ORDER BY Type,hebName")) {
				$currType = "";
				while ($row = $result->fetch_assoc()) {
					$currItem = DutyEquipmentItem::BuildItem($row);
					if ($currType!=$currItem->type) {
						$itemsOptions .= "<option value=''>======".$currItem->type."======</option>";
						$currType = $currItem->type;
					}
					$itemsOptions .= "<option value='".$currItem->id."'>  ".$currItem->hebName."</option>";
				}
				$result->free();
			}
			
			$currEquipment = DutyEquipment::BuildItem(getFieldg("dutysel",$db),getFieldg("modeType",$db));
			if ($currEquipment) {
				$dutyName = GetValue("Name","mngr_Dutys","DutyID=".getFieldg("dutysel",$db));
				print "<table border=1 style='border-collapse: collapse;' align=center width=50%>";
				print "<tr><td colspan=3 style='font-weight:bold;text-align:center;background-color:#707070;'>".$dutyName."</td></tr>";
				print "<tr><td colspan=3 style='font-weight:bold;text-align:center;'></td></tr>";
				print $currEquipment->DoPrint("setAmount",$itemsOptions,getFieldg("modeType",$db));
				print "</table><br>";
			}
			?>
			<br><center><b><u>העתק ציוד מתפקיד אחר</u></b></center><br>
			<form method=get>
			<input type=hidden name='mode' value='dutysItem'>
			<input type=hidden name='action' value='copy'>
			<input type=hidden name='modeType' value='<?php print getFieldg("modeType",$db) ?>'>
			<input type=hidden name='dutysel' value='<?php print getFieldg("dutysel",$db) ?>'>
			<center>מצב: <select name=fromModeType>
				<option value='Day' <?php print ((getFieldg("modeType",$db)=="Day") ? "selected":"")?>>יום</option>
				<option value='Night' <?php print ((getFieldg("modeType",$db)=="Night") ? "selected":"")?>>לילה</option>
				<option value='Dive' <?php print ((getFieldg("modeType",$db)=="Dive") ? "selected":"")?>>צלילה</option>
			</select>
			תפקיד: <select name='fromDutysel'><option value=''>בחר</option>
			<?php
			if ($result = $db->query("SELECT * FROM mngr_Dutys ORDER BY DutyID")) {
				while ($row = $result->fetch_assoc()) {
					print "<option value='".$row["DutyID"]."'>".$row["Name"]."</option>";
				}
				$result->free();
			}
			print "</select> <input type=submit value='העתק'></center></form><br>";
		}
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