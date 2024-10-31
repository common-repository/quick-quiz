<?php



/*

  Plugin Name: Quick Quiz

  Plugin URI: http://quick-quiz.php.org/

  Description: Simple quiz creator 

  Author: André Dagenais (ADC_DAO)

  Version: 1.1

  Author URI: http://www.adc-dao.com

 */

if ( ! defined( 'ABSPATH' ) ) exit; 

//Define constants

define( 'QQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'QQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define( 'QQ_VERSION', '1.1' );

// Register function to be called when plugin is activated
register_activation_hook( __FILE__, 'quick_quiz_activation' );

add_action( 'admin_enqueue_scripts', 'qq_admin_scripts' );

function qq_admin_scripts(){

    wp_enqueue_script( 'jquery' );

    //wp_enqueue_script('newQuiz', QQ_PLUGIN_URL . 'asset/js/new_quiz.js');

    //wp_localize_script( 'newQuiz', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php')));

    add_thickbox();

}

// Activation Callback

function quick_quiz_activation() {

	// Get access to global database access class

	global $wpdb;


	// Check to see if WordPress installation is a network

	if ( is_multisite() ) {



		// If it is, cycle through all blogs, switch to them

		// and call function to create plugin table

		if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1) ) {

			$start_blog = $wpdb->blogid;



			$blog_list = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs );

			foreach ( $blog_list as $blog ) {

				switch_to_blog( $blog );



				// Send blog table prefix to table creation function

				quick_quiz_create_table( $wpdb->get_blog_prefix() );

			}

			switch_to_blog( $start_blog );

			return;

		}

	}



	// Create table on main blog in network mode or single blog

	quick_quiz_create_table( $wpdb->get_blog_prefix() );

}



// Register function to be called when new blogs are added

// to a network site

add_action( 'wpmu_new_blog', 'quick_quiz_new_network_site' );

function quick_quiz_new_network_site( $blog_id ) {

	global $wpdb;



	// Check if this plugin is active when new blog is created

	// Include plugin functions if it is

	if ( !function_exists( 'is_plugin_active_for_network' ) )

		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );



	// Select current blog, create new table and switch back to

	// main blog

	if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {

		$start_blog = $wpdb->blogid;

		switch_to_blog( $blog_id );



		// Send blog table prefix to table creation function

		quick_quiz_create_table( $wpdb->get_blog_prefix() );



		switch_to_blog( $start_blog );

	}

}



// Function to create new database table

function quick_quiz_create_table( $prefix ) {

    // Prepare SQL query to create database table

    // using received table prefix

    $installed_version = get_option('custom_table_quick_quiz_db_version');
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


    // Quiz table
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $prefix . "quick_quiz_data'") != $prefix . 'quick_quiz_data'){
        $wpdb->query("CREATE TABLE " . $prefix . "quick_quiz_data (
            qq_quiz_id int(20) NOT NULL AUTO_INCREMENT,
            qq_quiz_owner_id int(20),
            qq_quiz_title VARCHAR(128) NULL,
            qq_quiz_description text,
            PRIMARY KEY  (qq_quiz_id)
            );");
        add_option('custom_table_quick_quiz_db_version', QQ_VERSION);
    } 
    else {
        if ($installed_version != QQ_VERSION) {
            $sql = "CREATE TABLE " . $prefix . "quick_quiz_data (
                qq_quiz_id int(20) NOT NULL AUTO_INCREMENT,
                qq_quiz_owner_id int(20),
                qq_quiz_title VARCHAR(128) NULL,
                qq_quiz_description text,
                PRIMARY KEY  (qq_quiz_id)
                );";
            dbDelta($sql);
            // notice that we are updating option, rather than adding it
            update_option('custom_table_quick_quiz_db_version', QQ_VERSION);
        }
    }
   
    // Quiz Questions table
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $prefix . "quick_quiz_questions'") != $prefix . 'quick_quiz_questions'){

        $wpdb->query("CREATE TABLE " . $prefix . "quick_quiz_questions (
            qq_question_id int(20) NOT NULL AUTO_INCREMENT,
            qq_quiz_owner_id int(20),
            qq_question_text VARCHAR( 256 ) NULL,
            qq_question_keywords VARCHAR( 128 ) NULL,
            PRIMARY KEY  (qq_question_id)
            );");
    }   
    else {
        if ($installed_version != QQ_VERSION) {
            $sql = "CREATE TABLE " . $prefix . "quick_quiz_questions (
                qq_question_id int(20) NOT NULL AUTO_INCREMENT,
                qq_quiz_owner_id int(20),
                qq_question_text VARCHAR( 256 ) NULL,
                qq_question_keywords VARCHAR( 128 ) NULL,
                PRIMARY KEY  (qq_question_id)
                );";
            dbDelta($sql);
        }
    }

    // Quiz Answers table
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $prefix . "quick_quiz_answers'") != $prefix . 'quick_quiz_answers'){
        $wpdb->query("CREATE TABLE " . $prefix . "quick_quiz_answers (
            qq_answer_id int(20) NOT NULL AUTO_INCREMENT,
            qq_quiz_owner_id int(20),
            qq_question_id int(20) NOT NULL,
            qq_answer_text VARCHAR( 256 ) NULL,
            qq_answer_isgood tinyint(1) NOT NULL DEFAULT 0,
            qq_answer_order smallint(5) NOT NULL DEFAULT 0,
            PRIMARY KEY  (qq_answer_id)
            );");
    }   

    // Quiz Question List table
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $prefix . "quick_quiz_question_list'") != $prefix . 'quick_quiz_question_list'){
        $wpdb->query("CREATE TABLE " . $prefix . "quick_quiz_question_list (
            qq_quiz_id int(20) NOT NULL,
            qq_question_id int(20) NOT NULL,
            PRIMARY KEY  (qq_quiz_id,qq_question_id)
            );");
    }   
 
    // Quiz exam data information
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $prefix . "quick_quiz_exam_data'") != $prefix . 'quick_quiz_exam_data'){

            $wpdb->query("CREATE TABLE " . $prefix . "quick_quiz_exam_data (

			qq_exam_data_id int(20) NOT NULL AUTO_INCREMENT,

			qq_exam_data_useremail VARCHAR( 256 ) NULL,

			qq_exam_data_date date DEFAULT NULL,

			qq_exam_data_result DECIMAL(5,2) DEFAULT NULL,

			qq_exam_data_sendmethod int(20) NOT NULL,

			qq_exam_data_emailto VARCHAR( 256 ) NULL,

			PRIMARY KEY (qq_exam_data_id)

            );");

    }   

    // Quiz exam data questions
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $prefix . "quick_quiz_exam_data_questions'") != $prefix . 'quick_quiz_exam_data_questions'){

            $wpdb->query("CREATE TABLE " . $prefix . "quick_quiz_exam_data_questions (

			qq_exam_data_question_id int(20) NOT NULL AUTO_INCREMENT,

                        qq_exam_data_question_text text,

			qq_exam_data_question_no int(20) NOT NULL,

			PRIMARY KEY (qq_exam_data_question_id)

            );");

    }   

    // Quiz exam data answers
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $prefix . "quick_quiz_exam_data_answers'") != $prefix . 'quick_quiz_exam_data_answers'){

            $wpdb->query("CREATE TABLE " . $prefix . "quick_quiz_exam_data_answers (

			qq_exam_data_answer_id int(20) NOT NULL AUTO_INCREMENT,

                        qq_exam_data_answer_text text,

 			qq_exam_data_question_no int(20) NOT NULL,

 			qq_answer_isgood tinyint(1) NOT NULL DEFAULT 0,

			qq_answer_wasselected tinyint(1) NOT NULL DEFAULT 0,

			PRIMARY KEY (qq_exam_data_answer_id)

            );");

    }   

}



