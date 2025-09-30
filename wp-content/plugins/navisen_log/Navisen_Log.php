<?php
/*
Plugin Name: Navisen Log
Description: Adds some logging features - still testing...
Version: 0.3
 */

define('RUC_LOG_1H', 3600);
define('RUC_LOG_24H', 86400);
define('RUC_LOG_7D', 604800);


class Navisen_Log{
	static $db;
	static $tablename;
	static $stmt_add;
	static $stmt_get;
	static $stmt_getTop;

/*
	function __construct(){
		global $table_prefix;
		$blogid = get_current_blog_id();
		$blogid = $blogid === 1 ? '' : '_'.$blogid;
		$this->tablename = $table_prefix.'ruclog_test'.$blogid;
		try{
			$this->stmt_add = Navisen_Log::$db->prepare("INSERT INTO $this->tablename (time,postid) VALUES (:time,:postid);");
//			$this->stmt_get = Navisen_Log::$db->prepare("SELECT * FROM $this->tablename LIMIT 10");
			$this->stmt_get = Navisen_Log::$db->prepare("SELECT postid,count(postid) AS cnt FROM $this->tablename WHERE postid = :postid");
			$this->stmt_getTop = Navisen_Log::$db->prepare("SELECT postid,count(*) AS cnt FROM $this->tablename WHERE time > :stime GROUP BY postid ORDER BY cnt DESC ");
		} catch (PDOException $pdoe){
			error_log('Navisen_Log: '.$pdoe->getMessage());
		}
	}
*/

	static function register(){
		error_log('Navisen_Log: register option');
		register_setting( 'navisen_log_group', 'navisen_log', array('Navisen_Log', 'setup') );
	}

