<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!-- Nav tabs -->
<ul class="nav nav-tabs">
	<li class="active"><a href="#quizContainer" id="tab_quiz_container" data-toggle="tab">Quizzes</a></li>
	<li><a href="#create_quiz" id="tab_create_quiz" data-toggle="tab">Create New Quiz</a></li>
</ul>
<!-- Tab panes -->
<div class="tab-content">
	<!-- Quizzes Tab Body Start Here -->
	<div class="tab-pane active" id="quizContainer">
                <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="qq_delete_quiz" />
                <!-- Adding security through hidden referrer field -->
                <?php wp_nonce_field( 'qq_quiz_main_tab' ); ?>
                <table class="wp-list-table widefat fixed table-hover" id="quiz_listing">
                 <thead>
                    <tr>
                        <th style="width: 50px;"></th>
                        <th style="width: 80px;">ID</th>
                        <th style="width:300px;">Title</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <div class="quiz_listing">
            <!-- Display bug list if no parameter sent in URL -->
            <?php if ( empty( $_GET['id'] ) ) { 
                $tablename = $wpdb->get_blog_prefix() . "quick_quiz_data";
		$quiz_query = 'select * from ';
                $quiz_query .= $tablename;
                $quiz_query .= ' WHERE qq_quiz_owner_id=' . $current_user->ID . ' OR qq_quiz_owner_id IS NULL'; 
		$quiz_query .= ' ORDER by qq_quiz_id ASC';
		$quiz_items = $wpdb->get_results($quiz_query, ARRAY_A );
            ?>
            <?php 
		// Display quizzes if query returned results
		if ( $quiz_items ) {
			foreach ( $quiz_items as $quiz_item ) {
				echo '<tr style="background: #FFF">';
				echo '<td id="' . $quiz_item['qq_quiz_id'] . '"><input type="checkbox" name="quizzes[]" value="';
				echo esc_attr( $quiz_item['qq_quiz_id'] ) . '" /></td>';
				echo '<td>' . esc_attr($quiz_item['qq_quiz_id']) . '</td>';
				echo '<td>' . stripslashes(esc_attr($quiz_item['qq_quiz_title'])) . '</td>';
				echo '<td>' . stripslashes(esc_attr($quiz_item['qq_quiz_description'])) . '</td></tr>';
			}
		} else {
			echo '<tr style="background: #FFF">';
			echo '<td colspan=3>No Quiz Found</td></tr>';
		}
	?> 
            <?php }
        ?>
                    
                </div>
                </table><br/>
             	
                <input type="submit" value="Delete Selected" class="button-primary"/>
            </form>
	</div>

	<!-- Quizzes Tab Body End Here -->
	<!-- Create New Quiz Tab Body Start Here -->
	<div class="tab-pane" id="create_quiz">
		<div id="create_quiz_container" style="">
                    <?php include_once( QQ_PLUGIN_DIR.'includes/admin/create_new_quiz.php' );?>
		</div>
	</div>

	<!-- Create New Quiz Tab Body End Here -->
</div>