// Admin menu

add_action( 'admin_menu', 'quick_quiz_menu_page' );

function quick_quiz_menu_page(){

//add_options_page( 'Quick Quiz Data Management',

//		'Quick Quiz', 'manage_options',

//		'quick_quiz_manage',

//		'quick_quiz_manage' );

  add_menu_page( 'Quick Quiz Management', 'Quick Quiz', 'manage_options', 'quick-quiz-manage', 'quick_quiz_manage',QQ_PLUGIN_URL.'asset/images/quiz-icon3.png');

}



function quick_quiz_manage(){

    wp_enqueue_script('wpce_bootstrap', QQ_PLUGIN_URL . 'asset/js/bootstrap/js/bootstrap.min.js');

    wp_enqueue_style('wpce_bootstrap', QQ_PLUGIN_URL . 'asset/js/bootstrap/css/bootstrap.min.css');

    wp_enqueue_style('qq_display_quiz', QQ_PLUGIN_URL . 'asset/css/display_quiz.css');

    wp_enqueue_script('qq_display_quiz', QQ_PLUGIN_URL . 'asset/js/display_quiz.js');

    $localize_script_data=array(

				'qq_ajax_url'=>admin_url( 'admin-ajax.php' ),
				'qq_ajax_nonce'=>wp_create_nonce('qq_nonce_field'),
				'qq_site_url'=>site_url(),
				'plugin_url'=>QQ_PLUGIN_URL,
				'plugin_dir'=>QQ_PLUGIN_DIR

		);

    wp_localize_script( 'qq_display_quiz', 'display_quiz_data', $localize_script_data );    

    

    global $current_user;

    global $wpdb;

    get_currentuserinfo();

    

    ?>

        <div class="panel panel-primary" style="width: 99%; margin-top: 20px;">

          <div class="panel-heading">

            <h3 class="panel-title">Quick Quiz</h3>

            <span style="float: right; margin-top: -19px;">Welcome, <?php echo $current_user->display_name;?></span>

          </div>

          <div class="panel-body">

            <?php include_once( QQ_PLUGIN_DIR.'includes/admin/display_quiz.php' );?>

          </div>

    </div>  

    <?php    

}



//add_action ('admin_init', 'qq_admin_init');

//

//// Register functions to be called when quizzes are saved

//function qq_admin_init() {

//	add_action('admin_post_save_qq_quiz',

//		'process_qq_quiz' );

//}


add_action( 'wp_head', 'qq_declare_ajaxurl' );


function qq_declare_ajaxurl() { ?>

<script type="text/javascript">

	var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

</script>

<?php }

// General functions
function sanitize_text_textarea($textareastring){
    $return_value='';
    $fake_newline = '--OMGKEEPTHISNEWLINE--';
    $escaped_newlines= str_replace("\n", $fake_newline, $textareastring);
    $sanitized = sanitize_text_field($escaped_newlines);
    $return_value= str_replace($fake_newline, "\n", $sanitized);
    return $return_value;
}
//End General functions

//This function is use to add a new quiz
function addNewQuiz(){

    global $wpdb;
    global $current_user;
    get_currentuserinfo();
    
    check_ajax_referer( 'qq_nonce_field', 'security' );

    $quiz_data = array();
    $quiz_data['qq_quiz_id'] = ( isset( $_POST['qq_quiz_id'] ) ? sanitize_text_field($_POST['qq_quiz_id']) : '' );
    $quiz_data['qq_quiz_owner_id'] = $current_user->ID;
    $quiz_data['qq_quiz_title'] = ( isset( $_POST['qq_quiz_title'] ) ? sanitize_text_field($_POST['qq_quiz_title']) : '' );
    $quiz_data['qq_quiz_description'] = ( isset( $_POST['qq_quiz_description'] ) ? sanitize_text_textarea($_POST['qq_quiz_description']) : '' );
   
    if (isset($_POST['qq_quiz_id']) && $_POST['qq_quiz_id']=='new'){
        if ($wpdb->insert($wpdb->get_blog_prefix() . 'quick_quiz_data', $quiz_data )===FALSE){
            echo "Error";
        } else {
            echo "Quiz '" . esc_attr(stripslashes($quiz_data['qq_quiz_title'])) . "' successfully added, row ID is " . $wpdb->insert_id;
        }
    } elseif (isset($_POST['qq_quiz_id'])){
        if (is_numeric($quiz_data['qq_quiz_id'])){
            if ($wpdb->update($wpdb->get_blog_prefix() . 'quick_quiz_data', $quiz_data, array('qq_quiz_id'=>$quiz_data['qq_quiz_id']) )===FALSE){
                echo "Error";
            } else {
                echo "Quiz '" . esc_attr($quiz_data['qq_quiz_title']) . "' successfully updated, row ID is " . $quiz_data['qq_quiz_id'];
            }        
        } else {
            echo "Error";
        }
    } else {
        echo "ID NOT SET: " . esc_attr($quiz_data['qq_quiz_id']);
    }

    die();

}

add_action('wp_ajax_addNewQuiz', 'addNewQuiz');
add_action('wp_ajax_nopriv_addNewQuiz', 'addNewQuiz');

