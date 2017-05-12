<?php
// Creating the widget 
class isrtg_calendar_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
		// Base ID of your widget
		'isrtg_calendar_widget',

		// Widget name will appear in UI
		__('IsrTG Calendar Widget', 'wpb_widget_domain'), 

		// Widget description
		array( 'description' => __( 'IsrTG\'s calendar widget', 'wpb_widget_domain' ), ) 
		);
	}

	private function render_widget() {
		ob_start();
		userLastLogin();
		
		$db = db_Connect();
		?>
		<script src="/mngr/js/wz_tooltip.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="/mngr/css/IsrTG_Calendar.css">

		<script language="javascript" type="text/javascript">
		var now = new Date();
		var curMonth = now.getMonth()+1;
		var curYear = now.getFullYear();
		var monthName=new Array();
		monthName[1]="ינואר";
		monthName[2]="פברואר";
		monthName[3]="מרץ";
		monthName[4]="אפריל";
		monthName[5]="מאי";
		monthName[6]="יוני";
		monthName[7]="יולי";
		monthName[8]="אוגוסט";
		monthName[9]="ספטמבר";
		monthName[10]="אוקטובר";
		monthName[11]="נובמבר";
		monthName[12]="דצמבר";
		</script>
		
		<div id='IsrTG_CalendarDiv'>
			<table id='IsrTG_CalendarTable'>
			<tr id='tableTitleSlider'>
				<td class='monthSlider' title="חודש קודם"><a href="javascript:void;" onClick="PrevMonth();">&laquo;</a></td>
				<td colspan=5 class='tableTitle'><div id="mName" style="display:inline;"></div></td>
				<td class='monthSlider' title="חודש הבא"><a href="javascript:void;" onClick="NextMonth();">&raquo;</a></td>
			</tr>
			<tr><td colspan=7>
				<div id="calc_days" style="display:inline;">טוען...</div>
				</td>
			</tr>
			</table>
		</div>
		
		<?php
		$tdate = date('Y-m-d H:i:s', strtotime('+14 days'));
		$tnow = date_now();
		$tnow = $tnow->format('Y-m-d H:i:s');
		$events_not_hitpaked = "";
		$events_maybe = "";
		if ($result = $db->query("SELECT * FROM mngr_Events WHERE Status=0 AND EventDate<'".$tdate."' AND EventDate>'".$tnow."'"))
		{
			//print_r($result); print("</br>");
			while ($row = $result->fetch_assoc())
			{
				$dateObj_now = date_now();
				$tdate = DateTime::createFromFormat("Y-m-d H:i:s", $row['EventDate']);
				$tdate->modify('-1 days');
				$tdate->setTime(20,0,0);
				
				$wanted = MultiToArray(EventWantedPlayers($row['EventID']));
				$myid = fm_getSessionID();
				if (SearchInArray($wanted,$myid)) //Player invited to the event
				{
					if (SearchInMulti($row['PlayersAccept'],$myid)==False && SearchInMulti($row['PlayersDecline'],$myid)==False && SearchInMulti($row['PlayersMaybe'],$myid)==False) //player havnt hitpaked yet
					{
						$events_not_hitpaked .= "<li style='font-size: 11px;'> <a href='index.php/calendar-day?eid=".$row['EventID']."'><span style='font-size: 11px;'>".$row['Name']." (".$row['Type'].", ".displayFullDate($row['EventDate']).")</font></a>";
					}
					elseif ($dateObj_now<$tdate && SearchInMulti($row['PlayersAccept'],$myid)==False && SearchInMulti($row['PlayersDecline'],$myid)==False && SearchInMulti($row['PlayersMaybe'],$myid)==True)
					{
						$events_maybe .= "<li style='font-size: 11px;'> <a href='index.php/calendar-day?eid=".$row['EventID']."'><span style='font-size: 11px;'>".$row['Name']." (".$row['Type'].", ".displayFullDate($row['EventDate']).")</font></a>";
					}
				}
			}
			$result->free();
			
			if ($events_not_hitpaked)
			{
				print("<div>");
					print "<span>אירועים שטרם התפקדת אליהם:</span>";
					print $events_not_hitpaked;
				print("</div>");
			}
			
			if ($events_maybe)
			{
				print("<div>");
					print "<span>אירועים הממתינים להתפקדות סופית:</span>";
					print $events_maybe;
				print("</div>");
			}
		}
		
		?>
		
		<script language="javascript" type="text/javascript">		
		function ajaxFunction(tmonth,tyear){
			var data = {
				'action': 'calendar_ajax_request',
				'tmonth': tmonth,
				'tyear': tyear
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				var ajaxDisplay = document.getElementById('calc_days');
				ajaxDisplay.innerHTML = response;
			});
		}

		function changeMonth(tmonth,tyear)
		{
			ajaxFunction(tmonth,tyear);
			document.getElementById('mName').innerHTML = 'חודש ' + monthName[tmonth] + ", " + tyear;
		}

		function NextMonth()
		{
			if (curMonth==12)
			{
				curMonth = 1;
				curYear = curYear + 1;
			} else {
				curMonth = curMonth + 1;
			}
			changeMonth(curMonth,curYear);
		}

		function PrevMonth()
		{
			if (curMonth==1)
			{
				curMonth = 12;
				curYear = curYear - 1;
			} else {
				curMonth = curMonth - 1;
			}
			changeMonth(curMonth,curYear);
		}

		changeMonth(curMonth,curYear);
		</script>
		<?php 
		$db->close();
		?>
		
		<?php
		return ob_get_clean();
	}
	
	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];

		// This is where you run the code and display the output
		$output = self::render_widget();
		echo $output;
		
		echo $args['after_widget'];
	}
			
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
		$title = $instance[ 'title' ];
		}
		else {
		$title = __( 'New title', 'wpb_widget_domain' );
		}
		// Widget admin form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
		
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}

