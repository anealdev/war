<?php
class Deck
{
    public function getDeckId()
    {
        $url        = "https://deckofcardsapi.com/api/deck/new/shuffle/?deck_count=1";
        $cardsJson  = file_get_contents($url); //get contents of url, makes the API call
        $cardsArray = json_decode($cardsJson, true); //encodes the json into array instead of object
        //$deckId = "23wlz18d5zj8"; //work off of same deck
        $deckId     = $cardsArray['deck_id'];

        //build player1 hand
        $dealCardUrl   = "https://deckofcardsapi.com/api/deck/" . $deckId . "/draw/?count=26";
        $dealCardJson  = file_get_contents($dealCardUrl); // put the contents of the file into a variable
        $dealCardArray = json_decode($dealCardJson, true); // decodes json
        $buildUrl      = "https://deckofcardsapi.com/api/deck/" . $deckId . "/pile/player1/add/?cards=";

        for ($i = 0; $i < 26; $i++) {
            $cardCode = $dealCardArray["cards"][$i]["code"]; //code of card just drawn
            $buildUrl .= $cardCode . ",";
        }

        $playerOne = file_get_contents($buildUrl);

        //build player2 hand
        $dealCardUrl   = "https://deckofcardsapi.com/api/deck/" . $deckId . "/draw/?count=26";
        $dealCardJson  = file_get_contents($dealCardUrl); // put the contents of the file into a variable
        $dealCardArray = json_decode($dealCardJson, true); // decodes json
        $buildUrl      = "https://deckofcardsapi.com/api/deck/" . $deckId . "/pile/player2/add/?cards=";

        for ($j = 0; $j < 26; $j++) {
            $cardCode = $dealCardArray["cards"][$j]["code"];
            $buildUrl .= $cardCode . ",";
        }

        $player2 = file_get_contents($buildUrl);


        return $deckId;
    }
}
?>