//This function is use to display quiz fopr edition (including adding questions)
function editQuiz(){

    global $wpdb;
    check_ajax_referer( 'qq_nonce_field', 'security' );

    $id = ( isset( $_POST['qq_quiz_id'] ) ? intval($_POST['qq_quiz_id']) : 0 );

    if ($id > 0) {

        $quiz_data = array();
        $tablename = $wpdb->get_blog_prefix() . "quick_quiz_data";
        $quiz_query = 'select * from ';
        $quiz_query .= $tablename;
        $quiz_query .= " WHERE qq_quiz_id=%d";
        $quiz_query .= ' ORDER by qq_quiz_id ASC';
        $quiz_item = $wpdb->get_row($wpdb->prepare ($quiz_query, $id), ARRAY_A );

         if ( $quiz_item ){

            echo '<button class="btn btn-primary" style="margin-top: 10px;" onclick="backToQuizzes();">« Back To Quizzes</button><br>';
            echo '<h3>[Quiz #' . $id . '] ' . esc_attr(stripslashes($quiz_item['qq_quiz_title'])) . '</h3>';
            echo '<form type="POST" id="editQuizForm" action="">';
	    echo '<input type="hidden" name="action" value="addNewQuiz" />';
	    echo '<input type="hidden" id="qq_quiz_id" name="qq_quiz_id" value="' . esc_attr( $quiz_item['qq_quiz_id']) . ' "/>';
            echo '<span class="label label-info" style="font-size: 13px;">Title</span><br>';
	    echo '<input type="text" id="qq_quiz_title" name="qq_quiz_title" maxlength="80" style="width: 95%; margin-top: 10px;"value="' . esc_attr( stripslashes($quiz_item['qq_quiz_title'])) . '"/><br><br>';
	    echo '<span class="label label-info" style="font-size: 13px;">Description</span><br>';
	    echo '<textarea id="qq_quiz_description" name="qq_quiz_description" style="margin-top: 10px; width: 95%; overflow-y:hidden;"';
            echo 'onkeyup=\'this.rows = (this.value.split("\n").length||1);\'>';
            echo esc_attr( stripslashes($quiz_item['qq_quiz_description'])) . '</textarea><br><br>';
            echo '<br>';
	    echo '<input type="submit" id="Submit" class="btn btn-success" value="Update Quiz">';
            echo '</form>';
            echo '<div id="feedback"></div><br/>';
            echo '<div id="addQuizQuestion" style="display:none;">';
            echo '<h4 id="newQuizQuestionHeader">Question:</h4>';
            echo '<form method="post" action="" id="addQuizQuestionForm">';
            echo '<p><textarea name="quizQuestion" id="qq_quiz_question" rows="2" cols="50"></textarea></p>';
            echo '<input type="hidden" name="action" value="addQuizQuestion">';
            echo '<input type="hidden" name="quizid" id="qq_quiz_id" value="">';
            echo '<input type="hidden" name="quizquestionid" id="qq_quiz_questionid" value="">';
 	    echo '<input type="submit" id="Submit" class="btn btn-success" value="Add Question">';
            echo '</form>';
            echo '<div id="feedbackNewQuestion"></div><br/>';
            echo '</div>';            
            echo '<h4>Questions listing</h4>';
            echo '<button class="button-primary" onclick="addQuizQuestion();">Add Question</button><br/><br/>';
            echo '<div id="quizQuestionsContainer">';

            getQuizQuestions();

            echo '</div>';

        } else {

            echo "Quiz not found";

        }

    }
 
    die();

}

add_action('wp_ajax_editQuiz', 'editQuiz');
add_action('wp_ajax_nopriv_editQuiz', 'editQuiz');

function getQuizzes(){

    global $wpdb;    
    check_ajax_referer( 'qq_nonce_field', 'security' );
  
    echo '<form method="post" action="' . admin_url( 'admin-post.php' ) . '">';
    echo '  <input type="hidden" name="action" value="qq_delete_quiz" />';
    echo '  <!-- Adding security through hidden referrer field -->';
    echo '  <?php wp_nonce_field("qq_quiz_deletion"); ?>';
    echo '  <table class="wp-list-table widefat fixed table-hover" id="quiz_listing">';
    echo '      <thead>';
    echo '          <tr>';
    echo '              <th style="width: 50px;"></th>';
    echo '              <th style="width: 80px;">ID</th>';
    echo '              <th style="width:300px;">Title</th>';
    echo '              <th>Description</th>';
    echo '          </tr>';
    echo '      </thead>';
    echo '      <div class="quiz_listing">';
    echo '      <!-- Display bug list if no parameter sent in URL -->';

    $tablename = $wpdb->get_blog_prefix() . "quick_quiz_data";
    $quiz_query = 'select * from ';
    $quiz_query .= $tablename;
    $quiz_query .= ' ORDER by qq_quiz_id ASC';
    $quiz_items = $wpdb->get_results($quiz_query, ARRAY_A );

    // Display quizzes if query returned results

    if ( $quiz_items ) {

	foreach ( $quiz_items as $quiz_item ) {

            echo '<tr style="background: #FFF">';
            echo '<td id="' . $quiz_item['qq_quiz_id'] . '"><input type="checkbox" name="quizzes[]" value="';
            echo esc_attr( $quiz_item['qq_quiz_id'] ) . '" /></td>';
            echo '<td>' . $quiz_item['qq_quiz_id'] . '</td>';
            echo '<td>' . esc_attr(stripslashes($quiz_item['qq_quiz_title'])) . '</td>';
            echo '<td>' . esc_textarea(stripslashes($quiz_item['qq_quiz_description'])) . '</td></tr>';

        }

    } else {

        echo '<tr style="background: #FFF">';
        echo '<td colspan=3>No Quiz Found</td></tr>';

    }

    echo '      </div>';
    echo '   </table><br/>';
    echo '   <input type="submit" value="Delete Selected" class="button-primary"/>';
    echo '</form>';

    die();

}

add_action('wp_ajax_getQuizzes', 'getQuizzes');
add_action('wp_ajax_nopriv_getQuizzes', 'getQuizzes');
add_action('admin_post_qq_delete_quiz', 'qq_delete_quiz');

