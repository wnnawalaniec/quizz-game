Game has 4 states:
 - new game
 - awaiting for players
 - started
 - finished

In the beginning, the game is not available as long as it is not created by host.
After host creates the game it shifts to "new game" state.
From now other people may join by entering special link becoming players.
Host can start the game at any point. After game has been started, no other
person is allowed to join it.
Game is consist of set of questions. Questions are asked one by one in the
order specified by host. Game moves to next question when all players
give theirs answer. Host can always force it.
At the end scores are summed up and the game picks its winner.

There are three main components of the game:
  - host panel - to manage the game
  - player panel - to play the game
  - game panel - it's a component displaying current game state eg. screen with join link, current question etc.