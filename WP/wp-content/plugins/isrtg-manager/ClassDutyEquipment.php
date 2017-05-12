<?php
	require_once 'functions.php';
	$categories = array();
	define("ITEM_MAX_AMOUNT",20);
	
	class DutyEquipment {
		public $equipmentItems = array();
		public $dutyID = 0;
		public $JoomlaID = 0;
		public $mode = 'Day';
		
		public function __construct($_dutyid,$_mode) {
			$this->dutyID = $_dutyid;
			$this->mode = $_mode;
		}
		
		public static function BuildItem($dutyid,$mode="Day") {
			$db = db_Connect();
			$result = $db->query("SELECT * FROM mngr_DutyEquipment WHERE DutyID=".$dutyid." AND Mode='".$mode."'");
			if (!$result) return;
			$row = $result->fetch_assoc();
			if ($row["Data"]!="") {
				$instance = unserialize($row["Data"]);
				$instance->dutyID = $dutyid;
				$instance->mode = $mode;
				//JoomlaID
			} else	
				$instance = new DutyEquipment($dutyid,$mode);
			$db->close();
			return $instance;
		}
		
		public function AddItem($itemID,$loc,$amount) {
			$this->equipmentItems[] = Array($itemID,$loc,$amount);
			$this->Save();
		}
		
		public function DeleteItem($itemID) {
			unset($this->equipmentItems[$itemID]);
			$this->equipmentItems = array_merge($this->equipmentItems);
			$this->Save();
		}
		
		public function Copy($dutyid,$type="Day") {
			$this->Save($dutyid, $type);
		}
		
		public function EditItemLoc($itemIndex, $loc) {
			$this->equipmentItems[$itemIndex][1] = $loc;
			$this->Save();
		}
		
		public function EditItemAmount($itemIndex, $amount) {
			$this->equipmentItems[$itemIndex][2] = $amount;
			$this->Save();
		}
		
		public function Save($dutyid=0, $mode="", $jID=0) {
			if ($mode=="")
				$mode = $this->mode;
			if ($dutyid==0)
				$dutyid = $this->dutyID;
			$db = db_Connect();
			$res = $db->query("SELECT EquipmentID FROM mngr_DutyEquipment WHERE DutyID=".$dutyid." AND Mode='".$mode."' AND JoomlaID=".$jID);
			$ttlRows = $res->num_rows;
			if ($ttlRows>0)
				$res = $db->query("UPDATE mngr_DutyEquipment SET Data='".serialize($this)."' WHERE DutyID=".$dutyid." AND Mode='".$mode."' AND JoomlaID=".$jID);
			else
				$res = $db->query("INSERT mngr_DutyEquipment (JoomlaID,DutyID,Mode,Data) values(".$jID.",".$dutyid.",'".$mode."','".serialize($this)."')");
			$db->close();
			return $res;
		}
		
		public function DoPrint($promptFunc,$itemsList,$type) {
			$res = "<tr style='background-color:#9F9F9F;font-weight:bold;text-align:center;'><td width=50%>ציוד בגוף</td><td width=50%>ציוד במדים</td></tr>";
			
			$res .= "<tr style='font-weight:bold;text-align:center;'>";
				$res .= "<td valign=top>".$this->PrintSection("גוף",$promptFunc,$type)."</td>";
				$res .= "<td valign=top>".$this->PrintSection("במדים",$promptFunc,$type)."</td>";
			$res .= "</tr>";
			
			$res .= "<tr style='background-color:#9F9F9F;font-weight:bold;text-align:center;'><td width=50%>ציוד בווסט</td><td width=50%>ציוד בתיק</td></tr>";
			$res .= "<tr style='font-weight:bold;text-align:center;'>";
				$res .= "<td valign=top>".$this->PrintSection("ווסט",$promptFunc,$type)."</td>";
				$res .= "<td valign=top>".$this->PrintSection("תיק",$promptFunc,$type)."</td>";
			$res .= "</tr>";
			
			$res .= "<tr style='background-color:#9F9F9F;font-weight:bold;text-align:center;'><td width=50%>מדים</td><td width=50%>קסדות</td></tr>";
			$res .= "<tr style='font-weight:bold;text-align:center;'>";
				$res .= "<td valign=top>".$this->PrintSection("מדים",$promptFunc,$type)."</td>";
				$res .= "<td valign=top>".$this->PrintSection("קסדות",$promptFunc,$type)."</td>";
			$res .= "</tr>";
			
			$res .= "<tr style='background-color:#9F9F9F;font-weight:bold;text-align:center;'><td width=50%>ווסטים</td><td width=50%>תיקים</td></tr>";
			$res .= "<tr style='font-weight:bold;text-align:center;'>";
				$res .= "<td valign=top>".$this->PrintSection("ווסטים",$promptFunc,$type)."</td>";
				$res .= "<td valign=top>".$this->PrintSection("תיקים",$promptFunc,$type)."</td>";
			$res .= "</tr>";
			
			$res .= "<tr style='background-color:#9F9F9F;font-weight:bold;text-align:center;'><td colspan=2>הוסף ציוד חדש לתפקיד זה:</td></tr>";
			$res .= "<tr style='font-weight:bold;text-align:center;'>";
			$res .= "<td valign=top colspan=2><form method=post><input type=hidden name='mode' value='dutysItem'><input type=hidden name=modeType value='".$type."'><input type=hidden name=action value='dutyItem_add'><input type=hidden name=dutyid value='".$this->dutyID."'><select name=itemToAdd><option value=''>בחר ציוד</option>";
			$res .= $itemsList;
			$res .= "</select> <select name=amount dir=ltr>";
			for ($i=1; $i <= ITEM_MAX_AMOUNT; $i++) {
				$res .= "<option value='".$i."'>".$i." x </option>";
			}
			$res .= "</select> <select name=loc>";
			$res .= "<option value=''>בחר מיקום</option><option value='גוף'>בגוף</option><option value='במדים'>במדים</option><option value='ווסט'>בווסט</option><option value='תיק'>בתיק</option><option value='מדים'>מדים</option><option value='קסדות'>קסדות</option><option value='ווסטים'>ווסטים</option><option value='תיקים'>תיקים</option>";
			$res .= "</select> <input type=submit value='הוסף'></form></td>";
			$res .= "</tr>";
			return $res;
		}
		
		function PrintSection($section,$promptFunc,$type) {
			$res = "";
			$res .= "<table border=1 style='border-collapse: collapse;' width=100%>";
			for ($i=0; $i < count($this->equipmentItems); $i++) {
				if ($this->equipmentItems[$i][1]!=$section) continue; //skip if not in section
				$currItem = DutyEquipmentItem::BuildItemByID($this->equipmentItems[$i][0]);
				$amount = "";
				if ($this->equipmentItems[$i][2]>1)
					$amount = "<p style='display:inline;font-size:14px;' dir=ltr>".$this->equipmentItems[$i][2]." x</p>";
				$res .= "<tr style='font-weight:bold;text-align:center;'>";
				$res .= "<td><p style='display:inline;font-size:14px;' dir=rtl>".$currItem->hebName."</p> ".$amount."<br>";
				$btn = "";
				if ($section!="גוף" && array_search('גוף', $currItem->availLocations)!==false) {
					if ($btn!="")
						$btn .= " | ";
					$btn .= "<a href=?mode=dutysItem&modeType=".$type."&action=move&dutysel=".$this->dutyID."&itemindex=".$i."&loc=גוף>העבר ל- בגוף</a>";
				}
				if ($section!="במדים" && array_search('במדים', $currItem->availLocations)!==false) {
					if ($btn!="")
						$btn .= " | ";
					$btn .= "<a href=?mode=dutysItem&modeType=".$type."&action=move&dutysel=".$this->dutyID."&itemindex=".$i."&loc=במדים>העבר ל- במדים</a>";
				}
				if ($section!="ווסט" && array_search('ווסט', $currItem->availLocations)!==false) {
					if ($btn!="")
						$btn .= " | ";
					$btn .= "<a href=?mode=dutysItem&modeType=".$type."&action=move&dutysel=".$this->dutyID."&itemindex=".$i."&loc=ווסט>העבר ל- בווסט</a>";
				}
				if ($section!="תיק" && array_search('תיק', $currItem->availLocations)!==false) {
					if ($btn!="")
						$btn .= " | ";
					$btn .= "<a href=?mode=dutysItem&modeType=".$type."&action=move&dutysel=".$this->dutyID."&itemindex=".$i."&loc=תיק>העבר ל- בתיק</a>";
				}
				
				if ($btn!="")
					$btn .= "<br>";
				$res .= "<p style='font-size:12px;display:inline;'>".$btn."<a href=javascript:void(0); onClick=".$promptFunc."('".$type."',".$this->dutyID.",".$i.",".$this->equipmentItems[$i][2].");>שנה כמות</a> | <a href=?mode=dutysItem&modeType=".$type."&action=delete&dutysel=".$this->dutyID."&itemindex=".$i.">מחק</a></p></td>";
				$res .= "</tr>";
			}
			$res .= "</table>";
			return $res;
		}
		
		function Dump() {
			$rest = "";
			$res_us = "";
			$res_vs = "";
			$res_hs = "";
			$res_bps = "";
			for ($i=0; $i < count($this->equipmentItems); $i++) {
				$currItem = DutyEquipmentItem::BuildItemByID($this->equipmentItems[$i][0]);
				if (DutyEquipment::translateLoc($currItem->type)=="us") {//Uniforms
					if ($res_us!="")
						$res_us .= "~~";
					$res_us .= DutyEquipment::translateType($currItem->type)."||".DutyEquipment::translateLoc($this->equipmentItems[$i][1])."||".$currItem->name."||".$this->equipmentItems[$i][2];
				}else if (DutyEquipment::translateLoc($currItem->type)=="vs") {//vests
					if ($res_vs!="")
						$res_vs .= "~~";
					$res_vs .= DutyEquipment::translateType($currItem->type)."||".DutyEquipment::translateLoc($this->equipmentItems[$i][1])."||".$currItem->name."||".$this->equipmentItems[$i][2];
				}else if (DutyEquipment::translateLoc($currItem->type)=="hs") {//helmets
					if ($res_hs!="")
						$res_hs .= "~~";
					$res_hs .= DutyEquipment::translateType($currItem->type)."||".DutyEquipment::translateLoc($this->equipmentItems[$i][1])."||".$currItem->name."||".$this->equipmentItems[$i][2];
				}else if (DutyEquipment::translateLoc($currItem->type)=="bps") {//backpacks
					if ($res_bps!="")
						$res_bps .= "~~";
					$res_bps .= DutyEquipment::translateType($currItem->type)."||".DutyEquipment::translateLoc($this->equipmentItems[$i][1])."||".$currItem->name."||".$this->equipmentItems[$i][2];
				} else {
					if ($rest!="")
						$rest .= "~~";
					$rest .= DutyEquipment::translateType($currItem->type)."||".DutyEquipment::translateLoc($this->equipmentItems[$i][1])."||".$currItem->name."||".$this->equipmentItems[$i][2];
				}
			}
			$ttl = "";
			$ttl = $res_us;
			if ($ttl!="")
				$ttl .= "~~";
			$ttl .= $res_vs;
			if ($ttl!="")
				$ttl .= "~~";
			$ttl .= $res_hs;
			if ($ttl!="")
				$ttl .= "~~";
			$ttl .= $res_bps;
			if ($ttl!="")
				$ttl .= "~~";
			$ttl .= $rest;
			return $ttl;
		}
		
		static function translateLoc($loc) {
			$res = "unknown";
			switch ($loc) {
				case "גוף":
					$res = "b";
					break;
				case "במדים":
					$res = "u";
					break;
				case "ווסט":
					$res = "v";
					break;
				case "תיק":
					$res = "bp";
					break;
				case "מדים":
					$res = "us";
					break;
				case "קסדות":
					$res = "hs";
					break;
				case "ווסטים":
					$res = "vs";
					break;
				case "תיקים":
					$res = "bps";
					break;
			}
			return $res;
		}
		
		static function translateType($type) {
			$res = "unknown";
			switch ($type) {
				case "מחסניות":
					$res = "2";
					break;
				case "נשקים":
					$res = "1";
					break;
				case "תוספות לנשקים עיקריים":
					$res = "3";
					break;
				case "תוספות לנשקים משניים":
					$res = "4";
					break;
				case "חפצים":
					$res = "5";
					break;
				case "מדים": //TODO: add ARMA command number
					$res = "6";
					break;
				case "קסדות":
					$res = "7";
					break;
				case "ווסטים":
					$res = "8";
					break;
				case "תיקים":
					$res = "9";
					break;
					
			}
			return $res;
		}
	}
	
	class DutyEquipmentItem {
		public $id;
		public $type;
		public $name;
		public $hebName;
		public $amount;
		public $availLocations;
		
		public function __construct($_id, $_type, $_name, $_hebName, $_amount, $_locations) {
			$this->id = $_id;
			$this->type = $_type;
			$this->name = $_name;
			$this->hebName = $_hebName;
			$this->amount = $_amount;
			$this->availLocations = $_locations;
		}
		
		public static function BuildItem($row) {
			if ($row["AvailableLocations"]=="")
				$availLocationsArray = array();
			else
				$availLocationsArray = unserialize($row["AvailableLocations"]);
			$instance = new self($row["ItemID"],$row["Type"], $row["Name"], $row["HebName"], 0, $availLocationsArray);
			return $instance;
		}
		
		public static function BuildItemByID($id) {
			$db = db_Connect();
			$result = $db->query("SELECT * FROM mngr_EquipmentItems WHERE ItemID=".$id);
			if (!$result) return;
			$row = $result->fetch_assoc();
			$db->close();
			return DutyEquipmentItem::BuildItem($row);
		}
		
		//Saves the item into the database. if ID==0 inserting it as a new line.
		public function Save() {
			$res = true;
			$db = db_Connect();
			if ($this->type=="מדים" || $this->type=="קסדות" || $this->type=="ווסטים" || $this->type=="תיקים")
					$this->availLocations = array($this->type);
					
			if ($this->id==0) {
				$res = $db->query("INSERT INTO mngr_EquipmentItems(Type,Name,HebName,AvailableLocations) values('".$this->type."','".$this->name."','".$this->hebName."','".serialize($this->availLocations)."')");
			} else {
				$res = $db->query("UPDATE mngr_EquipmentItems SET Type='".$this->type."', Name='".$this->name."', hebName='".$this->hebName."', AvailableLocations='".serialize($this->availLocations)."' WHERE ItemID=".$this->id);
			}
			$db->close();
			return $res;
		}
		
		public function Delete() {
			$res = true;
			addlog(fm_getSessionID(),"מחק את \"".$this->hebName."\" מהגדרות הציוד");
			$db = db_Connect();
			$res = $db->query("DELETE FROM mngr_EquipmentItems WHERE ItemID=".$this->id);
			$db->close();
			return $res;
		}
		
		public function DoAdminPrint() {
			$types_location_disabled = array('מדים','קסדות','ווסטים','תיקים');
			$res = "<tr>";
			$res .= "<td style='font-weight:bold;text-align:center;'><input type=text name='item".$this->id."_hebName' value='".$this->hebName."'></td>";
			$res .= "<td style='font-weight:bold;text-align:center;'><input type=text name='item".$this->id."_name' value='".$this->name."' dir=ltr></td>";
			$res .= "<td style='font-weight:bold;text-align:center;'>";
			if (array_search($this->type,$types_location_disabled)===false) {
				$res .= "<input type=checkbox name='item".$this->id."_AvailLocations[]' ".((array_search('גוף', $this->availLocations)!==false) ? "checked":"")." value='גוף'> בגוף";
				$res .= "<input type=checkbox name='item".$this->id."_AvailLocations[]' ".((array_search('במדים', $this->availLocations)!==false) ? "checked":"")." value='במדים'> במדים ";
				$res .= "<input type=checkbox name='item".$this->id."_AvailLocations[]' ".((array_search('ווסט', $this->availLocations)!==false) ? "checked":"")." value='ווסט'> בווסט";
				$res .= "<input type=checkbox name='item".$this->id."_AvailLocations[]' ".((array_search('תיק', $this->availLocations)!==false) ? "checked":"")." value='תיק'> בתיק<br>";
				$res .= "<input type=checkbox name='item".$this->id."_AvailLocations[]' ".((array_search('מדים', $this->availLocations)!==false) ? "checked":"")." value='מדים'> מדים";
				$res .= "<input type=checkbox name='item".$this->id."_AvailLocations[]' ".((array_search('קסדות', $this->availLocations)!==false) ? "checked":"")." value='קסדות'> קסדות";
				$res .= "<input type=checkbox name='item".$this->id."_AvailLocations[]' ".((array_search('ווסטים', $this->availLocations)!==false) ? "checked":"")." value='ווסטים'> ווסטים";
				$res .= "<input type=checkbox name='item".$this->id."_AvailLocations[]' ".((array_search('תיקים', $this->availLocations)!==false) ? "checked":"")." value='תיקים'> תיקים";
			} else {
				$res .= "שינוי מיקום לא אפשרי";
			}
			$res .= "</td><td>";
			$res .= "<input type=checkbox name='item".$this->id."_delete' value='1'> מחק";
			$res .= "</td></tr>";
			return $res;
		}
	}
?>