function qq_delete_quiz(){

    if(!current_user_can('manage_options'))

        wp_die('Not allowed');
    
    wp_verify_nonce( $_REQUEST['_wpnonce'], 'qq_quiz_deletion' );
    //check_admin_referer('qq_quiz_deletion');

    $quizzes_to_delete=$_POST['quizzes'];

    global $wpdb;

    foreach ($quizzes_to_delete as $quiz_to_delete) {

        $query= 'DELETE FROM ' . $wpdb->get_blog_prefix() . "quick_quiz_data";
        $query .= ' WHERE qq_quiz_id=' . intval($quiz_to_delete);
        $wpdb->query($wpdb->prepare($query));
        
        $tablename = $wpdb->get_blog_prefix() . "quick_quiz_question_list";
	$quiz_query = 'select * from ';
        $quiz_query .= $tablename;
        $quiz_query .= ' WHERE qq_quiz_id=' . intval($quiz_to_delete);
	$quiz_items = $wpdb->get_results($quiz_query, ARRAY_A );

 	foreach ( $quiz_items as $quiz_item ) {
            $query= 'DELETE FROM ' . $wpdb->get_blog_prefix() . "quick_quiz_question_list";
            $query .= ' WHERE qq_question_id=%d';
            $wpdb->query($wpdb->prepare($query, intval($quiz_item['qq_question_id'])));

            $query= 'DELETE FROM ' . $wpdb->get_blog_prefix() . "quick_quiz_questions";
            $query .= ' WHERE qq_question_id=%d';
            $wpdb->query($wpdb->prepare($query, intval($quiz_item['qq_question_id'])));

            $query= 'DELETE FROM ' . $wpdb->get_blog_prefix() . "quick_quiz_answers";
            $query .= ' WHERE qq_question_id=%d';
            $wpdb->query($wpdb->prepare($query, intval($quiz_item['qq_question_id'])));
        }
    }

    wp_redirect(add_query_arg('page','quick-quiz-manage', admin_url('options-general.php')));

    exit;

}

function getQuizQuestions(){

    check_ajax_referer( 'qq_nonce_field', 'security' );

    global $wpdb;    

    $id = ( isset( $_POST['qq_quiz_id'] ) ? intval($_POST['qq_quiz_id']) : 0 );
   
    $output = '';
    $output .= '<form method="post" action="' . admin_url( 'admin-post.php' ) . '">';
    $output .= '  <input type="hidden" name="action" value="qq_delete_quiz_question" />';
    $output .= '  <!-- Adding security through hidden referrer field -->';
    $output .= '  <?php wp_nonce_field( "qq_quiz_question_deletion" ); ?>';
    $output .= '  <table class="wp-list-table widefat fixed table-hover" id="quiz_question_listing">';
    $output .= '      <thead>';
    $output .= '          <tr>';
    $output .= '              <th style="width: 50px;"></th>';
    $output .= '              <th style="width: 80px;">ID</th>';
    $output .= '              <th style="width:300px;">Question</th>';
    $output .= '          </tr>';
    $output .= '      </thead>';
    $output .= '      <div class="quiz_question_listing">';
    $output .= '      <!-- Display bug list if no parameter sent in URL -->';

    $tablename1 = $wpdb->get_blog_prefix() . "quick_quiz_question_list";
    $tablename2 = $wpdb->get_blog_prefix() . "quick_quiz_questions";
    $quiz_query = 'select * from ';
    $quiz_query .= $tablename1;
    $quiz_query .= ' INNER JOIN ';
    $quiz_query .= $tablename2;
    $quiz_query .= ' ON ' . $tablename1 . '.qq_question_id = ' . $tablename2 . '.qq_question_id';
    $quiz_query .= " WHERE qq_quiz_id=" . $id;
    $quiz_query .= ' ORDER by ' . $tablename1 . '.qq_question_id ASC';
    $quiz_questions = $wpdb->get_results($quiz_query, ARRAY_A );

  if ( $quiz_questions ) {

	foreach ( $quiz_questions as $quiz_question ) {

            $output .= '<tr style="background: #FFF">';
            $output .= '<td id="' . $quiz_question['qq_question_id'] . '"><input type="checkbox" name="questions[]" value="';
            $output .= esc_attr( $quiz_question['qq_question_id'] ) . '" /></td>';
            $output .= '<td>' . esc_attr($quiz_question['qq_question_id']) . '</td>';
            $output .= '<td>' . esc_attr(stripslashes($quiz_question['qq_question_text'])) . '</td>';

         }

    } else {

        $output .= '<tr style="background: #FFF">';
        $output .= '<td colspan=2>No Question Found</td></tr>';

    }

    $output .= '      </div>';
    $output .= '   </table><br/>';
    $output .= '</form>';
    $output .= '<button class="button-primary" onclick="ajaxDeleteQuizQuestion();">Delete Selected</button><br/><br/>';

    echo $output;

    die(); 

}

add_action('wp_ajax_getQuizQuestions', 'getQuizQuestions');
add_action('wp_ajax_nopriv_getQuizQuestions', 'getQuizQuestions');


function qq_delete_quiz_questions(){

    if(!current_user_can('manage_options'))

        wp_die('Not allowed');
    check_ajax_referer( 'qq_nonce_field', 'security' );
    
    $quiz_id=intval($_POST['qq_quiz_id']);

    $quiz_questions_to_delete = explode(",", $_POST['qq_questions_ids']);

    global $wpdb;

    foreach ($quiz_questions_to_delete as $question_to_delete) {

        $query= 'DELETE FROM ' . $wpdb->get_blog_prefix() . "quick_quiz_question_list";

        $query .= ' WHERE qq_question_id=' . intval($question_to_delete);

        $wpdb->query($wpdb->prepare($query));

        $query= 'DELETE FROM ' . $wpdb->get_blog_prefix() . "quick_quiz_answers";

        $query .= ' WHERE qq_question_id=' . intval($question_to_delete);

        $wpdb->query($wpdb->prepare($query));

        $query= 'DELETE FROM ' . $wpdb->get_blog_prefix() . "quick_quiz_questions";

        $query .= ' WHERE qq_question_id=' . intval($question_to_delete);

        $wpdb->query($wpdb->prepare($query));

    }

    //echo getQuizQuestions($quiz_id);

    die();

}



add_action('wp_ajax_deleteQuizQuestions', 'qq_delete_quiz_questions');
add_action('wp_ajax_nopriv_deleteQuizQuestions', 'qq_delete_quiz_questions');



