<?php

function loadFeed($feed) {
  $curl = curl_init();
  curl_setopt_array($curl, Array(
    CURLOPT_URL              => $feed,
    CURLOPT_USERAGENT        => 'spider',
    CURLOPT_TIMEOUT          => 120,
    CURLOPT_CONNECTTIMEOUT   => 30,
    CURLOPT_RETURNTRANSFER   => TRUE,
    CURLOPT_ENCODING         => 'UTF-8'
  ));

  $data = curl_exec($curl);
  curl_close($curl);
  return simplexml_load_string($data,'SimpleXMLElement',LIBXML_NOCDATA);
}

function displayLatestGames($num) {
  $bggData = loadFeed('https://www.boardgamegeek.com/xmlapi2/plays?username=harrellca&subtype=boardgame&excludesubtype=boardgameexpansion');
  $i = 1;
  foreach($bggData as $cData) {
    print("<li><a href=\"http://boardgamegeek.com/boardgame/".$cData->item->attributes()->objectid."/\">".$cData->item->attributes()->name."</a></li>");
    if($i++ == $num) {
      break;
    }
  }
}

function displayLatestPictures($num) {
  $zenData = loadFeed('http://photos.harrell.ca/index.php?rss');
  $i = 1;
  foreach($zenData->channel->item as $item) {
    // If I don't just want the img, consider using:
    //  <a(.*?)<\/a>
    preg_match_all('/<img[^>]+>/i',$item->description,$result);
    print($result[0][0]);
    if($i++ == $num) {
      break;
    }
  }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Harrell.CA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
  </head>
  <body>
    <div class="sidebar">
      <div class="rotate"><div class="inner">harrell<span class="postfix">.ca</span></div></div>
      <div><i class="fa fa-code" aria-hidden="true"></i> with <i class="fa fa-beer" aria-hidden="true"></i> by <a href="//jamie.harrell.ca/">Jamie</a></div>
    </div>
    <div class="content">
      <h2>Welcome</h2>
      <p>There'll be more information here as time goes on, but for now, this page is simply a landing page to serve as a directory reference for the websites housed here.</p>
      <div class="tab">
        <div>
          <h4><i class="fa fa-fw fa-external-link" aria-hidden="true"></i> Hosted Sites</h4>
          <ul>
            <li> <a href="//games.harrell.ca/">Our Boardgaming Site</a></li>
            <li> <a href="//jamie.harrell.ca/">Jamie's Blog</a></li>
            <li> <a href="//photos.harrell.ca/">Travel Photos</a></li>
            <li> <a href="//webmail.harrell.ca/">Webmail</a></li>
          </ul>
        </div>
        <div>
          <h4><i class="fa fa-fw fa-lg fa-gamepad" aria-hidden="true"></i> Games we've recently played</h4>
          <ul>
            <?php displayLatestGames(5); ?>
          </ul>
        </div>
      </div>
      <div class="tab">
        <div>
          <h4><i class="fa fa-fw fa-search" aria-hidden="true"></i> Find us Elsewhere</h4>
          <ul>
            <li> <a href="//boardgamegeek.com/user/harrellca">BoardGameGeek</a></li>
            <li> <a href="//www.redditgifts.com/profiles/view/peoii/#/">RedditGifts</a></li>
          </ul>
        </div>
        <div>
          <h4><i class="fa fa-fw fa-camera" aria-hidden="true"></i> Recent Photos</h4>
          <?php displayLatestPictures(4); ?>
        </div>
      </div>
    </div>
    <noscript id="deferred-styles">
      <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,800,700,600,300|Montserrat:400,700' rel='stylesheet' type='text/css' async />
      <link href='css/font-awesome.min.css' rel='stylesheet' type='text/css' />
      <link href='css/style.css' rel='stylesheet' type='text/css' />
    </noscript>
    <script>
      var loadDeferredStyles = function() {
        var addStylesNode = document.getElementById("deferred-styles");
        var replacement = document.createElement("div");
        replacement.innerHTML = addStylesNode.textContent;
        document.body.appendChild(replacement)
        addStylesNode.parentElement.removeChild(addStylesNode);
      };
      var raf = requestAnimationFrame || mozRequestAnimationFrame ||
          webkitRequestAnimationFrame || msRequestAnimationFrame;
      if (raf) raf(function() { window.setTimeout(loadDeferredStyles, 0); });
      else window.addEventListener('load', loadDeferredStyles);
    </script>
  </body>
</html>
