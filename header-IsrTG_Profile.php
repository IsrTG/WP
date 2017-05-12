<?php
/**
 * The header for our theme.
 *
 * @package ThemeGrill
 * @subpackage Ample
 * @since Ample 0.1
 */

/* get Isrtg User Profile Nickname from pid */
$db = db_Connect();
$pid = getFieldg("pid");
if ($pid=="") $pid = getField("pid");
$pid = mysqli_real_escape_string($db,$pid);

$eid = getFieldg("eid");
if ($eid=="") $eid = getField("eid");
$eid = mysqli_real_escape_string($db,$eid);

unset($custom_title,$row,$result);
if ($pid != '')
	{
	//profile page title
	if ($result = $db->query("SELECT mngr_Players.*,mngr_Ranks.Name AS 'RankName' FROM mngr_Players INNER JOIN mngr_Ranks ON mngr_Players.RankID = mngr_Ranks.RankID WHERE JoomlaID=".$pid))
		{
		if ($result->num_rows>0)
			{
			$row = $result->fetch_assoc();
			$custom_title=htmlspecialchars("פרופיל אישי: ".$row['Nickname']);
			}
		}
	}
else if($eid != '')
	{
	//event title
	if ($result = $db->query("SELECT * FROM mngr_Events WHERE EventID=".$eid))
		{
		if ($result->num_rows>0)
			{
			$row = $result->fetch_assoc();
			$custom_title='אירוע: '.$row['Name'];
			}
		}
	}
else if ($_SERVER['QUERY_STRING'] == '')
	{
		unset($custom_title);
	}
else if (getFieldg("eid",$db) =="" && getField("eid",$db) =="") 
	{
	//multi events day - titles
		$currDay = (int) getFieldg("tday",$db);
		$currMonth = (int) getFieldg("tmonth",$db);
		$currYear = (int) getFieldg("tyear",$db);
		
		$custom_title='אירועים בתאריך '.$currDay.'/'.$currMonth.'/'.$currYear;
	}
unset($row,$result);
/* get Isrtg User Profile Nickname from pid */


?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<!-- IsrTG -->
<link rel="stylesheet" type="text/css" href="/mngr/css/IsrTG_General.css">
<!-- End IsrTG -->

<!-- jQuery -->  
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script> -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!-- BootStrap --> 
	<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.2/css/jquery.dataTables.min.css"></style>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
	<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<!-- End BootStrap --> 

<script type="text/javascript">
$.fn.collapsible = function() {
    $(this).addClass("ui-accordion ui-widget ui-helper-reset");
    var headers = $(this).children("h3");
    headers.addClass("accordion-header ui-accordion-header ui-helper-reset ui-state-active ui-accordion-icons ui-corner-all");
    headers.append('<span class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-s">');
    headers.click(function() {
        var header = $(this);
        var panel = $(this).next();
        var isOpen = panel.is(":visible");
        if(isOpen)  {
            panel.slideUp("fast", function() {
                panel.hide();
                header.removeClass("ui-state-active")
                    .addClass("ui-state-default")
                    .children("span").removeClass("ui-icon-triangle-1-s")
                        .addClass("ui-icon-triangle-1-e");
          });
        }
        else {
            panel.slideDown("fast", function() {
                panel.show();
                header.removeClass("ui-state-default")
                    .addClass("ui-state-active")
                    .children("span").removeClass("ui-icon-triangle-1-e")
                        .addClass("ui-icon-triangle-1-s");
          });
        }
    });
}; 

jQuery(document).ready(function ($)
	{
	$("#accordion").accordion({
								collapsible: false,
								heightStyle: "content",
								header: " > h3"
							});
	
	}
);
</script>

<?php
/**
 * This hook is important for wordpress plugins and other many things
 */
wp_head();
?>

</head>

