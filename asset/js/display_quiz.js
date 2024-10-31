jQuery(document).ready(function() {
    jQuery('#quiz_listing tr').live('dblclick',(function() {
        var id = jQuery(this).find("td").attr("id");
        var ajax_url = display_quiz_data.qq_ajax_url;
        var ajax_nonce = display_quiz_data.qq_ajax_nonce;
        if(id) {
            jQuery.ajax({
                type:"POST",
                url: ajax_url,
                data: { action: 'editQuiz',
                        security: ajax_nonce,
                        qq_quiz_id: parseInt(id)},
                success:function(data){  
                    jQuery("#quizContainer").html(data);
                }
            });
            return false;
        }
    }));
});

function ajaxDeleteQuizQuestion(){
 
    var all_location_id = document.querySelectorAll('input[name="questions[]"]:checked');
    var aIds = [];
    for(var x = 0, l = all_location_id.length; x < l;  x++)
    {
        aIds.push(all_location_id[x].value);
    }

    if(aIds) {
        var str = aIds.join(',');
        var ajax_url = display_quiz_data.qq_ajax_url;
        var ajax_nonce = display_quiz_data.qq_ajax_nonce;
        var quiz_id=document.forms["editQuizForm"]["qq_quiz_id"].value;
        jQuery.ajax({
            type:"POST",
            url: ajax_url,
                data: { action: 'deleteQuizQuestions',
                        security: ajax_nonce,
                        qq_questions_ids: str,
                        qq_quiz_id: quiz_id
                },
                success:function(data){  
                    ajaxGetQuizQuestions(quiz_id);
                }
            });
            return false;
        }

}      
       
jQuery(document).ready(function() {
    jQuery('#quiz_question_listing tr').live('dblclick',(function() {
        var quiz_id = document.forms["editQuizForm"]["qq_quiz_id"].value;
        var id = jQuery(this).find("td").attr("id");
        var ajax_url = display_quiz_data.qq_ajax_url;
        var ajax_nonce = display_quiz_data.qq_ajax_nonce;
        if(id) {
            jQuery.ajax({
                type:"POST",
                url: ajax_url,
                data: { action: 'editQuizQuestion',
                        security: ajax_nonce,
                        qq_quiz_id: parseInt(quiz_id),
                        qq_quiz_questionid: parseInt(id)},
                success:function(data){  
                    jQuery("#quizContainer").html(data);
                }
            });
            return false;
        }
    }));
});

function ajaxDeleteQuizAnswers(){
 
    var all_location_id = document.querySelectorAll('input[name="answers[]"]:checked');
    var aIds = [];
    for(var x = 0, l = all_location_id.length; x < l;  x++)
    {
        aIds.push(all_location_id[x].value);
    }

    if(aIds) {
        var str = aIds.join(',');
        var ajax_url = display_quiz_data.qq_ajax_url;
        var ajax_nonce = display_quiz_data.qq_ajax_nonce;
        var question_id=document.forms["editQuizQuestionForm"]["qq_quiz_questionid"].value;
        jQuery.ajax({
            type:"POST",
            url: ajax_url,
                data: { action: 'deleteQuizQuestionAnswers',
                        security: ajax_nonce,
                        qq_answer_ids: str,
                },
                success:function(data){  
                    ajaxGetQuizAnswers(question_id);
                }
            });
            return false;
        }

}      

jQuery(document).ready(function() {
    jQuery('#quiz_questionanswers_listing tr').live('dblclick',(function() {
        var $row=jQuery(this).find("td");
        var id = jQuery(this).find("td").attr("id");
        var answer = $row.eq(2).text();
        var answerid = $row.eq(1).text();
        var answerorder= $row.eq(3).text();
        var answergood
        if ($row.eq(4).text()=="Yes"){
            answergood=1;
        } else {
            answergood=0;
        }
            
        document.forms["addQuizQuestionAnswerForm"]["qq_quiz_question_answer"].value=answer; 
        document.forms["addQuizQuestionAnswerForm"]["qq_quiz_questionid"].value=document.forms["editQuizQuestionForm"]["qq_quiz_questionid"].value; 
        document.forms["addQuizQuestionAnswerForm"]["qq_quiz_answerid"].value=answerid;
        document.forms["addQuizQuestionAnswerForm"]["qq_quiz_question_answer_order"].value=answerorder;
        document.forms["addQuizQuestionAnswerForm"]["qq_quiz_question_answer_good"].checked=answergood;
        document.forms["addQuizQuestionAnswerForm"]["Submit"].value="Update Answer";
        tb_show("Edit a quiz question answer","#TB_inline?height=300&amp;width=425&amp;inlineId=addQuizQuestionAnswer");
        setTimeout( function () { jQuery('#qq_quiz_question_answer').focus(); }, 250 );
    }));   
});