function addNewQuizQuestion($quiz_id){

    global $wpdb;
    global $current_user;
    get_currentuserinfo();

    check_ajax_referer( 'qq_nonce_field', 'security' );

    $quiz_data_question = array();
    $quiz_data_question['qq_question_id'] = ( isset( $_POST['qq_quiz_questionid'] ) ? sanitize_text_field($_POST['qq_quiz_questionid']) : '' );
    $quiz_data_question['qq_quiz_owner_id'] = $current_user->ID;
    $quiz_data_question['qq_question_text'] = ( isset( $_POST['qq_quiz_question'] ) ? sanitize_text_field($_POST['qq_quiz_question']) : '' );

    if ($quiz_data_question['qq_question_id']=='new'){

        if ($wpdb->insert($wpdb->get_blog_prefix() . 'quick_quiz_questions', $quiz_data_question )===FALSE){

            echo "Error";

        } else {

            $quiz_data_quizquestion = array();

            $quiz_data_quizquestion['qq_quiz_id']=( isset( $_POST['qq_quiz_id'] ) ? intval($_POST['qq_quiz_id']) : 0 );
            $quiz_data['qq_quiz_owner_id'] = $current_user->ID;
            $quiz_data_quizquestion['qq_question_id'] = $wpdb->insert_id;

            if ($wpdb->insert($wpdb->get_blog_prefix() . 'quick_quiz_question_list', $quiz_data_quizquestion )===FALSE){

                echo "Error";

            }

            echo "Quiz Question'" . esc_attr($quiz_data_question['qq_question_text']) . "' successfully added";

        }

    } elseif (isset($_POST['qq_quiz_questionid'])){
        if (is_numeric($quiz_data_question['qq_question_id'])){
            if ($wpdb->update($wpdb->get_blog_prefix() . 'quick_quiz_questions', $quiz_data_question, array('qq_question_id'=>$quiz_data_question['qq_question_id']) )===FALSE){
                echo "Error";
            } else {
                echo "Quiz '" . esc_attr(stripslashes($quiz_data_question['qq_question_text'])) . "' successfully updated";
            }
        }  else {
            echo "Error";
        }
        
    } else {
        echo "Error: ID NOT SET: " . esc_attr($quiz_data_question['qq_question_id']);
   }

    die();
        

}



add_action('wp_ajax_addNewQuizQuestion', 'addNewQuizQuestion');

add_action('wp_ajax_nopriv_addNewQuizQuestion', 'addNewQuizQuestion');



function editQuizQuestion(){

    check_ajax_referer( 'qq_nonce_field', 'security' );

    global $wpdb;

    $quizid = ( isset( $_POST['qq_quiz_id'] ) ? intval($_POST['qq_quiz_id']) : 0 );

    $questionid = ( isset( $_POST['qq_quiz_questionid'] ) ? intval($_POST['qq_quiz_questionid']) : 0 );

    if ($questionid > 0) {

        $quiz_data = array();
        $tablename = $wpdb->get_blog_prefix() . "quick_quiz_questions";
        $quiz_query = 'select * from ';
        $quiz_query .= $tablename;
        $quiz_query .= " WHERE qq_question_id=%d";
        $quiz_query .= ' ORDER by qq_question_id ASC';
        $quiz_question_item = $wpdb->get_row($wpdb->prepare ($quiz_query, $questionid), ARRAY_A );

         if ( $quiz_question_item ){

            echo '<button class="btn btn-primary" style="margin-top: 10px;" onclick="backToQuizzes();">« Back To Quizzes</button>';
            echo '&nbsp';
            echo '<button class="btn btn-primary" style="margin-top: 10px;" onclick="backToQuiz();">« Back To Quiz</button><br>';
            echo '<h3>[Quiz Question #' . $questionid . '] ' . esc_attr(stripslashes($quiz_question_item['qq_question_text'])) . '</h3>';
            echo '<form type="POST" id="editQuizQuestionForm" action="">';
	    echo '<input type="hidden" name="action" value="editQuizQuestion" />';
	    echo '<input type="hidden" id="qq_quiz_id" name="qq_quiz_id" value="' . esc_attr($quizid) . ' "/>';
	    echo '<input type="hidden" id="qq_quiz_questionid" name="qq_quiz_questionid" value="' . esc_attr( $quiz_question_item['qq_question_id']) . ' "/>';
            echo '<span class="label label-info" style="font-size: 13px;">Question text</span><br>';
	    echo '<input type="text" id="qq_question_text" name="qq_question_text" maxlength="256" style="width: 95%; margin-top: 10px;"value="' . esc_attr(stripslashes( $quiz_question_item['qq_question_text'])) . '"/><br><br>';
            echo '<br>';
	    echo '<input type="submit" id="Submit" class="btn btn-success" value="Update Question">';
            echo '</form>';
            echo '<div id="feedbackQuestion"></div><br/>';
            echo '<div id="addQuizQuestionAnswer" style="display:none;">';
            echo '<h4 id="newQuizQuestionAnswerHeader">Answer:</h4>';
            echo '<form method="post" action="" id="addQuizQuestionAnswerForm">';
            echo '<p><textarea name="quizQuestionAnswer" id="qq_quiz_question_answer" rows="2" cols="50"></textarea></p>';
            echo '<p>Order: <input type="text" name="quizQuestionAnswerOrder" id="qq_quiz_question_answer_order" size="10"></p>';
            echo '<p>Correct answer: <input type="checkbox" name="quizQuestionAnswerGood" id="qq_quiz_question_answer_good" </p><br/><br/>';
            echo '<input type="hidden" name="action" value="addQuizQuestionAnswer">';
            echo '<input type="hidden" name="quizquestionid" id="qq_quiz_questionid" value="">';
            echo '<input type="hidden" name="quizanswerid" id="qq_quiz_answerid" value="">';
 	    echo '<input type="submit" id="Submit" class="btn btn-success" value="Add Answer">';
            echo '</form>';
            echo '<div id="feedbackNewQuestionAnswer"></div><br/>';
            echo '</div>';            
            echo '<h4>Answers listing</h4>';
            echo '<button class="button-primary" onclick="addQuizQuestionAnswer();">Add Answer</button><br/><br/>';
            echo '<div id="quizQuestionAnswersContainer">';

            getQuizQuestionAnswers();

            echo '</div>';

        } else {

            echo "Quiz question not found";

        }

    }

    

    die();

        

}



add_action('wp_ajax_editQuizQuestion', 'editQuizQuestion');

add_action('wp_ajax_nopriv_editQuizQuestion', 'editQuizQuestion');



