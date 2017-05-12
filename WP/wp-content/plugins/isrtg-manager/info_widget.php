<?php
// Creating the widget 
class isrtg_info_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
		// Base ID of your widget
		'isrtg_info_widget',

		// Widget name will appear in UI
		__('IsrTG Info Widget', 'wpb_widget_domain'),

		// Widget description
		array( 'description' => __( 'IsrTG\'s info widget', 'wpb_widget_domain' ), )
		);
	}

	private function render_widget() {
		ob_start();
		?>

		<div id="Info_Widget" style="text-align:center;">
			<br><div><a href='pws://www.isrtg.com/launcher/server.yml?action=update'><img src=/mngr/images/pws.png width=32 height=30 title='' style="display: inline;" onMouseOver="Tip('עדכן רשימת מודים',WIDTH,150,CENTERMOUSE,false,BORDERCOLOR,'#324253');" onMouseOut="UnTip();"></a> <a href='/launcher/IsrTG Profile.zip'><img src=/mngr/images/pws_shortcut.png width=32 height=30 style="display: inline;" onMouseOver="Tip('הורד קיצור דרך לשולחן העבודה לעדכון רשימת המודים בקלות',WIDTH,150,CENTERMOUSE,false,BORDERCOLOR,'#324253');" onMouseOut="UnTip();"></a> <a href='ts3server://<?=TS_ADDRESS?>?port=<?=TS_PORT?>'><img src=/mngr/images/ts.png style="display: inline;" onMouseOver="Tip('התחבר לטימספיק',WIDTH,150,CENTERMOUSE,false,BORDERCOLOR,'#324253');" onMouseOut="UnTip();"></a></div>
			<div style="cursor: default;"><b>הרשמה לקלאן כעת 
		<?php
		if (getSetting("RegOpen")=="1")
			print "<span data-role='IsrTGReg_Open'>פתוחה</span>";
		else
			print "<span data-role='IsrTGReg_Close'>סגורה</span>";
		?>
			</b>
			</div>
		</div>
		
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
function info_widget_load() {
	register_widget( 'isrtg_info_widget' );
}
add_action( 'widgets_init', 'info_widget_load' );
?>