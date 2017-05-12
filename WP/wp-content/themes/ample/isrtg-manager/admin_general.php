<?php
/*
 * Template Name: isrtg_admin_general
 */
include "admin_Menu.php";
include "header.php";
?>

<?php
if (PageRank($perm_Admin_Manager,"אינך מורשה להיכנס לעמוד זה")) {
$msg = "";
$db = db_Connect();
if (getField("mode")=="RegTextUpdate") {
    if (getSetting("RegText")!=getField("reg_text") && getField("reg_text")!="") { //Reg text changed
        setSetting("RegText",getField("reg_text"));
        addlog(fm_getSessionID(),"עדכן את מלל טופס ההרשמה");
    }
    if (getSetting("RegOpen")!=getField("reg_open")) { // Open/close clan registration option changed
        setSetting("RegOpen",getField("reg_open"));
        if (getField("reg_open")=="1")
            addlog(fm_getSessionID(),"הפעיל את האפשרות להירשם לקלאן");
        else
            addlog(fm_getSessionID(),"ביטל את האפשרות להירשם לקלאן");
    }
    $msg = 1;
}

if (getField("mode")=="HourDifference") {
    setSetting("HourDifference",getField("tofix"));
    $msg = 1;
    addlog(fm_getSessionID(),"עדכן את תיקון זמן המערכת ל <span dir=ltr>".getField("tofix")."</span>");
}

if (getField("mode")=="ModsManager") {
    $mods = getModList();
    if (substr(getField("action"),0,1)=="@") { //action is a mod name to delete
        $mods = array_merge(array_diff($mods, array(getField("action")))); //Remove from mods array the selected mod to remove
        setModList($mods);
        addlog(fm_getSessionID(),"מחק את המוד ".getField("action")." לרשימת המודים");
        $msg = 2;
    } else if (getField("action")=="add") {
        array_push($mods,getField("newmode"));
        setModList($mods);
        addlog(fm_getSessionID(),"הוסיף את המוד ".getField("newmode")." לרשימת המודים");
        $msg = 3;
    } else if (getField("action")=="notify") {
        $ttl_players = 0;
        $body = "שלום,<br>ברצוננו להודיע כי בוצע שינוי ברשימת המודים הרשמית של הקלאן.<br>רשימת המודים החדשה הינה:<br><div align=center dir=ltr>".implode("<br>",$mods)."</div><br>ניתן להוריד את קיצור הדרך ל Play WithSix אשר יצור רשימת מודים חדשה בצורה אוטומטית <a href='http://www.isrtg.com/launcher/IsrTG Profile.zip'>כאן</a>.<br><b>שים לב: רשימת המודים הנ\"ל נכנסת לתוקף בצורה מיידית וכלל אירועי הקלאן החל מאימייל זה יבוצעו עימם.</b><br><br>בברכה,<br>צוות IsrTG‬‎.";
        if ($result = $db->query("SELECT * FROM mngr_Players WHERE Status<=".PLAYER_STATUS_VACATION)) {
            while ($row = $result->fetch_assoc()) {
                sendmail($row["Email"],"עדכון רשימות מודים",$body);
                $ttl_players++;
            }
            $result->free();
        }

        setSetting("ModList",implode("|",$mods));
        addlog(fm_getSessionID(),"שלח מייל עדכון של רשימת המודים ל ".$ttl_players." שחקנים (שחקנים פעילים/בחופשה)");
        $msg = 4;
    }
}
?>

<div id="IsrTG_Content">
<?php
print("$MenuToPrint");
?>

<div class='Title'>
	<h1>ניהול כללי</h1>
</div>
<?php if ($msg==1) print "<div style='font-weight:bold;text-align:center;color:green;'>עודכן בהצלחה</div><br>"; ?>
<?php if ($msg==2) print "<div style='font-weight:bold;text-align:center;color:green;'>המוד נמחק בהצלחה</div><br>"; ?>
<?php if ($msg==3) print "<div style='font-weight:bold;text-align:center;color:green;'>המוד נוסף בהצלחה</div><br>"; ?>
<?php if ($msg==4) print "<div style='font-weight:bold;text-align:center;color:green;'>אימייל עדכון רשימת מודים נשלח בהצלחה ל ".$ttl_players." שחקניםֳ</div><br>"; ?>

<div class='Title'>
	<h2>שעון מערכת:</h2>
</div>
<div class="MainContent">
	<form method=post><input type=hidden name=mode value='HourDifference'>
<?php
    $dateObj = DateTime::createFromFormat("Y-m-d H:i:s", date("Y")."-".date("m")."-".date("d")." ".date("H").":".date("i").":".date("s"));
    $dateObj_Fixed = date_now();
    $currSet = getSetting("HourDifference");
    print "<b><u>שעה נוכחית לפי שרת</u></b>: ".$dateObj->format("d-m-Y H:i:s")."<br>";
    print "<b><u>שעה נוכחית לאחר תיקון (".$currSet.")</u></b>: ".$dateObj_Fixed->format("d-m-Y H:i:s")."<br>";
?>
תקן את שעון המערכת ב <select name=tofix dir=ltr><option value='+3'<?php if($currSet=="+3") print " selected"; ?>>+3</option><option value='+2'<?php if($currSet=="+2") print " selected"; ?>>+2</option><option value='+1'<?php if($currSet=="+1") print " selected"; ?>>+1</option><option value='0'<?php if($currSet=="0") print " selected"; ?>>0</option><option value='-1'<?php if($currSet=="-1") print " selected"; ?>>-1</option><option value='-2'<?php if($currSet=="-2") print " selected"; ?>>-2</option><option value='-3'<?php if($currSet=="-3") print " selected"; ?>>-3</option></select> שעות.
	<input type=submit value='עדכן'>
	</form>
	
	<script type="text/javascript">
    function submitForm(action) {
        if (action=='notify') {
            if (!confirm('האם אתה בטוח שברצונך לשלוח אימייל עידכון לכל שחקני הקלאן?')) {
                return;
            }
        }
        $("input[name='action']").val(action);
        form_modsmanager.submit();
    }
	</script>
</div>

<div class='Title'>
	<h2>ניהול מודים</h2>
</div>
<div class="MainContent">
<?php if ($msg==1) print "<div style='font-weight:bold;text-align:center;color:green;'>עודכן בהצלחה</div><br>"; ?>
	<form method=post id=form_modsmanager><input type=hidden name=mode value='ModsManager'><input type=hidden name=action value=''>
	<table border=0 align=center>
		<tr>
			<td align=center>
				<h3>מודים קיימים:</h3>
				<select name='mods' size=20 dir=ltr style='width: 200px;'>
<?php
    $mods = getModList();
    $allow_mail = false;
    if (getSetting("ModList")!=implode("|",$mods))
        $allow_mail = true;
    for ($i=0;$i<count($mods);$i++) {
        print "<option value='".$mods[$i]."'>".$mods[$i]."</option>";
    }
?>
				</select>
			</td>
			<td width=200 align=center valign=top>
				<h3>הוספת מוד חדש:</h3>
				<input type=text name="newmode" dir=ltr> <input type=button value='הוסף' onClick="submitForm('add');"><br><br>
				<br>
				<input type=button value='מחק מסומן' onClick="submitForm($('select[name=mods]').val());"> <input type=button value='הפץ מייל עדכון לכלל השחקנים' onClick="submitForm('notify');" <?php if ($allow_mail==false) print "disabled"?> >
			</td>
		</tr>
	</table>
	</form>
</div>

<div class='Title'>
	<h2>הרשמה לקלאן:</h2>
</div>
<div class="MainContent">
	<form method=post><input type=hidden name=mode value='RegTextUpdate'>
	<input type=radio name=reg_open value='0' <?php if (getSetting("RegOpen")=='0') echo 'checked'?>> <b><u>הרשמה סגורה.</u></b><br>
	<input type=radio name=reg_open value='1' <?php if (getSetting("RegOpen")=='1') echo 'checked'?>> <b><u>הרשמה פתוחה. מלל:</u></b><br><br>
	<link rel="stylesheet" href="/components/com_jce/editor/libraries/css/editor.css?version=2332" type="text/css" />
	<script data-cfasync="false" type="text/javascript" src="/components/com_jce/editor/tiny_mce/tiny_mce.js?version=2332"></script>
	<script data-cfasync="false" type="text/javascript" src="/components/com_jce/editor/libraries/js/editor.js?version=2332"></script>
	<script data-cfasync="false" type="text/javascript" src="/administrator/index.php?option=com_jce&view=editor&layout=editor&task=loadlanguages&lang=en&component_id=22&wfa4776bfd1d0383730043f55dc2e4dfe1=1&version=2332"></script>
	<script data-cfasync="false" type="text/javascript">
        try{WFEditor.init({
            token: "wfa4776bfd1d0383730043f55dc2e4dfe1",
            base_url: "http://www.isrtg.com/",
            language: "en",
            directionality: "rtl",
            theme: "advanced",
            plugins: "autolink,cleanup,core,code,colorpicker,upload,format,directionality,source,lists,textcase,browser,contextmenu,inlinepopups,media,advlist,wordcount,charmap",
            language_load: false,
            component_id: 22,
            theme_advanced_buttons1: "undo,redo,bold,italic,underline,strikethrough,justifyfull,justifycenter,justifyleft,justifyright,blockquote,ltr,rtl,source,backcolor,forecolor",
            theme_advanced_buttons2: "fontselect,fontsizeselect,indent,outdent,numlist,bullist,sub,sup,textcase",
            theme_advanced_buttons3: "",
            theme_advanced_toolbar_align: "right",
            theme_advanced_resizing: true,
            height: 600,
            content_css: "/templates/system/css/editor.css",
            toggle: 0,
            entities: "160,nbsp",
            invalid_elements: "iframe,script,style,applet,body,bgsound,base,basefont,frame,frameset,head,html,id,ilayer,layer,link,meta,name,title,xml",
            forced_root_block: "p",
            removeformat_selector: "p,address,pre,h1,h2,h3,h4,h5,h6,code,samp,span,dt,dd,b,strong,em,i,font,u,strike",
            theme_advanced_blockformats: {"advanced.paragraph":"p","advanced.div":"div","advanced.div_container":"div_container","advanced.address":"address","advanced.pre":"pre","advanced.h1":"h1","advanced.h2":"h2","advanced.h3":"h3","advanced.h4":"h4","advanced.h5":"h5","advanced.h6":"h6","advanced.code":"code","advanced.samp":"samp","advanced.span":"span","advanced.dt":"dt","advanced.dd":"dd"},
            remove_script_host: false,
            theme_advanced_fonts: "Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats",
            theme_advanced_font_sizes: "8pt,10pt,12pt,14pt,18pt,24pt,36pt",
            file_browser_callback: function(name, url, type, win){tinyMCE.activeEditor.plugins.browser.browse(name, url, type, win);},
            compress: {"javascript":0,"css":0}
        });}catch(e){console.debug(e);}
    </script>

    <center>
    <label for="jform_articletext" style="display:none;" aria-visible="false">jform_articletext_textarea</label><textarea id="jform_articletext" name="reg_text" cols="0" rows="0" style="width:90%;height:250px;" class="wfEditor mce_editable source" wrap="off"><?php print getSetting("RegText"); ?></textarea>
    <br><br>

    <input type=submit value='עדכן'>
    </center>

	</form>
	<br><br>
	<b><u>תצוגה מקדימה:</u></b><br>
	<br>
<?php
if (getSetting("RegOpen")=="1") {
    print getSetting("RegText");
?>
    <p align=center><b>
            עדיין מעוניין?
            <input type=button onClick="modeSel('accept');" value='הצטרף' disabled>
    </p>
<?php
} else {
    print "שלום לך שחקן יקר,<br>הנהלת הקלאן מודה לך שבחרת להתחיל בתהליך הצטרפות לקלאן Israeli Tactical Gaming.<br>לצערנו, הקלאן לא מגייס שחקנים חדשים בזמן זה.<br>אנא בדוק עמוד זה בשנית בעוד מספר שבועות או צור איתנו קשר באימייל.<br><br>בברכה,<br>הנהלת הקלאן.";
}
?>
</div>

</div>
<?php
$db->close();
}
?>

<?php include "footer.php"; ?>