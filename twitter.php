<?php

if ( !defined( 'ABSPATH' ) ) exit;

//require_once dirname( __FILE__ ) . '/twitter.php';

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
  register_setting( 'ti_twitter_vars', wp_filter_nohtml_kses('consumer_key') );
  register_setting( 'ti_twitter_vars', wp_filter_nohtml_kses('consumer_secret') );
  register_setting( 'ti_twitter_vars', wp_filter_nohtml_kses('access_token') );
  register_setting( 'ti_twitter_vars', wp_filter_nohtml_kses('access_token_secret') );
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
<?php } 

 // Sanitize and validate input. Accepts an array, return a sanitized array.
function ti_twitter_options_validate($input) {
  
  $input['access_token'] =  wp_filter_nohtml_kses($input['access_token']);
  $input['access_token_secret'] =  wp_filter_nohtml_kses($input['access_token_secret']);
  $input['consumer_key'] =  wp_filter_nohtml_kses($input['consumer_key']);
  $input['consumer_secret'] =  wp_filter_nohtml_kses($input['consumer_secret']);
  
  return $input;
}


// TimeStamp
   function timeago($date) {
    $dateOne = new DateTime($date);
    $dateTwo = new DateTime(date("c"));
    $interval = $dateOne->diff($dateTwo);
    if($interval->y != 0){
      $ts= "About ".$interval->y. " years ago";
    }elseif($interval->m != 0){
      $ts= "About ".$interval->m. " months ago";
    }elseif($interval->d != 0){
      $ts= "About ".$interval->d. " days ago";
    }elseif($interval->h != 0){
      $ts= "About ".$interval->h. " hours ago";
    }elseif($interval->i != 0){
      $ts= "About ".$interval->i. " min ago";
    }elseif($interval->s != 0){
      $ts= $interval->s. " sec ago";
    }
    return $ts;
  }

function parseTweet($ret) {
    $ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
    $ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
    $ret = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $ret); // Usernames
    $ret = preg_replace("/#(\w+)/", "<a href=\"http://twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $ret); // Hash Tags
    return $ret;
}

function buildBaseString($baseURI, $method, $params) { 
      $r = array(); ksort($params); 
        foreach($params as $key=>$value){ 
          $r[] = "$key=" . rawurlencode($value); } 
        return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r)); 
      }


    function buildAuthorizationHeader($oauth) { 
      $r = 'Authorization: OAuth '; 
      $values = array(); 
      foreach($oauth as $key=>$value) 
        $values[] = "$key=\"" . rawurlencode($value) . "\""; $r .= implode(', ', $values); return $r; 
    }

  class Get_Tweets {

        
    private $count;
    private $trim_user = true;
    private $time = 300; // 5 min
    private $user;
    public function __construct($username = "tweepsum", $tcount = 10) {
      $username = str_replace(array("https://twitter.com/","http://twitter.com/"), "", $username);
      $this->user = str_replace(array("https://twitter.com/","http://twitter.com/"), "", $username);
      $this->count = $tcount;
    }


    private function fetch_url($username, $tcount) {
     
      //$url = "http://api.twitter.com/1.1/statuses/user_timeline/{$username}.json?count=".$this->count."&trim_user=".$this->trim_user;
      $url = "https://api.twitter.com/1.1/statuses/user_timeline/{$username}.json";
     
        $oauth_access_token = get_option('access_token');
        $oauth_access_token_secret = get_option('access_token_secret');
        $consumer_key = get_option('consumer_key'); 
        $consumer_secret = get_option('consumer_secret');
        

      $oauth = array( 'oauth_consumer_key' => $consumer_key,
                        'oauth_nonce' => time(),
                        'oauth_signature_method' => 'HMAC-SHA1',
                        'oauth_token' => $oauth_access_token,
                        'oauth_timestamp' => time(),
                        'oauth_version' => '1.0');
                        //'screen_name'=>'tweepsum');

     
      $base_info = buildBaseString($url, 'GET', $oauth);
      $composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
      $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
      $oauth['oauth_signature'] = $oauth_signature;   

      


      $header = array(buildAuthorizationHeader($oauth), 'Expect:'); 
      $options = array( CURLOPT_HTTPHEADER => $header, 
      //CURLOPT_POSTFIELDS => $postfields, 
        CURLOPT_HEADER => false, 
        CURLOPT_URL => $url, 
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_SSL_VERIFYPEER => false);


        $curl = curl_init(); 
        curl_setopt_array($curl, $options); 
        $json = curl_exec($curl); 
        $tweets = json_decode($json);
  
        curl_close($curl);

      if(is_object($tweets)){
        return false;
      }else{
        return $tweets;
      }
    }


    private function save_cache($data) {
      $handle = fopen(dirname( __FILE__ )."../../cache/".$this->user."-twitter-cache.json", 'w');
      fwrite($handle, json_encode($data));
      fclose($handle);

      $handle = fopen(dirname( __FILE__ )."../../cache/".$this->user."-twitter-last-cache.txt", 'w');
      fwrite($handle, date("c"));
      fclose($handle); 
    }


    private function second(){
      if (!file_exists(dirname( __FILE__ )."../../cache/".$this->user."-twitter-last-cache.txt")) {

        $handle = fopen(dirname( __FILE__ )."../../cache/".$this->user."-twitter-last-cache.txt", 'w');
        fwrite($handle, "2000-01-01T12:12:12+00:00");
        fclose($handle); 
      }
      $prevDate = file_get_contents(dirname( __FILE__ )."../../cache/".$this->user."-twitter-last-cache.txt");
      $dateOne = new DateTime($prevDate);
      $dateTwo = new DateTime(date("c"));
      $diff = $dateTwo->format("U") - $dateOne->format("U");

      return $diff;
    }


    private function get_data(){
      $tweets = json_decode(file_get_contents(dirname( __FILE__ )."../../cache/".$this->user."-twitter-cache.json"));
      return $tweets;
    }


    public function data(){
	if (strlen($this->user) < 1 ) $this->user="tweepsum";

      if($this->second() < $this->time){

        $tweets = $this->get_data();
      }else{

        $tweets = $this->fetch_url($this->user,$this->count);
        if($tweets == false){
          $tweets = $this->get_data();
        }else{

          $this->save_cache($tweets);
        }
      }
      return $tweets;
    }
  }
?>