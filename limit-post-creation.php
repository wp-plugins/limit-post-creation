<?php
/*
Plugin Name: Limit Post Creation
Plugin URI: http://blog.glue-labs.com/359/wordpress-plugin-limit-post-creation/
Description: this plugin helps you to limit the number of posts/pages/custom post types each user can create on your site.
Version: 1.3
Author: Glue Labs - Makes extraordinary things
Author URI: http://www.glue-labs.com/
*/
/*  Copyright 2011  Glue Labs - Makes extraordinary things ( info@glue-labs.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
include 'misc/functions.php';
/**
 * Check capabilities to allow or deny access to create new page/post
 */
function spaw_lpc_check_cap($capabilities) {
    global $pagenow;
    //get current user data
     $current_user = wp_get_current_user();
 //if there is no user return false 
 if (! $current_user) {
    return FALSE;
  }
  $role_admin = spaw_lpc_get_role('administrator',$current_user->roles);
  //check if the user has administrator role
  if ($role_admin) {
    return $capabilities;
  } 
  //check if the page is different from post-new.php ( page that allow creation of new Post/Page
  if($pagenow!='post-new.php'){//substr($_SERVER['PHP_SELF'],-12) !== 'post-new.php'
      return $capabilities;
  }
  //retrieve type of page requested, if to crete new page or post
 $page_type = spaw_lpc_get_page_type();
 
 //retrieve options from db, at the moment: post_role_limits ,exclude_users 
 $options = array();
 $options['post_role_limits'] = get_option('spaw_lpc_post_role_limits');
 $options['exclude_users'] = get_option('spaw_lpc_exclude_users');
 
 //chek if the current user is a special one
 if(spaw_lpc_check_special_user($options['exclude_users'], $current_user->ID)) return $capabilities;
 
 //retrieve post/page role limit for the user
 $limit = false;
  foreach ($current_user->roles as $role) {
      if($page_type=='post'){
    if (isset($options['post_role_limits'][$role]['post'])) {
      if ($options['post_role_limits'][$role]['post'] == -1) {
        return $capabilities;
      } else if ($options['post_role_limits'][$role]['post'] > $limit) {
        $limit = $options['post_role_limits'][$role]['post'];
        
      }
    }
  }//if page is post
  if($page_type=='page'){
    if (isset($options['post_role_limits'][$role]['page'])) {
      if ($options['post_role_limits'][$role]['page'] == -1) {
        return $capabilities;
      } else if ($options['post_role_limits'][$role]['page'] > $limit) {
        $limit = $options['post_role_limits'][$role]['page'];

      }
    }
  }//if page is page
  //retrive time
  $timeLimit = 'ever';
  if ($options['post_role_limits'][$role]['time']!=$timeLimit){
          $timeLimit = $options['post_role_limits'][$role]['time'];
           }
  }
 $limit = intval($limit);
 //retrieve number of post/page for the current user
 if($timeLimit=='ever'){  
     $posts = get_posts(array('numberposts' => $limit, 'author' => $current_user->ID, 'post_status' => 'publish','post_type'=>$page_type));
     $nrPosts = count($posts);
 }else{
    $nrPosts = get_nr_post($timeLimit,$page_type);
 }
  
 
 //if limit is setted and reached deny access to user for new post
 if($limit==true && ($nrPosts>$limit || $nrPosts==$limit)){
     wp_die( __('<div style="color:red;font-size:20px">Limit Post/Page Creation Warning</div>
<div style="font-weight:bold">You reached the maximum published allowed Post, return to
<a href="index.php">Dashboard</a> or contact your administrator</div>') );
 }
    return $capabilities;
}
/**
 * get number of posts by formatting query
 * @param string $where where clause in query
 * @param string $page_type the kind of page
 * @return int number of posts
 */
function get_nr_post($where,$page_type) {
   $filter = false; 
    switch ($where) {
        case 'day':
            add_filter( 'posts_where', 'filter_day' );
            $filter=1;
            break;
        case 'week':
            add_filter( 'posts_where', 'filter_week' );
            $filter=2;
            break;
        case 'month':
            add_filter( 'posts_where', 'filter_month' );
            $filter=3;
            break;
        default:
            break;
    }    
$query_string = array( 'author' => $current_user->ID, 'post_status' => 'publish','post_type'=>$page_type);
$query = new WP_Query( $query_string );
$NrPosts =  $query->post_count;
switch ($filter) {
    case 1:
        remove_filter( 'posts_where', 'filter_day' );
        break;
    case 2:
        remove_filter( 'posts_where', 'filter_week' );
        break;
    case 3:
        remove_filter( 'posts_where', 'filter_month' );
        break;
    default:
        break;
}

return $NrPosts;
}
/**
 * Build menu under settings for Plugin
 */
function build_spaw_lpc_menu() {
  if (function_exists('add_options_page')) {
    add_options_page(__('Limit Post Creation'), __('Limit Post Creation'), 'manage_options', __FILE__, 'spaw_lpc_page');
  }
}
/**
 * create plugin options page
 */
function spaw_lpc_page() {  
  global $wp_roles;
  if (! isset($wp_roles)) {
    $wp_roles = new WP_Roles();
  } 
  //check form submit
  if (isset($_POST['post_role_limits']) && is_array($_POST['post_role_limits'])) {//if data were submitted
    $options = array('post_role_limits' => $_POST['post_role_limits']);//retrieve options for ROLE
    update_option('spaw_lpc_post_role_limits', $options['post_role_limits']);
	update_option('spaw_lpc_exclude_users',$_POST['exclude_users']);
    echo '<div class="updated"><p>' . __('Options saved') . '</p></div>';
  } else {//else retrieve data
    $options = array('post_role_limits' => get_option('spaw_lpc_post_role_limits'));
  }
  
  include 'misc/opt_page.php';
}
add_filter('user_has_cap', 'spaw_lpc_check_cap');
add_action('admin_menu', 'build_spaw_lpc_menu'); 