	static function setup(){
		try{
			$tablename = 'navisen_log_single';
			error_log('Navisen_Log: [setup] '.$tablename);
//			error_log(print_r(Navisen_Log::$db->query("SELECT 1 FROM $tablename LIMIT 1"),true)); return;
			$tc = Navisen_Log::$db->exec("CREATE TABLE IF NOT EXISTS $tablename (
				id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
				time INTEGER,
				postid INTEGER)");
//			error_log('Navisen_Log [setup]: table created '.$tablename.' '.print_r($tc,true));
			update_option('navisen_log_tablename', $tablename);
		} catch (PDOException $pdoe){
			error_log('Navisen_Log [setup]: '.$pdoe->getMessage());
		}
	}
	static function init(){
		$tablename = get_option('navisen_log_tablename');
		try{
//			error_log('Navisen_Log: [init] '.$tablename);	
					
			Navisen_Log::$db = new PDO('mysql:dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASSWORD);
			Navisen_Log::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if($tablename === false) {
				Navisen_Log::setup();
				return;
			}
			Navisen_Log::$tablename = $tablename;
			add_action('template_redirect', array('Navisen_Log','log'), 75);
		} catch (Exception $e){
			error_log('Navisen_Log [init]: '.$e->getMessage());
		}
		
	}

	static function log(){
		global $post; 
		if(is_user_logged_in()) return; 
		if(is_singular() && $post->post_type == 'post') {
			try{
				$tablename = Navisen_Log::$tablename;
				if(empty(Navisen_Log::$$stmt_add)) Navisen_Log::$stmt_add = Navisen_Log::$db->prepare("INSERT INTO $tablename (time,postid) VALUES (:time,:postid);");
				$t = time();
				Navisen_Log::$stmt_add->execute(array(':time'=>$t,':postid'=>$post->ID));
				$ip = $_SERVER['REMOTE_ADDR'];
				$rh = $_SERVER['REMOTE_HOST'];
				if($post->ID == 25992) {
					$ip = $_SERVER['REMOTE_ADDR'];
					$rh = gethostbyaddr($ip);
					error_log("|$t|$ip|$rh\n",3,'/var/log/php/post_test.log');
				}
			} catch (Exception $e){
				error_log('Navisen_Log [log]: '.$e->getMessage());
			}
//		} else {
//			error_log('Navisen_Log log()');
		}
	}


	static function getTop($period = 604800, $limit = 10){
//		$period = 3600; // 1 hour RUC_LOG_1H
//		$period = 86400; // 24 hours RUC_LOG_24H
//		$period = 604800; // 7 days RUC_LOG_7D
		$top = array();
//		error_log('Navisen_Log: [getTop]');
		try{
			if(empty(Navisen_Log::$stmt_getTop)) {
				$tablename = Navisen_Log::$tablename;
				Navisen_Log::$stmt_getTop = Navisen_Log::$db->prepare("SELECT postid,count(*) AS cnt FROM $tablename WHERE time > :stime GROUP BY postid ORDER BY cnt DESC LIMIT :limit");
			}
			Navisen_Log::$stmt_getTop->bindValue(':stime', time() - $period, PDO::PARAM_INT);
			Navisen_Log::$stmt_getTop->bindValue(':limit', $limit, PDO::PARAM_INT);
			Navisen_Log::$stmt_getTop->execute();
			$dbg = '';
			while($result = Navisen_Log::$stmt_getTop->fetch(PDO::FETCH_ASSOC)){
				$top[$result['postid']] = $result['cnt'];
				$dbg .= $result['postid'] .': '. $result['cnt'].' | ';
			}
//			error_log('Navisen_Log: [getTop] '.$dbg);
		} catch (PDOException $pdoe){
			error_log(Navisen_Log::$tablename.': '.$pdoe->getMessage());
		}
		return $top;
	}
 
	function getCount($postId, $period = false){
		$period = $period ? $period : time();
//		if(!$period) $period = time();
		try{
			if(empty(Navisen_Log::$stmt_get)) {
				$tablename = Navisen_Log::$tablename;
				Navisen_Log::$stmt_get = Navisen_Log::$db->prepare("SELECT postid,count(postid) AS cnt FROM $tablename WHERE postid = :postid AND  time > :stime");
			}
			Navisen_Log::$stmt_get->bindValue(':postid',$postId, PDO::PARAM_INT);
			Navisen_Log::$stmt_get->bindValue(':stime',time() - $period, PDO::PARAM_INT);
			Navisen_Log::$stmt_get->execute();
			if($result = Navisen_Log::$stmt_get->fetch(PDO::FETCH_ASSOC)){
//				error_log('Navisen_Log: [getCount] '.$result['postid'].': '.$result['cnt']);
				return $result['cnt'];
			} else {
				return -1;
			}
				
		} catch (PDOException $pdoe){
			error_log(Navisen_Log::$tablename.': '.$pdoe->getMessage());
		}
	}
	
}
//error_log('Navisen_Log: '.__FILE__);
add_action('init', array('Navisen_Log', 'init'));
/*
if(is_admin()) {
	add_action('admin_init', array('Navisen_Log', 'register'));
}
*/
//register_activation_hook( __FILE__, array('Navisen_Log', 'activate') );
//if ( ! empty ( $GLOBALS['pagenow'] ) && 'plugins.php' === $GLOBALS['pagenow'] )	add_action( 'admin_notices', array('Navisen_Log', 'activate'), 0 );


class Log_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		// widget actual processes
		parent::__construct(
				'log_widget', // Base ID
				'Log Widget', // Name
				array( 'description' => 'Log Widget', ) // Args
		);
				
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		$limit = isset($instance[ 'limit' ]) ? $instance[ 'limit' ] : 5;
		$limit = intval($limit);
		$top24 = Navisen_Log::getTop(RUC_LOG_24H, $limit + 5); 
		$okcnt = 0;
		echo '<ul>';
		foreach ($top24 as $pid => $cnt){
			$post = get_post($pid);
//			error_log(print_r($post,true));
			if($post->post_status != 'publish') continue;
			if($okcnt++ > $limit) break;
			echo "<li><a href='?p=$pid' title='$cnt'>$post->post_title</a></li>";
		}
		echo '</ul>';
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = 'Most...';
		}
		$limit = isset($instance[ 'limit' ]) ? $instance[ 'limit' ] : 5;
?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Max posts:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : 5;
		return $instance;
	}
}

add_action( 'widgets_init', function(){
	register_widget( 'Log_Widget' );
});
