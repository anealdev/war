<?php
require("Deck.php");

//Builds a new deck, divides into player1 and player2 piles
$deck = new Deck();
$deckId = $deck->getDeckId();

//Draw card from specified deck and return array
function drawCard($player, $deckId)
{
    $drawCardUrl   = "https://deckofcardsapi.com/api/deck/" . $deckId . "/pile/" . $player . "/draw/?count=1";
    $drawCardJson  = file_get_contents($drawCardUrl);
    $drawCardArray = json_decode($drawCardJson, true);
    return $drawCardArray;
}

//Compre card from each player and return winner
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

// Add cards in pool to round winner's hand
function addCards($player)
{
    global $pool;
    echo "\n";
    global $deckId;
    $buildUrl = "https://deckofcardsapi.com/api/deck/" . $deckId . "/pile/" . $player . "/add/?cards=" . $pool;
    file_get_contents($buildUrl);
    $pool       = "";
    $shuffleUrl = "https://deckofcardsapi.com/api/deck/" . $deckId . "/pile/" . $player . "/shuffle/";
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
    global $deckId;
    global $pool;

    // While same value cards are drawn, keep drawing 2 cards per hand and comparing one card per hand until winner
    while ($war == true) {
        $player1Draw1 = drawCard("player1", $deckId);
        $player1Draw2 = drawCard("player1", $deckId);
        $player2Draw1 = drawCard("player2", $deckId);
        $player2Draw2 = drawCard("player2", $deckId);

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
function checkRemaining($deckId, $roundWinner)
{
    $pile1Url             = "https://deckofcardsapi.com/api/deck/" . $deckId . "/pile/" . $roundWinner . "/list/";
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
    $player1Draw = drawCard("player1", $deckId);
    $player2Draw = drawCard("player2", $deckId);

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
    $keepPlaying = checkRemaining($deckId, $roundWinner);
}
if ($roundWinner == "player1") {
    echo "Game Over, Player 1 wins.";
} else {
    echo "Game Over, Player 2 wins.";
}

?>
