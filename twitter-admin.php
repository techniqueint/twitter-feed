<?php

require_once dirname( __FILE__ ) . '/twitter.php';

// create custom plugin settings menu
add_action('admin_menu', 'ti_twitter_menu');

function ti_twitter_menu() {

  //create new top-level menu
 
  add_options_page('Twitter Settings', 'Twitter Settings', 'manage_options', 'plugin', 'ti_twitter_setting_page');
  //call register settings function
  add_action( 'admin_init', 'register_mysettings' );
}


function register_mysettings() {
  //register our settings
  register_setting( 'ti_twitter_vars', 'consumer_key' );
  register_setting( 'ti_twitter_vars', 'consumer_secret' );
  register_setting( 'ti_twitter_vars', 'access_token' );
  register_setting( 'ti_twitter_vars', 'access_token_secret' );
}

function ti_twitter_setting_page() {
?>
<div class="wrap">
<h2>Twitter settings</h2>
<p>You will need to create an app in your twitter profile</p>

<form method="post" action="options.php">
    <?php settings_fields( 'ti_twitter_vars' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Consumer key</th>
        <td><input type="text" name="consumer_key" value="<?php echo get_option('consumer_key'); ?>" size="80" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Consumer secret</th>
        <td><input type="text" name="consumer_secret" value="<?php echo get_option('consumer_secret'); ?>" size="80" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Access token</th>
        <td><input type="text" name="access_token" value="<?php echo get_option('access_token'); ?>" size="80" /></td>
        </tr>
         <tr valign="top">
        <th scope="row">Access token secret</th>
        <td><input type="text" name="access_token_secret" value="<?php echo get_option('access_token_secret'); ?>" size="80" /></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php 

} ?>