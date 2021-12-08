function addAnswer() {
    var answerNo = countAnswers() + 1;
    var element = `
    <div className="input-group mb-3">
        <span className="input-group-text" id="basic-addon1">${answerNo}</span>
        <input name="answer-${answerNo-1}" type="text" className="form-control" placeholder="Odpowiedź" aria-label="Odpowiedź"
               aria-describedby="basic-addon1" required>
       <input class="form-check-input" type="radio" name="is_correct" id="is_correct-${answerNo-1}">
    </div>
    `
    var div = document.createElement('div');
    div.innerHTML = element.trim();
    document.getElementById("answers").appendChild(div.firstChild);
}

function countAnswers() {
    return document.getElementById('answers').childElementCount;
}