// Register and load the widget
function calendar_widget_load()
{
	register_widget( 'isrtg_calendar_widget' );
}
add_action( 'widgets_init', 'calendar_widget_load' );

function onCalendarAjaxRequest() {
	ob_start();
	
	$currMonth = (int) $_POST['tmonth'];
	$currYear = (int) $_POST['tyear'];
	$db = db_Connect();
	?>

	<table id='IsrTG_Inner_calendar_table'>
		<tr style="border-bottom:1;">
			<td class='titleDay'>א</td>
			<td class='titleDay'>ב</td>
			<td class='titleDay'>ג</td>
			<td class='titleDay'>ד</td>
			<td class='titleDay'>ה</td>
			<td class='titleDay'>ו</td>
			<td class='titleDay'>ש</td>
		</tr>
		<?php
			$ttlDays = date_format(date_create($currYear."-".$currMonth."-1"),"t");
			$dayStart = date_format(date_create($currYear."-".$currMonth."-1"),"w");
			$prev_month_daystart=date_format(date_create($currYear."-".($currMonth-1)."-1"),"t")-$dayStart+1;
			$date_now = date_now();
			$date_today = $date_now->format("Y-m-d");
			
			$currDay = 1;
			while ($dayStart>0)
			{
				//prev month
				$dateObj = DateTime::createFromFormat("Y-m-d", $currYear."-".($currMonth-1)."-".$prev_month_daystart);
				$tday = $prev_month_daystart;
				$tmonth = $dateObj->format('m');
				$tyear = $dateObj->format('Y');
				if ($result = $db->query("SELECT * FROM mngr_Events WHERE DateDiff('".$tyear."-".$tmonth."-".$tday."',EventDate)=0 ORDER BY EventDate"))
				{
					if ($result->num_rows > 0)
					{
						$events = '';
						while ($row = $result->fetch_assoc())
						{
							$events .= date_format(date_create($row['EventDate']),"H").":".date_format(date_create($row['EventDate']),"i")." ".$row['Name']."</br>";
							if ($result->num_rows > 1)
								$link = "tday=".$tday."&tmonth=".$tmonth."&tyear=".$tyear;
							else
								$link = "eid=".$row['EventID'];
						}
						print "<td class='PrevMonth' onmouseover=\"Tip('".toTip($events)."',WIDTH,150,CENTERMOUSE,false)\" onmouseout=\"UnTip()\"><a href='/index.php/calendar-day?".$link."'><span>".$tday."</span></a></td>";
					} 
					else 
						print "<td class='PrevMonth'>".$tday."</td>";
				}
				
				$dayStart--;
				$currDay++;
				$prev_month_daystart++;
			}
			for ($i=1;$i<=$ttlDays;$i++)
			{
				//current month
				$currDayWeek = date_format(date_create($currYear."-".$currMonth."-".$i),"w");
				$date_event = date_create($currYear."-".$currMonth."-".$i);
				$date_event = $date_event->format("Y-m-d");
				if ($date_today==$date_event) $styleToday = "Today"; //Mark today on calendar
				else if ($date_today>$date_event) $styleToday = "Passed"; //Mark day as Passed on calendar
				else if ($date_today<$date_event) $styleToday = "Future"; //Mark day as Future on calendar
				if ($currDay==8)
				{
					print "</tr><tr>";
					$currDay = 1;
				}
				if ($result = $db->query("SELECT * FROM mngr_Events WHERE DateDiff('".$currYear."-".$currMonth."-".$i."',EventDate)=0 ORDER BY EventDate"))
				{
					if ($result->num_rows > 0)
					{
						$events = '';
						$day_closed = true;
						while ($row = $result->fetch_assoc())
						{
							$events .= date_format(date_create($row['EventDate']),"H").":".date_format(date_create($row['EventDate']),"i")." ".$row['Name']."<br>";
							if ($result->num_rows>1)
							{
								$link = "tday=".$i."&tmonth=".$currMonth."&tyear=".$currYear;
							} else {
								$link = "eid=".$row['EventID'];
							}
							if ($row['Status']==0) $day_closed=false;
						}
						if ($day_closed)
						{
							$color = "#091855";
						} else {
							$color = "#1343FF";
						}
						print "<td class='CurrentMonth ".$styleToday."' onmouseover=\"Tip('".toTip($events)."',WIDTH,150,CENTERMOUSE,false,BORDERCOLOR,'".$color."')\" onmouseout=\"UnTip()\"><a href='/index.php/calendar-day?".$link."'><span style='color:".$color.";'>".$i."</span></a></td>";
					} else {
						print "<td class='CurrentMonth ".$styleToday."'>".$i."</td>";
					}
				}
				$result->free();
				$currDay++;
			}
			
			$currDay = 1;
			while ($currDayWeek<6)
			{
				//next month
				$dateObj = DateTime::createFromFormat("Y-m-d", $currYear."-".($currMonth+1)."-".$currDay);
				$tday = $currDay;
				$styleToday = "";
				$tmonth = $dateObj->format('m');
				$tyear = $dateObj->format('Y');
				$dateObj = $dateObj->format('Y-m-d');
				if ($date_today>$date_event) $styleToday = "Passed"; //Mark day as Passed on calendar
				if ($result = $db->query("SELECT * FROM mngr_Events WHERE DateDiff('".$dateObj."',EventDate)=0 AND Status=0 ORDER BY EventDate"))
				{
					if ($result->num_rows > 0)
					{
						$events = '';
						while ($row = $result->fetch_assoc())
						{
							$events .= date_format(date_create($row['EventDate']),"H").":".date_format(date_create($row['EventDate']),"i")." ".$row['Name']."</br>";
							if ($result->num_rows > 1)
								$link = "tday=".$tday."&tmonth=".$tmonth."&tyear=".$tyear;
							else
								$link = "eid=".$row['EventID'];
						}
						print "<td class='NextMonth ".$styleToday."' onmouseover=\"Tip('".toTip($events)."',WIDTH,150,CENTERMOUSE,false)\" onmouseout=\"UnTip()\"><a href='/index.php/calendar-day?".$link."'><span>".$tday."</span></a></td>";
					} else {
						print "<td class='NextMonth ".$styleToday."'>".$tday."</td>";
					}
				}
				$result->free();
				$currDayWeek++;
				$currDay++;
			}
		?>
	</table>
	
	<?php
	$db->close();

    echo ob_get_clean();
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action("wp_ajax_calendar_ajax_request", "onCalendarAjaxRequest");
add_action("wp_ajax_nopriv_calendar_ajax_request", "onCalendarAjaxRequest");
?>