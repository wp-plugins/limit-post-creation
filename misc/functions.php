<?php
/**
 * get the current user role
 * @param string $roleName the role name ( administrator,editor,author....)
 * @param array $roleUser the array retrieved from wp_get_current_user() function , like $current_user->roles
 */
function spaw_lpc_get_role($roleName,$roleUser) {    
    $key_role=array_search($roleName, $roleUser);  
  $role=$roleUser[$key_role];
  if ($role==$roleName)  return true;
  else return false;
}
/**
 * Get the type of page requested
 * @return string $page_type the type of page requested, if for new post or new page 
 */
function spaw_lpc_get_page_type() {
     if (isset($_GET['post_type'])){
			$page_type = $_GET['post_type'];
		}
		else
		{
			$page_type = 'post';
		}
                return $page_type;
}
/**
 * Check if current user is a apsecial one that doesn't have restrictions
 * @return bool $special true if the user is special 
 */
function spaw_lpc_check_special_user($options,$id) {
    if(!empty ($options)){
		$special_users = explode(",",$options);
		if(array_search($id,$special_users) !== false) return true;
                else return false;
    }
}
/**
 * SELECT count(*) FROM 
`wp_posts` p join `wp_usermeta` u
WHERE p.post_author = u.user_id
	AND  u.meta_key =  'wp_capabilities'
	AND  u.meta_value LIKE  '%administrator%'
	AND p.post_status = 'publish'
	AND p.post_type = 'page'
 */

?>