<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
<h2>Glue LaBS Limit Post Creation</h2>
<form name="form" action="" method="post">
  <p>Enter the number of post each respective type of user can create. You can set: post per role, special users by ID and time period. 
      Period is checked from now to time choosed; so you can set Week for a time period from today to six days ago.  
      Enter <strong>-1</strong> for no limits. Note that Administrators have not limitations.</p>

<h3>Role Limitation</h3>
<span class="description">Enter the maximum number of post/page each Role can create.</span>
<table class="form-table">
    <thead>
        <tr>
            <th>Role</th>
            <th>Post</th>
            <th>Page</th>
            <th>Time</th>
        </tr>
    </thead>
    <tbody>
	<?php foreach (array_keys($wp_roles->roles) as $role) {
            if ($role == 'administrator') continue;//exclude administrator role?>
	<tr>
		<th><label><?php echo ucfirst($role) ?></label></th>
        <td><input name="post_role_limits[<?php echo $role ?>][post]" type="text" value="<?php echo $options['post_role_limits'][$role]['post'] ?>" class="samll-text"  /></td>
        <td><input name="post_role_limits[<?php echo $role ?>][page]" type="text" value="<?php echo $options['post_role_limits'][$role]['page'] ?>" class="samll-text"  /></td>
        <td><select name="post_role_limits[<?php echo $role ?>][time]">
                <?php
                $timeLimit = $options['post_role_limits'][$role]['time'];
                switch ($timeLimit) {
                    case 'day':
                        $htmlTime = '<option value="ever">Forever</option>
                                       <option value="day" selected="selected">Per Day</option>  
                                      <option value="week">Per Week</option>
                                       <option value="month">Per Month</option>    ';
                        break;
                    case 'week':
                        $htmlTime = '<option value="ever">Forever</option>
                                      <option value="day" >Per Day</option>  
                                      <option value="week" selected="selected">Per Week</option>
                                       <option value="month">Per Month</option>    ';
                        break;
                     case 'month':
                        $htmlTime = '<option value="ever">Forver</option>
                                        <option value="day" >Per Day</option>  
                                      <option value="week" >Per Week</option>
                                       <option value="month" selected="selected">Per Month</option>    ';
                        break;

                    default:
                        $htmlTime = '<option value="ever" selected="selected">Forever</option>
                                      <option value="day" >Per Day</option>  
                                      <option value="week" >Per Week</option>
                                       <option value="month">Per Month</option>    ';
                        break;
                }
                echo $htmlTime;                
                ?>
                            
            </select></td>
        </tr>
    <?php } ?>
        </tbody>
</table>

<h3>Special Users</h3>
	<p>You may disable limits for some users just entering their IDs below.Separate each user ID with comma.</p>

<table class="form-table">
	<tr>
		<th><label >Special Users IDs</label></th>
		<td> <input name="exclude_users" type="text" value="<?php echo get_option('spaw_lpc_exclude_users');?>" class="regular-text code" /></td>
	</tr>
</table>
<p class="submit">
	<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
  </form>
</div>