function getQuizQuestionAnswers(){

    check_ajax_referer( 'qq_nonce_field', 'security' );

    global $wpdb;    


    $id = ( isset( $_POST['qq_quiz_questionid'] ) ? intval($_POST['qq_quiz_questionid']) : 0 );

    $output = '';
    $output .= '<form method="post" action="' . admin_url( 'admin-post.php' ) . '">';
    $output .= '  <input type="hidden" name="action" value="delete_qq_quiz_questionanswer" />';
    $output .= '  <!-- Adding security through hidden referrer field -->';
    $output .= '  <?php wp_nonce_field( "qq_quiz_questionanswer_deletion" ); ?>';
    $output .= '  <table class="wp-list-table widefat fixed table-hover" id="quiz_questionanswers_listing">';
    $output .= '      <thead>';
    $output .= '          <tr>';
    $output .= '              <th style="width: 50px;"></th>';
    $output .= '              <th style="width: 80px;">ID</th>';
    $output .= '              <th style="width:300px;">Answer text</th>';
    $output .= '              <th style="width:50px;">Answer order</th>';
    $output .= '              <th style="width:50px;">Correct answer</th>';
    $output .= '          </tr>';
    $output .= '      </thead>';
    $output .= '      <div class="quiz_questionanswer_listing">';
    $output .= '      <!-- Display bug list if no parameter sent in URL -->';

    $tablename1 = $wpdb->get_blog_prefix() . "quick_quiz_answers";
    $quiz_query = 'select * from ';
    $quiz_query .= $tablename1;
    $quiz_query .= " WHERE qq_question_id=" . $id;
    $quiz_query .= ' ORDER by ' . $tablename1 . '.qq_answer_order ASC,' . $tablename1 . '.qq_answer_id ASC';
    $quiz_answers = $wpdb->get_results($quiz_query, ARRAY_A );

    if ( $quiz_answers ) {

	foreach ( $quiz_answers as $quiz_answer ) {

            $output .= '<tr style="background: #FFF">';
            $output .= '<td id="' . $quiz_answer['qq_answer_id'] . '"><input type="checkbox" name="answers[]" value="';
            $output .= esc_attr($quiz_answer['qq_answer_id']) . '" /></td>';
            $output .= '<td answerid="' . $quiz_answer['qq_answer_id'] . '">' . esc_attr($quiz_answer['qq_answer_id']) . '</td>';
            $output .= '<td answer="' . $quiz_answer['qq_answer_text'] . '">' . esc_attr(stripslashes($quiz_answer['qq_answer_text'])) . '</td>';
            $output .= '<td answerorder="' . $quiz_answer['qq_answer_order'] . '">' . $quiz_answer['qq_answer_order'] . '</td>';

            if ($quiz_answer['qq_answer_isgood']==0){

                $output .= '<td answergood="' . esc_attr($quiz_answer['qq_answer_isgood']) . '">' . "No". '</td>';              

            } else {

                $output .= '<td answergood="' . esc_attr($quiz_answer['qq_answer_isgood']) . '">' . "Yes". '</td>';                             

            }

         }

    } else {

        $output .= '<tr style="background: #FFF">';

        $output .= '<td colspan=2>No Answer Found</td></tr>';

    }

                      

    $output .= '      </div>';

    $output .= '   </table><br/>';

    $output .= '</form>';

    $output .= '<button class="button-primary" onclick="ajaxDeleteQuizAnswers();">Delete Selected</button><br/><br/>';

   echo $output;

    

    die(); 

}



add_action('wp_ajax_getQuizQuestionAnswers', 'getQuizQuestionAnswers');
add_action('wp_ajax_nopriv_getQuizQuestionAnswers', 'getQuizQuestionAnswers');

function qq_delete_quiz_question_answers(){

    if(!current_user_can('manage_options'))

        wp_die('Not allowed');

    check_ajax_referer( 'qq_nonce_field', 'security' );

    $quiz_answers_to_delete = explode(",", $_POST['qq_answer_ids']);

    global $wpdb;

    foreach ($quiz_answers_to_delete as $answer_to_delete) {

        $query= 'DELETE FROM ' . $wpdb->get_blog_prefix() . "quick_quiz_answers";

        $query .= ' WHERE qq_answer_id=' . intval($answer_to_delete);

        $wpdb->query($wpdb->prepare($query));

    }

    

    die();

}



add_action('wp_ajax_deleteQuizQuestionAnswers', 'qq_delete_quiz_question_answers');
add_action('wp_ajax_nopriv_deleteQuizQuestionAnswers', 'qq_delete_quiz_question_answers');


function addNewQuizQuestionAnswer(){

    global $wpdb;

    check_ajax_referer( 'qq_nonce_field', 'security' );

    $quiz_data_question_answer = array();
    $quiz_data_question_answer['qq_answer_id'] = ( isset( $_POST['qq_quiz_answerid'] ) ? intval($_POST['qq_quiz_answerid']) : 0 );
    $quiz_data_question_answer['qq_question_id'] = ( isset( $_POST['qq_quiz_questionid'] ) ? intval($_POST['qq_quiz_questionid']) : 0 );
    $quiz_data_question_answer['qq_answer_text'] = ( isset( $_POST['qq_quiz_question_answer'] ) ? sanitize_text_field($_POST['qq_quiz_question_answer']) : '' );
    $quiz_data_question_answer['qq_answer_order'] = ( isset( $_POST['qq_quiz_question_answer_order'] ) ? intval($_POST['qq_quiz_question_answer_order']) : 0 );

    if (isset($_POST['qq_quiz_question_answer_good'])){

        if ($_POST['qq_quiz_question_answer_good']== 'true'){

            $quiz_data_question_answer['qq_answer_isgood'] = 1;

        } else {

            $quiz_data_question_answer['qq_answer_isgood'] = 0;  

        }

    } else {

        $quiz_data_question_answer['qq_answer_isgood'] = 0;       

    }    

    

    if (isset($_POST['qq_quiz_answerid']) && $_POST['qq_quiz_answerid']=='new'){

        if ($wpdb->insert($wpdb->get_blog_prefix() . 'quick_quiz_answers', $quiz_data_question_answer )===FALSE){

            echo "Error";

        } else {

            echo "Quiz Question Answer'" . esc_attr(stripslashes($quiz_data_question_answer['qq_answer_text'])) . "' successfully added";

        }

    } elseif (isset($_POST['qq_quiz_answerid'])){
        if (is_numeric($quiz_data_question_answer['qq_answer_id'])){
 
            if ($wpdb->update($wpdb->get_blog_prefix() . 'quick_quiz_answers', $quiz_data_question_answer, array('qq_answer_id'=>$quiz_data_question_answer['qq_answer_id']) )===FALSE){

                echo "Error";

            } else {
                echo "Quiz Question Answer '" . esc_attr(stripslashes($quiz_data_question_answer['qq_answer_text'])) . "' successfully updated";

            }        
        }   else {
            echo "Error";
        }
    } else {

        echo "Error: ID NOT SET: " . esc_attr($quiz_data_question_answer['qq_answer_id']);

   }

    die();

        

}

add_action('wp_ajax_addNewQuizQuestionAnswer', 'addNewQuizQuestionAnswer');
add_action('wp_ajax_nopriv_addNewQuizQuestionAnswer', 'addNewQuizQuestionAnswer');
add_shortcode('ShowQuickQuiz','qq_show_quiz');

