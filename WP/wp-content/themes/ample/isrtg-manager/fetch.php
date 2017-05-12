<?php
/*
 * Template Name: isrtg_arma_fetch
 */
 
	$FUNC_ONLY = true;
	$db = db_Connect();
	
	$job = getFieldg("job",$db);
	
	$mode = getFieldg("mode",$db);
	if ($mode=="")
		$mode = "Day";
	if ($mode=="1")
		$mode = "Day";
	if ($mode=="2")
		$mode = "Night";
	if ($mode=="3")
		$mode = "Dive";
		
	$dutyid = GetValue("DutyID","mngr_Dutys","FIND_IN_SET('".$job."',TemplateName)"); //find duty ID contains job templateName
	if (!$dutyid)
		$dutyid = 0;
	print getFieldg("owner")."~~";
	
	$currEquipment = DutyEquipment::BuildItem($dutyid,$mode);
	print $currEquipment->Dump();
	
	$db->close();
?>