<?php
/*
Plugin Name: Limit Post Creation
Plugin URI: http://www.spaw.it/somedirectory/spaw-limit-page-creation
Description: this plugin helps you to limit the number of posts/pages/custom post types each user can create on your site.
Version: 1.0
Author: SPAW - Servizi Portali & Applicazioni Web
Author URI: http://www.spaw.it/
*/
/*  Copyright 2011  SPAW - Servizi Portali & Applicazioni Web ( info@spaw.it)

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
$total_call = 0;
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
 
 //retrieve post role limit for the user
 $limit = false;
  foreach ($current_user->roles as $role) {      
    if (isset($options['post_role_limits'][$role])) {
      if ($options['post_role_limits'][$role] == -1) {
        return $capabilities;
      } else if ($options['post_role_limits'][$role] > $limit) {
        $limit = $options['post_role_limits'][$role];
        
      }
    }
  }
  
  //retrieve number of post for the current user
 $posts = get_posts(array('numberposts' => $limit, 'author' => $current_user->ID, 'post_status' => 'publish'));
 $nrPosts = count($posts);
 //test part
 
 //if limit is setted and reached deny access to user forn new post
 if($limit==true && ($nrPosts>$limit || $nrPosts==$limit)){
     wp_die( __('<div style="color:red;font-size:20px">Limit Post/Page Creation Warning</div>
<div style="font-weight:bold">You reached the maximum published allowed Post, return to
<a href="index.php">Dashboard</a> or contact your administrator</div>') );
   // unset($capabilities['edit_posts']);
     
     //return $capabilities;
 }
  
  //unset($capabilities['edit_posts']);
	//if($total_call == 1) echo "<p><span style='color:red;font-size:15px;'>You exceeded the maximum allowed Post/page creation!<br> Tomorrow you will be able to create new Posts</span><br><br><a href='index.php'>Go To Dashboard</a> or visit <a href=''>Limit Post Creation Per Day</a></p>";
    return $capabilities;
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