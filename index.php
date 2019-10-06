<?php
$url         = "https://deckofcardsapi.com/api/deck/new/shuffle/?deck_count=1";
$cards_json  = file_get_contents($url); //get contents of url, makes the API call
$cards_array = json_decode($cards_json, true); //encodes the json into array instead of object
//$deck_id = "23wlz18d5zj8"; //work off of same deck
$deck_id     = $cards_array['deck_id'];

//build player1 hand
$deal_card_url   = "https://deckofcardsapi.com/api/deck/" . $deck_id . "/draw/?count=26";
$deal_card_json  = file_get_contents($deal_card_url); // put the contents of the file into a variable
$deal_card_array = json_decode($deal_card_json, true); // decodes json
$build_url       = "https://deckofcardsapi.com/api/deck/" . $deck_id . "/pile/player1/add/?cards=";

for ($i = 0; $i < 26; $i++) {
    $card_code = $deal_card_array["cards"][$i]["code"]; //code of card just drawn
    $build_url .= $card_code . ",";
}

$playerOne = file_get_contents($build_url);

//build player2 hand
$deal_card_url   = "https://deckofcardsapi.com/api/deck/" . $deck_id . "/draw/?count=26";
$deal_card_json  = file_get_contents($deal_card_url); // put the contents of the file into a variable
$deal_card_array = json_decode($deal_card_json, true); // decodes json
$build_url       = "https://deckofcardsapi.com/api/deck/" . $deck_id . "/pile/player2/add/?cards=";

for ($j = 0; $j < 26; $j++) {
    $card_code = $deal_card_array["cards"][$j]["code"];
    $build_url .= $card_code . ",";
}

$player2 = file_get_contents($build_url);


// so far this builds a new deck, divides and makes  player1 and player2 piles

function drawCard($player, $deck_id) //draw card from specified deck and return array
{
    $drawCardUrl   = "https://deckofcardsapi.com/api/deck/" . $deck_id . "/pile/" . $player . "/draw/?count=1";
    //https://deckofcardsapi.com/api/deck/px9m71ypb6ow/pile/player1/draw/?count=2
    $drawCardJson  = file_get_contents($drawCardUrl);
    $drawCardArray = json_decode($drawCardJson, true);
    //echo $drawCardJson;
    //echo "|||||||||||||||||||||||||";
    //echo $drawCardArray["cards"][0]["value"];
    return $drawCardArray;
}

function compareCards($card1, $card2)
{
    echo "Player 1 draws a " . ucfirst(strtolower($card1["cards"][0]["value"])) . " of " . ucfirst(strtolower($card1["cards"][0]["suit"]));
    echo "\n";
    echo "Player 2 draws a " . ucfirst(strtolower($card2["cards"][0]["value"])) . " of " . ucfirst(strtolower($card2["cards"][0]["suit"]));;
    echo "\n";
    if ($card1["cards"][0]["value"] == $card2["cards"][0]["value"]) {
        return "War";
    } elseif ($card1["cards"][0]["value"] == "ACE") {
        return "player1";
    } elseif ($card2["cards"][0]["value"] == "ACE") {
        return "player2";
    } elseif ($card1["cards"][0]["value"] == "KING" && $card2["cards"][0]["value"] != "ACE") {
        return "player1";
    } elseif ($card2["cards"][0]["value"] == "KING" && $card1["cards"][0]["value"] != "ACE") {
        return "player2";
    } elseif ($card1["cards"][0]["value"] == "QUEEN" && $card2["cards"][0]["value"] != "ACE" && $card2["cards"][0]["value"] != "KING") {
        return "player1";
    } elseif ($card2["cards"][0]["value"] == "QUEEN" && $card1["cards"][0]["value"] != "ACE" && $card1["cards"][0]["value"] != "KING") {
        return "player2";
    } elseif ($card1["cards"][0]["value"] == "JACK" && $card2["cards"][0]["value"] != "ACE" && $card2["cards"][0]["value"] != "KING" && $card2["cards"][0]["value"] != "QUEEN") {
        return "player1";
    } elseif ($card2["cards"][0]["value"] == "JACK" && $card1["cards"][0]["value"] != "ACE" && $card1["cards"][0]["value"] != "KING" && $card1["cards"][0]["value"] != "QUEEN") {
        return "player2";
    } elseif ($card1["cards"][0]["value"] > $card2["cards"][0]["value"]) {
        return "player1";
    } else {
        return "player2";
    }

}

