function createGame() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/admin/api/game/create');
    xhr.send(null);
}