function refreshForEveryMS(everyMs) {
    let date = new Date();
    let ms = date.getSeconds() * 1000 + date.getMilliseconds();
    let start = everyMs - (ms % everyMs);

    setTimeout(function(){
        refresh();
        window.setInterval(refresh, everyMs);
    }, start);

    function refresh() {
        window.location.reload();
    }
}