// add cards in pool to round winner's hand
function addCards($player)
{
    global $pool;
    echo "\n";
    global $deck_id;
    $build_url = "https://deckofcardsapi.com/api/deck/" . $deck_id . "/pile/" . $player . "/add/?cards=" . $pool;
    file_get_contents($build_url);
    $pool       = "";
    $shuffleUrl = "https://deckofcardsapi.com/api/deck/" . $deck_id . "/pile/" . $player . "/shuffle/";
    file_get_contents($shuffleUrl);

}

// Draws 2 cards per call and adds to Pool
// Adds pool to winner's hand
function war()
{
    echo "War declared";
    echo "\n";
    // draw 2 cards per player
    // compare 1 card from each player from new draw
    $war = true;
    global $deck_id;
    global $pool;

    // While same value cards are drawn, keep drawing 2 cards per hand and comparing one card per hand until winner
    while ($war == true) {
        $player1Draw1 = drawCard("player1", $deck_id);
        $player1Draw2 = drawCard("player1", $deck_id);
        $player2Draw1 = drawCard("player2", $deck_id);
        $player2Draw2 = drawCard("player2", $deck_id);

        // add all drawn cards to pool
        $pool .= $player1Draw1["cards"][0]["code"] . ",";
        $pool .= $player1Draw2["cards"][0]["code"] . ",";
        $pool .= $player2Draw1["cards"][0]["code"] . ",";
        $pool .= $player2Draw2["cards"][0]["code"] . ",";

        // compare one card from each player draw and decide winner or continue war
        $roundWinner = compareCards($player1Draw1, $player2Draw1);
        if ($roundWinner == "player1") {
            echo "Player 1 wins the war";
            echo "\n";
            addCards("player1");
            $pool = "";
            $war  = false;
        } elseif ($roundWinner == "player2") {
            echo "Player 2 wins the war";
            echo "\n";
            addCards("player2");
            $pool = "";
            $war  = false;
        } elseif ($roundWinner = "War") {
            echo "Still at war";
            echo "\n";
        }
    }
}

// Check if either player has 0 cards in hand
function checkRemaining($deck_id, $roundWinner)
{
    $pile1Url             = "https://deckofcardsapi.com/api/deck/" . $deck_id . "/pile/" . $roundWinner . "/list/";
    $pile1Json            = file_get_contents($pile1Url);
    $pile1Array           = json_decode($pile1Json, true);
    $player1PileRemaining = $pile1Array["piles"]["player2"]["remaining"];
    $player2PileRemaining = $pile1Array["piles"]["player1"]["remaining"];
    // print how many cards remaining for each player hand
    /*echo "Player1 cards remaining: " . $player2PileRemaining;
    echo "\n";
    echo "Player2 cards remaining: " . $player1PileRemaining;
    echo "\n"; */
    if ($player1PileRemaining <= 0 || $player2PileRemaining <= 0) {
        return False;
    } else {
        return True;
    }
}

// While each player still has cards in their hand, keep looping through the game
$keepPlaying = True;
while ($keepPlaying == True) {

    $pool        = "";
    $player1Draw = drawCard("player1", $deck_id);
    $player2Draw = drawCard("player2", $deck_id);

    $roundWinner = compareCards($player1Draw, $player2Draw);

    $pool .= $player1Draw["cards"][0]["code"] . ",";
    $pool .= $player2Draw["cards"][0]["code"] . ",";

    if ($roundWinner == "player1") {
        echo "Player 1 wins the round ";
        echo "\n";
        addCards("player1");
    } elseif ($roundWinner == "player2") {
        echo "Player 2 wins the round";
        echo "\n";
        addCards("player2");
    } elseif ($roundWinner = "War") {

        War();
    }
    $keepPlaying = checkRemaining($deck_id, $roundWinner);
}
if ($roundWinner == "player1") {
    echo "Game Over, Player 1 wins.";
} else {
    echo "Game Over, Player 2 wins.";
}
?>