function ajaxUpdate(){
    var ajax_url = display_quiz_data.qq_ajax_url;
    var ajax_nonce = display_quiz_data.qq_ajax_nonce;
    var quiz_id = document.forms["editQuizForm"]["qq_quiz_id"].value; 
    var quiz_title = document.forms["editQuizForm"]["qq_quiz_title"].value; 
    var quiz_description = document.forms["editQuizForm"]["qq_quiz_description"].value; 
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
            jQuery("#feedback").html(data);
        }
    });
    return false;
    }
    return false;
}

 jQuery(document).ready(function(){
            jQuery('#editQuizForm').live('submit',(ajaxUpdate));
       });

      
function ajaxAddQuizQuestion (){
    var ajax_url = display_quiz_data.qq_ajax_url;
    var ajax_nonce = display_quiz_data.qq_ajax_nonce;
    var quiz_id = document.forms["addQuizQuestionForm"]["qq_quiz_id"].value; 
    var quiz_questionid = document.forms["addQuizQuestionForm"]["qq_quiz_questionid"].value;
    var quizNewQuestion=document.forms["addQuizQuestionForm"]["qq_quiz_question"].value; 
    if (quizNewQuestion  == "" || quizNewQuestion == null){
        jQuery("#feedbackNewQuestion").html("Question required");
    } else {
    jQuery.ajax({
        type:"POST",
        url: ajax_url,
        data: { action: 'addNewQuizQuestion',
                security: ajax_nonce,
                qq_quiz_id:quiz_id,
                qq_quiz_questionid:quiz_questionid,
                qq_quiz_question:quizNewQuestion,},  
        success:function(data){
            if (data.substring(0,5) == "Error"){
                jQuery("#feedbackNewQuestion").html(data);
            } else {
                self.parent.tb_remove();
                ajaxGetQuizQuestions(quiz_id);
            }
        }
    });
    return false;
    }
    return false;    
}

jQuery(document).ready(function(){
            jQuery('#addQuizQuestionForm').live('submit',(ajaxAddQuizQuestion));
       });

function ajaxEditQuizQuestion (){
    var ajax_url = display_quiz_data.qq_ajax_url;
    var ajax_nonce = display_quiz_data.qq_ajax_nonce;
    var quiz_id = document.forms["editQuizQuestionForm"]["qq_quiz_id"].value; 
    var quiz_questionid = document.forms["editQuizQuestionForm"]["qq_quiz_questionid"].value;
    var quizNewQuestion=document.forms["editQuizQuestionForm"]["qq_question_text"].value; 
    if (quizNewQuestion  == "" || quizNewQuestion == null){
        jQuery("#feedbackQuestion").html("Question required");
    } else {
    jQuery.ajax({
        type:"POST",
        url: ajax_url,
        data: { action: 'addNewQuizQuestion',
                security: ajax_nonce,
                qq_quiz_id:quiz_id,
                qq_quiz_questionid:quiz_questionid,
               qq_quiz_question:quizNewQuestion,},  
        success:function(data){
            if (data.substring(0,5) == "Error"){
                jQuery("#feedbackQuestion").html(data);
            } else {
               jQuery("#feedbackQuestion").html(data); 
            }
        }
    });
    return false;
    }
    return false;    
}

jQuery(document).ready(function(){
            jQuery('#editQuizQuestionForm').live('submit',(ajaxEditQuizQuestion));
       });

function ajaxGetQuizQuestions(quiz_id){
 
    var ajax_nonce = display_quiz_data.qq_ajax_nonce;
    jQuery.ajax({
        type:"POST",
        url: display_quiz_data.qq_ajax_url,
        data: {
            action:'getQuizQuestions',
            security: ajax_nonce,
            qq_quiz_id:quiz_id,
        },
        success:function(data) {
            // This outputs the result of the ajax request
            jQuery("#quizQuestionsContainer").html(data);
        },
        error: function(errorThrown){
            jQuery("#quizQuestionsContainer").html(data);
        }
    });  
}