<body <?php body_class(); ?>>
	<script src="/mngr/js/wz_tooltip.js" type="text/javascript"></script>
	
   <div id="page" class="hfeed site">
   <?php
      if ( ample_option( 'ample_show_header_logo_text', 'text_only' ) == 'none' ) {
         $header_extra_class = 'logo-disable';
      } else {
         $header_extra_class = '';
      }
   ?>
   <header id="masthead" class="site-header <?php echo $header_extra_class; ?>" role="banner">
      <div class="header">
         <?php if( ample_option( 'ample_header_image_position', 'above' ) == 'above' ) { ample_render_header_image(); } ?>

         <div class="main-head-wrap inner-wrap clearfix">
            <div id="header-left-section">
               <?php if( ( ample_option( 'ample_show_header_logo_text', 'text_only' ) == 'both' || ample_option( 'ample_show_header_logo_text', 'text_only' ) == 'logo_only' ) ) {?>

				<div id="header-logo-image">
					<?php if (ample_option('ample_header_logo_image', '') != '') { ?>

			                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo esc_url(ample_option( 'ample_header_logo_image', '' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"></a>

	                <?php }

	                if (function_exists('the_custom_logo') && has_custom_logo( $blog_id = 0 )) {
						ample_the_custom_logo();
					}?>
				</div><!-- #header-logo-image -->

                <?php }

               $screen_reader = '';
               if ( ( ample_option( 'ample_show_header_logo_text', 'text_only' ) == 'logo_only' || ample_option( 'ample_show_header_logo_text', 'text_only' ) == 'none' ) ) {
                  $screen_reader = 'screen-reader-text';
               }
               ?>
               <div id="header-text" class="<?php echo $screen_reader; ?>">
               <?php
                  if ( is_front_page() || is_home() ) : ?>
                     <h1 id="site-title">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                     </h1>
                  <?php else : ?>
                     <h3 id="site-title">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                     </h3>
                  <?php endif;
                  $description = get_bloginfo( 'description', 'display' );
                  if ( $description || is_customize_preview() ) : ?>
                     <p id="site-description"><?php echo $description; ?></p>
                  <?php endif;
               ?>
               </div>
            </div><!-- #header-left-section -->

            <div id="header-right-section">
               <nav id="site-navigation" class="main-navigation" role="navigation">
                  <p class="menu-toggle"></p>
                  <?php
                  if ( has_nav_menu( 'primary' ) ) {
                     wp_nav_menu(
                        array(
                           'theme_location' => 'primary',
                           'menu_class'    => 'menu menu-primary-container',
						   'echo'		   => 1
                        )
                     );
                  }
                  else {
                     wp_page_menu();
                  }
                  ?>
               </nav>
               <i class="fa fa-search search-top"></i>
               <div class="search-form-top">
                  <?php get_search_form(); ?>
               </div>
   	      </div>
   	   </div><!-- .main-head-wrap -->
         <?php if( ample_option( 'ample_header_image_position', 'above' ) == 'below' ) { ample_render_header_image(); } ?>
  	   </div><!-- .header -->
	</header><!-- end of header -->
   <div class="main-wrapper">

      <?php if( ample_option('ample_activate_slider' , '0') == '1' ) {
         if( is_front_page() ) {
            ample_featured_image_slider();
         }
      }
      if( '' != ample_header_title() && !( is_front_page() ) )
		{
?>
         <div class="header-post-title-container clearfix">
            <div class="inner-wrap">
               <div class="post-title-wrapper">
               <?php if ( is_home() ) : ?>
                  <h2 class="header-post-title-class entry-title"><?php echo ($custom_title != '') ? $custom_title : ample_header_title(); ?></h2>
               <?php else : ?>
                  <h1 class="header-post-title-class entry-title"><?php echo ($custom_title != '') ? $custom_title : ample_header_title(); ?></h1>
               <?php endif; ?>
               </div>
               <?php if( function_exists( 'ample_breadcrumb' ) ) { ample_breadcrumb(); } ?>
            </div>
         </div>
     <?php 
		}
	?>