function qq_show_quiz($atts){

    wp_enqueue_style('qq_display_quiz', QQ_PLUGIN_URL . 'asset/css/display_quiz.css');

    wp_enqueue_script( 'jquery' );
    wp_enqueue_style('thickbox');
    wp_enqueue_script('thickbox');
    wp_enqueue_script('qq_display_quiz', QQ_PLUGIN_URL . 'asset/js/display_quiz.js');

   $localize_script_data=array(

				'qq_ajax_url'=>admin_url( 'admin-ajax.php' ),
                                'qq_ajax_nonce'=>wp_create_nonce('qq_nonce_field'),
				'qq_site_url'=>site_url(),
				'plugin_url'=>QQ_PLUGIN_URL,
				'plugin_dir'=>QQ_PLUGIN_DIR

		);

    wp_localize_script( 'qq_display_quiz', 'display_quiz_data', $localize_script_data );    

    extract(shortcode_atts(array('quiz_id'=>''), $atts));
    extract(shortcode_atts(array('email_to'=>''), $atts));
    extract(shortcode_atts(array('show_title'=>''), $atts));
    extract(shortcode_atts(array('success_result'=>''), $atts));
    extract(shortcode_atts(array('show_results'=>''), $atts));
     

    global $wpdb;

    

    $tablename = $wpdb->get_blog_prefix() . "quick_quiz_data";

    $quiz_query = 'select * from ';

    $quiz_query .= $tablename;

    $quiz_query .= " WHERE qq_quiz_id=%d";

    $quiz_query .= ' ORDER by qq_quiz_id ASC';

    $quiz_item = $wpdb->get_row($wpdb->prepare ($quiz_query, $quiz_id), ARRAY_A );

    

    $output = '';

    if ( $quiz_item ){

        if (strtoupper($show_title)!="NO"){

            $output .= '<h3>Quiz :' . $quiz_item['qq_quiz_title'] . '</h3><br/>';

        }

        $output .= '<input type="hidden" name="quiz_title" value="' . esc_attr($quiz_item['qq_quiz_title']) . '" />'; 

        $output .= '<div id="quizContainer">';

//        $output .= '<form type="POST" id="ShowQuizForm" action="" onsubmit="return submitQuizForm()">';
        $output .= '<form type="POST" id="ShowQuizForm" action="">';

        $output .= '<h6> *Champ requis </h6>';

        $output .= '<div><label>Votre nom*:</label><br/>';

        $output .= '<input name="send_from_name" size="50" type="text" /><span id="send_from_name_error" class="quiz_error"></span></div>';

        $output .= '<div><label>Votre adresse de courriel*:</label><br/>';

        $output .= '<input name="send_from_email" size="50" type="text" /><span id="send_from_email_error" class="quiz_error"></span></div><br/>';



        $tablename1 = $wpdb->get_blog_prefix() . "quick_quiz_question_list";

        $tablename2 = $wpdb->get_blog_prefix() . "quick_quiz_questions";

        $quiz_questions_query = 'select * from ';

        $quiz_questions_query .= $tablename1;

        $quiz_questions_query .= ' INNER JOIN ';

        $quiz_questions_query .= $tablename2;

        $quiz_questions_query .= ' ON ' . $tablename1 . '.qq_question_id = ' . $tablename2 . '.qq_question_id';

        $quiz_questions_query .= " WHERE qq_quiz_id=" . $quiz_id;

        $quiz_questions_query .= ' ORDER by ' . $tablename1 . '.qq_question_id ASC';

        $quiz_questions = $wpdb->get_results($quiz_questions_query, ARRAY_A );

        if ( $quiz_questions ) {

            $question_no = 0;

             foreach ( $quiz_questions as $quiz_question ) {

                $question_no = $question_no + 1;

                $output .= '<input type="hidden" name="question_no' . esc_attr($question_no) . '" value="' . esc_attr($quiz_question['qq_question_id']) . '" />'; 

                $output .= '<label>' . esc_attr($question_no) . '. ' . esc_attr(stripslashes($quiz_question['qq_question_text'])) . '</label><span id="Question_' . esc_attr($question_no) . '_error" class="quiz_error"></span>';

                $tablename1 = $wpdb->get_blog_prefix() . "quick_quiz_answers";

                $quiz_answers_query = 'select * from ';

                $quiz_answers_query .= $tablename1;

                $quiz_answers_query .= " WHERE qq_question_id=" . $quiz_question['qq_question_id'];

                $quiz_answers_query .= ' ORDER by ' . $tablename1 . '.qq_answer_order ASC,' . $tablename1 . '.qq_answer_id ASC';

                $quiz_answers = $wpdb->get_results($quiz_answers_query, ARRAY_A );

                if ( $quiz_answers ) {

                    $answer_no = 'a';   

                    foreach ( $quiz_answers as $quiz_answer ) {

                        $output .= '<div class="quiz_radio_indent"> <input  type="radio" name="Question_' . esc_attr($question_no) . '" id="Answer_' . esc_attr($answer_no) . '" value="'

                                . esc_attr($quiz_answer['qq_answer_id']) . '"/>&nbsp' . '(' . esc_attr($answer_no) . ') ' . esc_attr(stripslashes($quiz_answer['qq_answer_text'])) . '</div>';

                        $answer_no++;               

                    }

                } else {

                       $output .= '<h5>No Answers found<h5>';

                }

                $output .= '<br/>';

             }

        } else {

            $output .= '<h5>No Questions found<h5>';

        }

        $output .= '<input type="hidden" name="quiz_title" value="' . esc_attr($quiz_item['qq_quiz_title']) . '" />'; 
        $output .= '<input type="hidden" name="email_to" value="' . esc_attr($email_to) . '" />'; 
        $output .= '<input type="hidden" name="success_result" value="' . esc_attr($success_result) . '" />'; 
        $output .= '<input type="hidden" name="show_results" value="' . esc_attr($show_results) . '" />'; 
        $output .= '<input type="hidden" name="totalQuestions" value="' . esc_attr($question_no) . '" />'; 

        $output .= '<input type="submit" align="middle" />';

        $output .= '</form>';

        $output .= '<div id="showPopupError" style="display:none;">';

        $output .= '<h4>Des erreurs ont été trouvées sur la page!</h4>';

        $output .= '<p>Vérifiez votre nom, votre courriel et que vous avez répondu à toutes les questions.</p>';

        $output .= '</div>';  

        $output .= '</div>';  

    }

    return $output;

    

}