function getQuizzes(){
	
    var ajax_nonce = display_quiz_data.qq_ajax_nonce;
    jQuery.ajax({
        url: display_quiz_data.qq_ajax_url,
        data: {
            action:'getQuizzes',
            security: ajax_nonce,
        },
        success:function(data) {
            // This outputs the result of the ajax request
            jQuery("#quizContainer").html(data);
        },
        error: function(errorThrown){
            jQuery("#quizContainer").html(data);
        }
    });
 
//	jQuery.ajax(display_quiz_data.qq_ajax_url, data, function(response) {
//		jQuery('#quizContainer').html(response);
//	});
}

function backToQuizzes(){
	getQuizzes();
}

function getQuiz(quizid){
	
    var ajax_url = display_quiz_data.qq_ajax_url;
    var ajax_nonce = display_quiz_data.qq_ajax_nonce;
    if(quizid) {
        jQuery.ajax({
            type:"POST",
            url: ajax_url,
            data: { action: 'editQuiz',
                    security: ajax_nonce,
                    qq_quiz_id: parseInt(quizid)},
            success:function(data){  
                jQuery("#quizContainer").html(data);
            }
        });
        return false;
    }

}

function backToQuiz(){
    var $quizId=document.forms["editQuizQuestionForm"]["qq_quiz_id"].value; 
    if ($quizId){
        getQuiz($quizId);
    }
}

function addQuizQuestion(){
    var quizNewQuestion=document.forms["addQuizQuestionForm"]["qq_quiz_question"]; 
    quizNewQuestion.value="";
    document.forms["addQuizQuestionForm"]["qq_quiz_id"].value=document.forms["editQuizForm"]["qq_quiz_id"].value; 
    document.forms["addQuizQuestionForm"]["qq_quiz_questionid"].value="new";
    tb_show("Add a quiz question","#TB_inline?height=225&amp;width=425&amp;inlineId=addQuizQuestion");
    //setTimeout(jQuery('#qq_quiz_question').focus(), 500);
    setTimeout( function () { jQuery('#qq_quiz_question').focus(); }, 250 );
}

function addQuizQuestionAnswer(){
    var quizNewQuestion=document.forms["addQuizQuestionAnswerForm"]["qq_quiz_question_answer"]; 
    quizNewQuestion.value="";
    document.forms["addQuizQuestionAnswerForm"]["qq_quiz_questionid"].value=document.forms["editQuizQuestionForm"]["qq_quiz_questionid"].value; 
    document.forms["addQuizQuestionAnswerForm"]["qq_quiz_answerid"].value="new";
    tb_show("Add a quiz question answer","#TB_inline?height=300&amp;width=425&amp;inlineId=addQuizQuestionAnswer");
    //setTimeout(jQuery('#qq_quiz_question').focus(), 500);
    setTimeout( function () { jQuery('#qq_quiz_question_answer').focus(); }, 250 );
}

