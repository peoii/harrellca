<?php
class BGGHandler {
  private $baseURL;
  private $baseUser;
  private $baseType;

  private $bggData;
  private $bggPlays;

  private function simplexml_merge(SimpleXMLElement &$xml1, SimpleXMLElement $xml2) {
    // convert SimpleXML objects into DOM ones
    $dom1 = new DomDocument();
    $dom2 = new DomDocument();
    $dom1->loadXML($xml1->asXML());
    $dom2->loadXML($xml2->asXML());

    // pull all child elements of second XML
    $xpath = new domXPath($dom2);
    $xpathQuery = $xpath->query('/*/*');
    for ($i = 0; $i < $xpathQuery->length; $i++) {
        // and pump them into first one
        $dom1->documentElement->appendChild(
            $dom1->importNode($xpathQuery->item($i), true));
    }
    $xml1 = simplexml_import_dom($dom1);
  }

  private function loadFeed($feed) {
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

  private function loadQuery($type) {
    if($type == 'user') {
      $cURL = $this->baseURL . $type . "?name=" . $this->baseUser;
      $tData = $this->loadFeed($cURL);
    } else {
      $cURL = $this->baseURL . $type . "?username=" . $this->baseUser . "&subtype=boardgame&excludesubtype=boardgameexpansion&own=1&stats=1";
      $tData = $this->loadFeed($cURL);
      if($type == 'plays') {
        if($tData->attributes()->total > 100) {
          $num = ceil($tData->attributes()->total/100);
          for($i=2; $i <= $num; $i++) {
            $cURL = $this->baseURL . $type . "?username=" . $this->baseUser . "&subtype=boardgame&excludesubtype=boardgameexpansion&own=1&stats=1&page=".$i;
            $pData = $this->loadFeed($cURL);
            $this->simplexml_merge($tData, $pData);
          }
        }
      }
    }
    return $tData;
  }

  public function __construct($user,$type = "collection") {
    $this->baseUser = $user;
    $this->baseType = $type;
    $this->baseURL = 'https://www.boardgamegeek.com/xmlapi2/';
    $this->bggData = $this->loadQuery($type);
    $this->bggPlays = $this->loadQuery('plays');
    //print_r($this->bggData);
  }

  public function getShameCount() {
    $i = 0;
    foreach($this->bggData as $cData) {
      if($cData->numplays == "0") {
        $i++;
      }
    }
    print($i);
  }

  public function getShameList($pre = "<li>",$post = "</li>") {
    foreach($this->bggData as $cData) {
      if($cData->numplays == "0") {
        print($pre."<a href=\"http://boardgamegeek.com/boardgame/".$cData->attributes()->objectid."/\">".$cData->name."</a>".$post);
      }
    }
  }

  public function getRecentlyPlayed($num = 10,$pre = "<li>",$post = "</li>") {
    $i = 1;
    foreach($this->bggPlays as $cData) {
      print("<li><a href=\"http://boardgamegeek.com/boardgame/".$cData->item->attributes()->objectid."/\">".$cData->item->attributes()->name."</a></li>");
      if($i++ == $num) {
        break;
      }
    }
  }

  public function getTopRated($threshold = 9,$pre = "<li>",$post = "</li>") {
    foreach($this->bggData as $cData) {
      if($cData->stats->rating->attributes()->value >= $threshold) {
        print($pre."<a href=\"http://boardgamegeek.com/boardgame/".$cData->attributes()->objectid."/\">".$cData->name." (".$cData->stats->rating->attributes()->value.")</a>".$post);
      }
    }
  }

  public function getUser($pre = "<tr><td>", $post = "</td></tr>", $seperator = "</td><td>") {
   $tData = $this->loadQuery('user');
   print($pre."Username:".$seperator."<a href=\"http://boardgamegeek.com/user/".$tData->attributes()->name."/\">".$tData->attributes()->name."</a>".$post);
   print($pre."Member Since:".$seperator.$tData->yearregistered->attributes()->value.$post);
   print($pre."Location:".$seperator.$tData->stateorprovince->attributes()->value.", ".$tData->country->attributes()->value.$post);
   print($pre."Collection Size:".$seperator.count($this->bggData).$post);
   print($pre."Recorded Game Plays:".$seperator.$this->bggPlays->attributes()->total.$post);
   //print($pre.":"$seperator.$tData->attributes()->value.$post);
  }
}
?>