function ajaxSendQuiz(){

    
    check_ajax_referer( 'qq_nonce_field', 'security' );

    $good_answers=0;

    parse_str($_POST['form_data'], $form_data);

    $send_from_name=sanitize_text_field($form_data['send_from_name']);

    $send_from_email=sanitize_text_field($form_data['send_from_email']);

    $total_questions=intval($form_data['totalQuestions']);

    $quiz_title=sanitize_text_field($form_data['quiz_title']);

    $answers = explode(";", $_POST['answers']);

//    $send_from_name=trim($_POST['send_from_name']);

//    $send_from_email=trim($_POST['send_from_email']);

    $sendto=sanitize_text_field($form_data['email_to']);
    $success_result=sanitize_text_field($form_data['success_result']);
    if ($success_result=='') {
        $success_result=60;
    }
    $show_results=sanitize_text_field($form_data['show_results']);
            
    $sendfrom = get_bloginfo('admin_email');

    $subject = "Quiz de " . $send_from_name . " (" . $send_from_email . ")";   

    $headers='';

    $headers .= "From: " . $sendfrom . "\r\n";

    $headers .= "Reply-To: " . $sendfrom . "\r\n";

    $headers .= "CC: " . $sendfrom . "\r\n";

    $headers .= "MIME-Version: 1.0\r\n";

    $headers .= "Content-Type: text/html; charset=utf-8\r\n";

    $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";

    $message = "<html><body>";

    $quiz_message='';

    for ($count = 1; $count <= intval($total_questions );$count++){

        $fieldname="question_no" . (string)$count;

        $question_id=sanitize_text_field($form_data[$fieldname]);

        $quiz_message .= "<p><h3>" . (string)$count . ". " .  stripslashes(getQuestionText ($question_id)) . ".</h3></p>";

        getAnswersText($question_id, $answers[$count-1], $quiz_message, $good_answers);
        
    }

    

    $message .= '<h2>Quiz: ' . $quiz_title . '</h2>';

    $message .= '<h3>Nom: ' . $send_from_name . '</h3>';
    $message .= '<h3>Courriel: ' . $send_from_email . '</h3>';

    $message .= "<p><h3>Résultat du quiz: " . strval(round(($good_answers / intval($total_questions ))*100,0)) . "/100</h3></p>";

    $message = $message . $quiz_message;

    $message .= "<br/><p><h3>Envoyé par QuickQuiz</h3></p>";

    $message .="</body></html>";  

    $output = '';

    if (mail($sendto, $subject, $message, $headers)){

        $output .= '<h3>Quiz envoyé avec succès</h3>';
        $output .= '<p><h4>Vous avez obtenu un résultat de: ' . 
            strval(round(($good_answers / intval($total_questions ))*100,0)) . '/100</h4></p>';      
        $output .= '<p><h6>La note de passage est: ' . $success_result . '/100</h6></p>';
        
        if ($show_results=='YES') {
            $output .= '<p><h4>Voici le résutlat de votre quiz: </h4></p>';
            $output .= $quiz_message;               
        } elseif ($show_results=='ONSUCCESS' AND (round(($good_answers / intval($total_questions ))*100,0)) > intval($success_result)) {
            $output .= '<p><h4>Voici le résutlat de votre quiz: </h4></p>';
            $output .= $quiz_message;                    
        }
        
    } else {

       $output .= '<h3>Le quiz n\' pu être envoyé.</h3>';

    }

    echo $output;

    // Always die in functions echoing ajax content

   die();

}

add_action('wp_ajax_ajaxSendQuiz', 'ajaxSendQuiz');
add_action('wp_ajax_nopriv_ajaxSendQuiz', 'ajaxSendQuiz');

function getQuestionText ($question_id){

    
    global $wpdb;

    $tablename1 = $wpdb->get_blog_prefix() . "quick_quiz_questions";

    $quiz_question_query = 'select * from ';

    $quiz_question_query .= $tablename1;

    $quiz_question_query .= " WHERE qq_question_id=" . $question_id;

    $quiz_question = $wpdb->get_row($quiz_question_query, ARRAY_A );

    if ( $quiz_question ) {

        return $quiz_question['qq_question_text'];

    } else {

        return "";

    }

}



function getAnswersText($question_id, $answer_id, &$message, &$good_answers){

 

    global $wpdb;

 

    $tablename1 = $wpdb->get_blog_prefix() . "quick_quiz_answers";

    $quiz_answers_query = 'select * from ';

    $quiz_answers_query .= $tablename1;

    $quiz_answers_query .= " WHERE qq_question_id=" . $question_id;

    $quiz_answers_query .= ' ORDER by ' . $tablename1 . '.qq_answer_id ASC';

    $quiz_answers = $wpdb->get_results($quiz_answers_query, ARRAY_A );

    if ( $quiz_answers ) {

        $answer_no = 'a';

         foreach ( $quiz_answers as $quiz_answer ) {

            $style='';

            if ($quiz_answer['qq_answer_id']==intval($answer_id) && $quiz_answer['qq_answer_isgood']==1){

                $style='style="color:green;font-weight: bold;"';

                $good_answers++;

            } elseif ($quiz_answer['qq_answer_id']==intval($answer_id) && $quiz_answer['qq_answer_isgood']==0){

                $style='style="color:red;font-weight: bold;"';

            } elseif ($quiz_answer['qq_answer_isgood']==1){

                $style='style="color:green;"';

            }

            $message .= '<div ' . $style . '>&nbsp' . '(' . esc_attr($answer_no) . ') ' . esc_attr(stripslashes($quiz_answer['qq_answer_text'])) . '</div><br/>';

            $answer_no++;

        }

    }



}

//function example_ajax_request() {
//
// 
//
//    // The $_REQUEST contains all the data sent via ajax
//
//    if ( isset($_REQUEST) ) {
//
//     
//
//        $fruit = $_REQUEST['fruit'];
//
//         
//
//        // Let's take the data that was sent and do something with it
//
//        if ( $fruit == 'Banana' ) {
//
//            $fruit = 'Apple';
//
//        }
//
//     
//
//        // Now we'll return it to the javascript function
//
//        // Anything outputted will be returned in the response
//
//        echo $fruit;
//
//         
//
//        // If you're debugging, it might be useful to see what was sent in the $_REQUEST
//
//        // print_r($_REQUEST);
//
//     
//
//    }
//
//     
//
//    // Always die in functions echoing ajax content
//
//   die();
//
//}
//
// 
//
//add_action( 'wp_ajax_example_ajax_request', 'example_ajax_request');



?>



