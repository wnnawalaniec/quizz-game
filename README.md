# Quizz-game

It's simple quizz game, where bunch of players answers set of question and one who has the most correct answers wins.

## About

**Disclaimer** I created it for my girlfriend on her Baby showe party. Time was crucial, and expectations weren't that high. It's far from being ideal but it was a little after hours projcet.

### Game rules
Game has 4 states:
 - new game
 - awaiting for players
 - started
 - finished

In the beginning, the game is not available as long as it is not created by host. After host creates the game, it switch to "new game" state.
From now other people may join by entering special link and becoming players after they give their names. Also now host can add questions to the game.

After game has been started by the host, no other person is allowed to join and no more questions can be added.

Questions are asked one by one in the order specified by host. Game moves to next question automatically when all players give theirs answer.
Always only one answer can be given. There are no multi answer questions. After all players answer last question, game ends.
At the end scores are summed up and the game picks the winner.

### Code
There are three main components of the game:
  - host panel - to manage the game (actually there is only start button, rest must be done via API)
  - player panel - to join and play the game
  - game panel - to displaying current game state: screen with join link, current question and score board

To add questions REST API must be used. Rest of the game is based on forms.
Admin panel is hidden and to reach it user must be authenticated. Base HTTP Authentication was choosen as simplest solution.
Game is based on session, so loosing your sesion will unable you to continue the game. **Acctually game is constructed in such a way that if one player looses his/her session game cannot be continued by anyone as it is expecting all players to answet their questions.**
Although I have very little time, I decided to use DDD way of writing with separation into three layers. It is my default way of thinking about applications, and my default way of creating them. I feel very comfrotable with that separation of responsibilities.
Domain is mostly tested where the rest of code is not.

One interesting part of the code is a little state machine I created with the new enums in PHP8, you can check it out at [Domain\Game\State](https://github.com/wnnawalaniec/quizz-game/blob/master/src/Domain/Game/State.php).

----

I used some graphic from the Internet to make this game a little more nice to eye.
Here are links:
 - [/static/logo.svg](https://www.svgrepo.com/svg/165164/pacifier)
 - [/static/sad.svg](https://commons.wikimedia.org/wiki/File:Emojione_BW_2639.svg)
 - [/static/cup.svg](https://www.onlinewebfonts.com/icon/530687)
