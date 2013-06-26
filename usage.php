<ul>
<?php
     // Crate a new instance
      $get_tweets = new Get_Tweets("twitter-username");
      // Get Data
      $tweets = $get_tweets->data();
      // Print the last tweet text
      for ($i=0; $i < 3; $i++) { 
        echo "<li>".parseTweet($tweets[$i]->text)."<br/><span>".timeago($tweets[$i]->created_at)."</span></li>";

      }
    ?>
</ul>