function ajaxAddQuizQuestionAnswer (){
    var ajax_url = display_quiz_data.qq_ajax_url;
    var ajax_nonce = display_quiz_data.qq_ajax_nonce;
    var quiz_answerid = document.forms["addQuizQuestionAnswerForm"]["qq_quiz_answerid"].value; 
    var quiz_questionid = document.forms["addQuizQuestionAnswerForm"]["qq_quiz_questionid"].value;
    var quizNewAnswer=document.forms["addQuizQuestionAnswerForm"]["qq_quiz_question_answer"].value; 
    var quizNewAnswerOrder=document.forms["addQuizQuestionAnswerForm"]["qq_quiz_question_answer_order"].value; 
    var quizNewAnswerGood=document.forms["addQuizQuestionAnswerForm"]["qq_quiz_question_answer_good"].checked; 
    
    if (quizNewAnswer  == "" || quizNewAnswer == null){
        jQuery("#feedbackNewQuestionAnswer").html("Answer required");
    } else {
    jQuery.ajax({
        type:"POST",
        url: ajax_url,
        data: { action: 'addNewQuizQuestionAnswer',
                security: ajax_nonce,
                qq_quiz_answerid:quiz_answerid,
                qq_quiz_questionid:quiz_questionid,
                qq_quiz_question_answer:quizNewAnswer,
                qq_quiz_question_answer_order:quizNewAnswerOrder,
                qq_quiz_question_answer_good:quizNewAnswerGood,
        },  
        success:function(data){
            if (data.substring(0,5) == "Error"){
                jQuery("#feedbackNewQuestionAnswer").html(data);
            } else {
                self.parent.tb_remove();
                ajaxGetQuizAnswers(quiz_questionid);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('There was an error processing your request...');
            jQuery("#feedbackNewQuestionAnswer").html(textStatus.concat(":", errorThrown));
        }
    });
    return false;
    }
    return false;    
}

jQuery(document).ready(function(){
            jQuery('#addQuizQuestionAnswerForm').live('submit',(ajaxAddQuizQuestionAnswer));
       });

function ajaxGetQuizAnswers(quiz_questionid){
 
    var ajax_nonce = display_quiz_data.qq_ajax_nonce;
    jQuery.ajax({
        type:"POST",
        url: display_quiz_data.qq_ajax_url,
         data: {
            action:'getQuizQuestionAnswers',
            security: ajax_nonce,
            qq_quiz_questionid:quiz_questionid,
        },
        success:function(data) {
            // This outputs the result of the ajax request
            jQuery("#quizQuestionAnswersContainer").html(data);
        },
        error: function(errorThrown){
            jQuery("#quizQuestionAnswersContainer").html(data);
        }
    });  
}

function submitQuizForm (){
    //Valid form
    currentField="";
    error_found=false;
    qq_clear_error("send_from_name");
    qq_clear_error("send_from_email");
    var totalQuestions = document.forms["ShowQuizForm"]["totalQuestions"].value;
    for (var i=0; i<totalQuestions-1; i++){
        var n=i+1;
        var question_prefix = "Question_";
        var question_name=question_prefix.concat(n.toString());
        qq_clear_error(question_name);
    }
        
    var name = document.forms["ShowQuizForm"]["send_from_name"].value;
    var email = document.forms["ShowQuizForm"]["send_from_email"].value;

    if (name=="" || name== null) {
        showerror("send_from_name", " * Le nom est obligatoire"); 
        error_found=true;
    }
    
    if (email  == "" || email  == null) {
       showerror("send_from_email", " * L'adresse courriel est obligatoire"); 
       error_found=true;
    } else if (email.indexOf("@") == -1 || email.indexOf(".") == -1) {
        showerror("send_from_email", " * Le format de l'adresse courriel est invalide"); 
        error_found=true;
    }
    
    //Valid all questions answered
    var answers ="";
    for (var i=0; i<totalQuestions; i++){
        var n=i+1;
        var question_prefix = "Question_";
        var question_name=question_prefix.concat(n.toString());
        var question_field= "input[name='";
        var question_field=question_field.concat(question_name,"']:checked");
        if (!jQuery(question_field).val()){
            showerror(question_name, " * Aucune réponse");
            error_found=true;
            answers= answers.concat ("0;")
        } else {
            answers = answers.concat(jQuery(question_field).val(),";");
        }
    }
    
    if (error_found) {
        tb_show("Erreurs trouvées","#TB_inline?height=175&amp;width=400&amp;inlineId=showPopupError");
	return false;
    } else {
        ajaxSendQuiz(answers);
        return true;
    }

}

function qq_clear_error(field){
    var span = document.getElementById(field.concat("_error"));
    if (span != null){ 
        while( span.firstChild ) {
            span.removeChild( span.firstChild );
        }
    }
}

function showerror(field, value){
       var span = document.getElementById(field.concat("_error"));
       while( span.firstChild ) {
          span.removeChild( span.firstChild );
       }
       span.appendChild( document.createTextNode(value) );
       span.className="quiz_error";
       if (currentField=="" || currentField== null) {      
           currentField=field;
       }
}

function ajaxSendQuiz(answers){

    var ajax_nonce = display_quiz_data.qq_ajax_nonce;

//    var name = document.forms["ShowQuizForm"]["send_from_name"].value;
//    var email = document.forms["ShowQuizForm"]["send_from_email"].value;
    jQuery.ajax({
        type:"POST",
        url: display_quiz_data.qq_ajax_url,
        async: false,
        data: {
            action:'ajaxSendQuiz',
            security: ajax_nonce,
            answers : answers,
            form_data : jQuery('#ShowQuizForm').serialize(),
         },
        success:function(data) {
            // This outputs the result of the ajax request
            jQuery("#quizContainer").html(data);
            //window.location.assign(data);
        },
        error: function(errorThrown){
            jQuery("#quizContainer").html(data);
        }
    });  
}

jQuery(document).ready(function(){
            jQuery('#ShowQuizForm').live('submit',(submitQuizForm));
    });