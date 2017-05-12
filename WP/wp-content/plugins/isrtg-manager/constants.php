<?php
	//Frameworks
	define("FM_WORDPRESS","");
	//define("FM_JOOMLA", "");
	
	//Settings
	define("SERVER_PATH",dirname(__FILE__)."/../../../../");
	define("JOOMLA_PREFIX","r0ky9");
	define("DAYS_TO_VETERAN",365); // define after how many days a player will be promoted to veteran member
	define("PLAYER_DEF_RANK",5); //Define the rank to grant a new registered player
	define("TS_ADDRESS","62.75.151.35");
	define("TS_PORT","1313");
	define("GAME_SERVER_IP","82.81.169.162");
	define("GAME_SERVER_PORT","2314");
	$perm_Admin_Manager = 2;
	$perm_Admin_Segel = 2;
	$server_local_path = "/var/www/vhosts/isrtg.com";
	define("PERM_MIN_RANK", 5);
	define("BACKUPS_TO_SAVE", 60); //backups files to save
	define("DUTIES_SET_ALLOWED_NO_QULI", 24); // Determines which duties doesn't need qualifications to achieve them (24=recruit)
	define("UserProfilePage","/חברי-הקלאן/?pid=");
	
	//Consts
	define("PLAYER_STATUS_ACTIVE",0);
	define("PLAYER_STATUS_VACATION",1);
	define("PLAYER_STATUS_NOT_ACTIVE",2);
	define("PLAYER_STATUS_KICKED",3);
	define("PLAYER_STATUS_INTERVIEW_STAGE",4);
	define("PLAYER_STATUS_NEW",5);
	
	// Time zone
	date_default_timezone_set("Israel");
	
	//Email Bodys
	define("EmailAddress","info@isrtg.com");
	//define("WebmasterEmailAddress","israelitacticalgaming@gmail.com");
	define("WebmasterEmailAddress","webmaster@isrtg.com");
	$email_body_player_new_register = "";
	$email_body_player_to_interview = "שלום,<br>ברצונו להודות לך על כך שהגשת בקשת הצטרפות לקלאן. ממעבר על הטופס שלך מצאנו כי<br>אתה מתאים להצטרפות, ואנו מזמינים אותך להמשיך בתהליך.<br>השלב הבא הוא להגיע לשרת ה TeamSpeak של הקלאן בכדי לעבור ראיון אישי.<br><br>הראיון האישי מתקיים בימי שבת בשעה 19:00 בשרת ה TS של הקלאן.<br>כתובת השרת היא: ".TS_ADDRESS.":".TS_PORT."<br><br>שימו לב שהראיון האישי הוא שלב הכרחי בהצטרפות לקלאן.<br>לא ניתן להצטרף מבלי לעבור אותו.<br><br>בברכה,<br>צוות IsrTG‬‎.";
	$email_body_player_approved = "שלום,<br>הנהלת הקלאן שמחה לבשר לך כי עברת את שלב הראיון האישי.<br>מרגע זה אתה בסטטוס המתנה לאימון בסיסי.<br>כמו כן, הנך חבר קלאן רשמי, ולכן אתה יכול להתחבר לאתר.<br>אם ברצונך להגיע לאימון בסיסי אשר מתקיים בימי שלישי בשעה 19:00, עליך להתפקד!<br>לאחר התחברות למערכת, ההתפקדות מתבצעת דרך לוח השנה באתר, על בסיס שבועי.<br>שים לב שללא התפקדות לא תורשה להיכנס לאימון<br><br>ברוך הבא ל IsrTG !<br><br>בברכה,<br>צוות IsrTG‬‎.";
	$email_body_player_decline = "שלום,<br>ברצונו להודות לך על כך שהגעת לראיון אישי<br>לאחר הראיון, הנהלת הקלאן החליטה כי אינך מתאים להשתלב במסגרת הקלאן.<br><br>אנחנו מודעים לכך שמערכת הקלאן אינה מתאימה לכל אחד, ולכן לא כל אחד מתקבל לקלאן.<br>אנחנו מודים לך על הזמן שהקדשת במהלך תהליך ההצטרפות.<br><br>יש לציין כי אי קבלה אינה פוסלת קבלה בעתיד.<br><br>בברכה,<br>צוות IsrTG‬‎.";
	$email_body_player_decline_before_interview = "שלום,<br>ברצונו להודות לך על כך שהגשת טופס הצטרפות לקלאן.<br><br>לאחר מעבר על טופס הצטרפות שלך מצאנו כי אתה לא מתאים להצטרף לקלאן.<br>הנהלת הקלאן עוברת על כל הטפסים ומאשרת \ פוסלת אותם בהתאם.<br><br>אנחנו מודעים לכך שמערכת הקלאן אינה מתאימה לכל אחד, ולכן לא כל אחד מתקבל לקלאן.<br>אנחנו מודים לך על הזמן שהקדשת במהלך תהליך ההצטרפות.<br><br>יש לציין כי אי קבלה אינה פוסלת קבלה בעתיד.<br><br>בברכה,<br>צוות IsrTG‬‎.";
	
	$eventTypes = array('משימת שישי','משימת חו``ל','אימון','משימת פבליק','שיחת קלאן','הענקת דרגה');
	$eventTypesIcons = array(
		'default' => 'sword.png',
		'אימון' => 'training.png',
		'שיחת קלאן' => 'talk.png',
		'משימת פבליק' => 'training.png',
		'הענקת דרגה' => ''
	);
	
	// Dutys Event System
	class structDuty {
		public $id;
		public $playerID;
		public $available = False;
		
		public function __construct($id) {
			$this->id = $id;
		}
	}
	
	class structGroup {
		public $Name = NULL;
		public $dutysArr = NULL;
		public $lowerGroupsArr = array();
		public function __construct($name,$dutys) {
			$this->Name = $name;
			$this->dutysArr = $dutys;
		}
		
		public function addLowerGroup($obj) {
			array_push($this->lowerGroupsArr,$obj);
		}
	}
	
	$infantryStruct = array();
	$newGroup = new structGroup("פיקוד משימה",array(new structDuty(1),new structDuty(4),new structDuty(9),new structDuty(14)));
	array_push($infantryStruct,$newGroup);
	
	$newGroup = new structGroup("כיתה א",array(new structDuty(2),new structDuty(4),new structDuty(9),new structDuty(14)));
		$newGroup->addLowerGroup(new structGroup("חוליה א1",array(new structDuty(3),new structDuty(4),new structDuty(8),new structDuty(5),new structDuty(6),new structDuty(7))));
		$newGroup->addLowerGroup(new structGroup("חוליה א2",array(new structDuty(3),new structDuty(4),new structDuty(20),new structDuty(16),new structDuty(17),new structDuty(18))));
	array_push($infantryStruct,$newGroup);
	
	$newGroup = new structGroup("כיתה ב",array(new structDuty(2),new structDuty(4),new structDuty(9),new structDuty(14)));
		$newGroup->addLowerGroup(new structGroup("חוליה ב1",array(new structDuty(3),new structDuty(4),new structDuty(8),new structDuty(5),new structDuty(6),new structDuty(7))));
		$newGroup->addLowerGroup(new structGroup("חוליה ב2",array(new structDuty(3),new structDuty(4),new structDuty(20),new structDuty(16),new structDuty(17),new structDuty(18))));
	array_push($infantryStruct,$newGroup);
	
	$newGroup = new structGroup("כוחות נוספים",array());
		$newGroup->addLowerGroup(new structGroup("שחקים 1",array(new structDuty(10),new structDuty(10))));
		$newGroup->addLowerGroup(new structGroup("שחקים 2",array(new structDuty(10),new structDuty(10))));
		$newGroup->addLowerGroup(new structGroup("נגמ\"ש",array(new structDuty(25),new structDuty(21),new structDuty(21))));
		$newGroup->addLowerGroup(new structGroup("צוות סיור",array(new structDuty(26),new structDuty(12),new structDuty(13),new structDuty(27))));
		$newGroup->addLowerGroup(new structGroup("עתודה",array(new structDuty(8),new structDuty(8),new structDuty(8),new structDuty(8),new structDuty(8))));
	array_push($infantryStruct,$newGroup);
?>