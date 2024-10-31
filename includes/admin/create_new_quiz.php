<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php $qq_quiz_id='new'; ?>
<h3>Create New Quiz</h3>
<form type="POST" id="newQuizForm" action="">
	<input type="hidden" name="action" value="addNewQuiz" />
	<input type="hidden" id="qq_quiz_id" name="qq_quiz_id" value="<?php echo esc_attr( $qq_quiz_id ); ?>" />
 	<span class="label label-info" style="font-size: 13px;">Title</span><br>
	<input type="text" id="qq_quiz_title" name="qq_quiz_title" maxlength="80" style="width: 95%; margin-top: 10px;"/><br><br>
	<span class="label label-info" style="font-size: 13px;">Description</span><br>
	<textarea id="qq_quiz_description" name="qq_quiz_description" style="margin-top: 10px; width: 95%; overflow-y:hidden;" onkeyup='this.rows = (this.value.split("\n").length||1);'></textarea><br><br>

	<br>
	<input type="hidden" name="action" value="createNewQuiz">
	<input type="hidden" name="user_id" value="<?php echo $current_user->ID;?>">
	<input type="hidden" name="type" value="user">
	<input type="submit" id="Submit" class="btn btn-success" value="Submit Quiz">
        <input type="button" class="btn btn-success" value="Reset Form" onClick="this.form.reset()" >
</form>               
        <div id="feedback"></div>
        <script type="text/javascript">
            function simpleajax(){
            var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
            var fruit = 'Banana';
            // This does the ajax request
            jQuery.ajax({
                url: ajax_url,
                data: {
                    'action':'example_ajax_request',
                    'fruit' : fruit
                },
                success:function(data) {
                    // This outputs the result of the ajax request
                    jQuery("#feedback").html(data);
                },
                error: function(errorThrown){
                    jQuery("#feedback").html(data);
                }
            });
        }
            function ajaxSubmit(){
                var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                var ajax_nonce = '<?php echo wp_create_nonce('qq_nonce_field'); ?>';
                var quiz_id = document.forms["newQuizForm"]["qq_quiz_id"].value; 
                var quiz_title = document.forms["newQuizForm"]["qq_quiz_title"].value; 
                var quiz_description = document.forms["newQuizForm"]["qq_quiz_description"].value; 
                if (quiz_title  == "" || quiz_title  == null){
                    jQuery("#feedback").html("Title required");
                } else {
                jQuery.ajax({
                    type:"POST",
                    url: ajax_url,
                    data: { action: 'addNewQuiz',
                            security: ajax_nonce,
                            qq_quiz_id:quiz_id,
                            qq_quiz_title:quiz_title,
                            qq_quiz_description:quiz_description},  
                    success:function(data){  
                        getQuizzes();
                        jQuery("#feedback").html(data);
                    }
                });
                return false;
                }
                return false;
            }
//        jQuery(document).ready(function(){
//            setTimeout(function(){
//                tb_show('Pop-Up Message','<?php echo plugins_url('content.html?width=420&height=220',__FILE__)?>',null);
//            }, 500);
//        });
        jQuery(document).ready(function(){
            jQuery('#newQuizForm').submit(ajaxSubmit);
        });
        </script>
<!--        <script type="text/javascript">  
            function ajaxSubmit(){
                alert ('ok');
                var newQuizForm = jQuery(this).serialize();    
                jQuery.ajax({
                    type:"POST",
                    url: "/wp-admin/admin-ajax.php",
                    data: newQuizForm,  
                    success:function(data){  
                        jQuery("#feedback").html(data);
                    }
                });
                return false;
            }
            jQuery(document).ready(function(){
                setTimeout(function(){
                    tb_show('Pop-Up Message','<?php echo plugins_url('content.html?width=420&height=220',__FILE__)?>',null);
                }, 2000)
                jQuery('.show_closed_bugs').submit(ajaxSubmit); 
            }
        </script>-->
