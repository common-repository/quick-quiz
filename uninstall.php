<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();
global $wpdb;

if (is_multisite()){
    if (!empty($GET['networkwide'])){
        $start_blog=$wpdb->blogid;
        $blog_list=$wpdb->get_col('SELECT blog_id FROM ' . $wpdb->blogs);
            foreach($blog_list as $blog){
                switch_to_blog($blog);
                quick_quiz_drop_table($wpdb->get_blog_prefix());
            }
            switch_to_blog($start_blog);
            return;
    }
}
quick_quiz_drop_table($wpdb->get_blog_prefix());

function quick_quiz_drop_table($prefix){
    global $wpdb;
    $wpdb->query($wpdb->prepare('DROP TABLE ' . $prefix . 'quick_quiz_data'));
    $wpdb->query($wpdb->prepare('DROP TABLE ' . $prefix . 'quick_quiz_questions'));
    $wpdb->query($wpdb->prepare('DROP TABLE ' . $prefix . 'quick_quiz_question_list'));
    $wpdb->query($wpdb->prepare('DROP TABLE ' . $prefix . 'quick_quiz_answers'));
    $wpdb->query($wpdb->prepare('DROP TABLE ' . $prefix . 'quick_quiz_exam_data'));
    $wpdb->query($wpdb->prepare('DROP TABLE ' . $prefix . 'quick_quiz_exam_data_answers'));
    $wpdb->query($wpdb->prepare('DROP TABLE ' . $prefix . 'quick_quiz_exam_data_questions'));
}

?>