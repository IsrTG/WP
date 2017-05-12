<?php
function on_every_hour()
{
    error_reporting(E_ALL);
    $FUNC_ONLY = true;
    require_once 'functions.php';

    $backUpDirectory = SERVER_PATH. '/DB_Backups/';
    $db = db_Connect();
    $dateObj = date_now();
    $dateTomorrow = date_now();
    $dateTomorrow->modify("+1 days");
    print "System Time: " . $dateObj->format('d/m/Y H:i:s') . "<BR><BR>";

//Promote players over 1 year in clan
    $db->query("UPDATE mngr_Players SET RankID=4 WHERE RankID=3 AND DateDiff(now(),JoinDate)<" . DAYS_TO_VETERAN);
    $db->query("UPDATE mngr_Players SET RankID=3 WHERE RankID=4 AND DateDiff(now(),JoinDate)>=" . DAYS_TO_VETERAN);

//Update squad URL
    $content = "<?xml version=\"1.0\"?>\n";
    $content .= "<!DOCTYPE squad SYSTEM \"squad.dtd\">\n";
    $content .= "<?xml-stylesheet href=\"squad.xsl?\" type=\"text/xsl\"?>\n";
    $content .= "<squad nick=\"IsrTG\">\n";
    $content .= "	<name>Israeli Tactical Gaming</name>\n";
    $content .= "	<email>" . EmailAddress . "</email>\n";
    $content .= "	<web>www.isrtg.com</web>\n";
    $content .= "	<picture>logo1.paa</picture>\n";
    $content .= "	<title>The Lion Knights (Ver. " . $dateObj->format('d/m') . ")</title>\n";

    if ($result = $db->query("SELECT * FROM mngr_Players WHERE ArmaID!=0 AND Status<2 AND RankID<5 ORDER BY RankID,Nickname")) {
        while ($row = $result->fetch_assoc()) {
            switch ($row["RankID"]) {
                case 1:
                    $trank = "General";
                    break;
                case 2:
                    $trank = "Colonel";
                    break;
                case 3:
                    $trank = "Lieutenant Colonel";
                    break;
                case 4:
                    $trank = "Major";
                    break;
                case 5:
                    $trank = "Captain";
                    break;
                case 6:
                    $trank = "First Lieutenant";
                    break;
                case 7:
                    $trank = "Second Lieutenant";
                    break;
                case 8:
                    $trank = "Chief Sergeant";
                    break;
                case 9:
                    $trank = "Sergeant Major";
                    break;
                case 10:
                    $trank = "First Sergeant";
                    break;
                case 11:
                    $trank = "Master Sergeant";
                    break;
                case 12:
                    $trank = "Sergeant First Class";
                    break;
                case 13:
                    $trank = "Staff Sergeant";
                    break;
                case 14:
                    $trank = "Sergeant";
                    break;
                case 15:
                    $trank = "Corporal";
                    break;
                case 16:
                    $trank = "Private First Class";
                    break;
                case 17:
                    $trank = "Private";
                    break;
                default:
                    $trank = $row["RankID"];
                    break;
            }
            $tmp = explode(" ", $row['Name']);
            $tname = $tmp[0];
            $content .= "	<member id=\"" . $row["ArmaID"] . "\" nick=\"" . $row["Nickname"] . "\">\n";
            $content .= "		<name>" . $row["NameENG"] . "</name>\n";
            $content .= "		<email>N/A</email>\n";
            $content .= "		<icq>N/A</icq>\n";
            $content .= "		<remark>" . $trank . "</remark>\n";
            $content .= "	</member>\n";
        }
        $result->free();
    }
    $content .= "</squad>";
    file_put_contents(SERVER_PATH . "/squadxml/squad.xml", $content);
    print "<li> Squad XML updated.<BR>";

//======  Prevent running more than once per hour - If so: die  ========
    if ($result = $db->query("SELECT LogDate FROM mngr_Logs WHERE LogID=1")) {
        $row = $result->fetch_assoc();
        $logHour = dateObj($row["LogDate"])->format("%H");
        $nowHour = $dateObj->format("%H");
        if ($logHour == $nowHour) {
            print "Last run: " . $row["LogDate"] . " - Die.";
            die();
        }
        $result->free();
    }

//Send reminder email at 8:00 for hitpakdut 'maybe' in events tomorrow
    if ($dateObj->format('H') == 8) {
        if ($result = $db->query("SELECT * FROM mngr_Events WHERE Status=0 AND DateDiff('" . $dateTomorrow->format('Y-m-d') . "',EventDate)=0")) {
            while ($row = $result->fetch_assoc()) {
                $wanted = MultiToArray(EventWantedPlayers($row['EventID']));
                $maybe = MultiToArray($row['PlayersMaybe']);
                $count_sent = 0;
                for ($i = 0; $i < count($wanted); $i++) {
                    if (SearchInMulti($row['PlayersAccept'], $wanted[$i]) == False && SearchInMulti($row['PlayersDecline'], $wanted[$i]) == False && SearchInMulti($row['PlayersMaybe'], $wanted[$i]) == False) //If player not hitpaked - remind him to
                    {
                        $email = GetValue("Email", "mngr_Players", "JoomlaID=" . $wanted[$i]);
                        $body = "שלום,<br>טרם התפקדת לאירוע - " . $row["Name"] . " (" . $row["Type"] . ") שמתקיים ב " . displayFullDate($row["EventDate"]) . ".<br><b>המערכת ממתינה להתפקדותך הסופית (מגיע/לא מגיע). אנא בצע זאת בהקדם.</b><br>כדי להגיע לעמוד האירוע באפשרותך ללחוץ <a href='http://www.isrtg.com/index.php/calendar-day?eid=" . $row["EventID"] . "'>כאן</a>.<br>שים לב: ניתן לעדכן נוכחות עד יום לפני האירוע בשעה 20:00<br><br>בברכה,<br>צוות IsrTG‬‎.";
                        sendmail($email, "תזכורת להתפקדות", $body);
                        $count_sent++;
                    }
                    if (SearchInMulti($row['PlayersMaybe'], $wanted[$i])) //If player is maybe - remind to change
                    {
                        $email = GetValue("Email", "mngr_Players", "JoomlaID=" . $wanted[$i]);
                        $body = "שלום,<br>התפקדת לאירוע - " . $row["Name"] . " (" . $row["Type"] . ") שמתקיים ב " . displayFullDate($row["EventDate"]) . " כ\"אולי\".<br><b>המערכת ממתינה להתפקדותך הסופית (מגיע/לא מגיע). אנא בצע זאת בהקדם.</b><br>כדי להגיע לעמוד האירוע באפשרותך ללחוץ <a href='http://www.isrtg.com/index.php/calendar-day?eid=" . $row["EventID"] . "'>כאן</a>.<br>שים לב: ניתן לעדכן נוכחות עד יום לפני האירוע בשעה 20:00<br><br>בברכה,<br>צוות IsrTG‬‎.";
                        sendmail($email, "תזכורת להתפקדות סופית", $body);
                        $count_sent++;
                    }
                }
                if ($count_sent > 0)
                    addlog(0, "מתוזמן: נשלחו " . $count_sent . " אימיילים לתזכורת להתפקדות סופית");
            }
            $result->free();
        }

        //Run database backup
        print backup_database($backUpDirectory, $ttlLines, $filePath);
        print $filePath;
        sendmail(WebmasterEmailAddress, "IsrTG - גיבוי מסד נתונים יומי", "<center><b>מצורף.</b></center>", $filePath);
        addlog(0, "גיבוי מסד הנתונים בוצע בהצלחה (" . $ttlLines . " שורות)");
    }

//update log row
    $dateObj = date_now();
    $db->query("UPDATE mngr_Logs SET LogDate='" . $dateObj->format('Y-m-d H:i:s') . "' WHERE LogID=1");
    $db->close();

    print "Done.";

//MYSQL EXPORT TO GZIP 
    function backup_database($directory, &$ttlLines, &$filePath)
    {

        // check mysqli extension installed
        if (!function_exists('mysqli_connect')) {
            die(' This scripts need mysql extension to be running properly ! please resolve!!');
        }

        $mysqli = db_Connect();

        if ($mysqli->connect_error) {
            print_r($mysqli->connect_error);
            return false;
        }

        $dir = $directory;
        $result = '<p> Could not create backup directory on :' . $dir . ' Please Please make sure you have set Directory on 755 or 777 for a while.</p>';
        $res = true;
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 755)) {
                $res = false;
            }
        }

        $n = 1;
        if ($res) {
            $date = date('d.m.y H_i_s', time());
            $name = $date;
            # counts
            if (file_exists($dir . '/' . $name . '.sql.gz')) {

                for ($i = 1; @count(file($dir . '/' . $name . '_' . $i . '.sql.gz')); $i++) {
                    $name = $name;
                    if (!file_exists($dir . '/' . $name . '_' . $i . '.sql.gz')) {
                        $name = $name . '_' . $i;
                        break;
                    }
                }
            }

            $fullname = $dir . '/' . $name . '.sql.gz'; # full structures
            $filePath = $fullname;
            if (!$mysqli->error) {
                $sql = "SHOW TABLES";
                $show = $mysqli->query($sql);
                while ($r = $show->fetch_array()) {
                    $tables[] = $r[0];
                }

                if (!empty($tables)) {

                    //cycle through
                    $return = '';
                    $ttlLines = 0;
                    foreach ($tables as $table) {
                        $result = $mysqli->query('SELECT * FROM ' . $table);
                        $num_fields = $result->field_count;
                        $ttlLines += $result->num_rows;
                        $row2 = $mysqli->query('SHOW CREATE TABLE ' . $table);

                        $row2 = $row2->fetch_row();
                        $return .=
                            "\n
-- ---------------------------------------------------------
--
-- Table structure for table : `{$table}`
--
-- ---------------------------------------------------------

" . $row2[1] . ";\n";

                        for ($i = 0; $i < $num_fields; $i++) {

                            $n = 1;
                            while ($row = $result->fetch_row()) {


                                if ($n++ == 1) { # set the first statements
                                    $return .=
                                        "
--
-- Dumping data for table `{$table}`
--

";
                                    /**
                                     * Get structural of fields each tables
                                     */
                                    $array_field = array(); #reset ! important to resetting when loop
                                    while ($field = $result->fetch_field()) # get field
                                    {
                                        $array_field[] = '`' . $field->name . '`';

                                    }
                                    $array_f[$table] = $array_field;
                                    // $array_f = $array_f;
                                    # endwhile
                                    $array_field = implode(', ', $array_f[$table]); #implode arrays

                                    $return .= "INSERT INTO `{$table}` ({$array_field}) VALUES\n(";
                                } else {
                                    $return .= '(';
                                }
                                for ($j = 0; $j < $num_fields; $j++) {

                                    $row[$j] = str_replace('\'', '\'\'', preg_replace("/\n/", "\\n", $row[$j]));
                                    if (isset($row[$j])) {
                                        $return .= is_numeric($row[$j]) ? $row[$j] : '\'' . $row[$j] . '\'';
                                    } else {
                                        $return .= '\'\'';
                                    }
                                    if ($j < ($num_fields - 1)) {
                                        $return .= ', ';
                                    }
                                }
                                $return .= "),\n";
                            }
                            # check matching
                            @preg_match("/\),\n/", $return, $match, false, -3); # check match
                            if (isset($match[0])) {
                                $return = substr_replace($return, ";\n", -2);
                            }

                        }

                        $return .= "\n";

                    }

                    $return =
                        "-- ---------------------------------------------------------
--
-- SIMPLE SQL Dump
-- 
-- http://www.nawa.me/
--
-- Host Connection Info: " . $mysqli->host_info . "
-- Generation Time: " . date('F d, Y \a\t H:i A ( e )') . "
-- PHP Version: " . PHP_VERSION . "
--
-- ---------------------------------------------------------\n\n

SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
SET time_zone = \"+00:00\";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
" . $return . "
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";

# end values result

                    @ini_set('zlib.output_compression', 'Off');
                    $gzipoutput = gzencode($return, 9);

                    if (@ file_put_contents($fullname, $gzipoutput)) { # 9 as compression levels

                        $result = $name . '.sql.gz'; # show the name

                    } else { # if could not put file , automaticly you will get the file as downloadable

                        $result = false;
                        // various headers, those with # are mandatory
                        header('Content-Type: application/x-download');
                        header("Content-Description: File Transfer");
                        header('Content-Encoding: gzip'); #
                        header('Content-Length: ' . strlen($gzipoutput)); #
                        header('Content-Disposition: attachment; filename="' . $name . '.sql.gz' . '"');
                        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
                        header('Connection: Keep-Alive');
                        header("Content-Transfer-Encoding: binary");
                        header('Expires: 0');
                        header('Pragma: no-cache');

                        echo $gzipoutput;

                    }

                } else {

                    $result = '<p>Error when executing database query to export.</p>' . $mysqli->error;

                }
            }

        } else {
            $result = '<p>Wrong mysqli input</p>';
        }

        if ($mysqli && !$mysqli->error) {
            @$mysqli->close();
        }

        try {
            //save last 'BackupsToSave' in the dir - delete the rest (FIFO)
            $BackUpsInDirArray = scandir($directory, SCANDIR_SORT_DESCENDING);
            for ($i = BACKUPS_TO_SAVE; $i < count($BackUpsInDirArray); $i++) {
                if (!is_dir($directory . $BackUpsInDirArray[$i]))
                    unlink($directory . $BackUpsInDirArray[$i]); //delete the file - if not dir
            }
        } catch (\Exception $e) {
            echo 'mysqldump-php error: ' . $e->getMessage();
        }

        return $result;
    }
}
add_action('isrtg_hourly_event', 'on_every_hour');
?>