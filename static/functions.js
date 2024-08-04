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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('questionForm');
    const addOptionButton = document.getElementById('addOption');
    const optionsContainer = document.getElementById('options');
    const messageDiv = document.getElementById('message');
    const questionsList = document.getElementById('questionsList');

    if (!messageDiv || !questionsList || !form || !addOptionButton || !optionsContainer) {
        console.error('One or more required elements are missing from the DOM.');
        return;
    }

    async function fetchQuestions() {
        try {
            const response = await fetch('/admin/api/questions');
            if (response.ok) {
                const questions = await response.json();
                renderQuestions(questions);
            } else {
                throw new Error('Failed to fetch questions');
            }
        } catch (error) {
            messageDiv.textContent = `Error: ${error.message}`;
            messageDiv.classList.add('text-danger');
        }
    }

    function renderQuestions(questions) {
        questionsList.innerHTML = ''; // Clear the list before rendering
        questions.forEach(question => {
            const questionElement = document.createElement('div');
            questionElement.classList.add('card', 'mb-3');
            questionElement.innerHTML = `
                <div class="card-body">
                    <h5 class="card-title">${question.question}</h5>
                    <ul class="list-group list-group-flush">
                        ${question.answers.map(option =>
                `<li class="list-group-item ${option.is_correct ? 'list-group-item-success' : ''}">
                                ${option.text}
                            </li>`
            ).join('')}
                    </ul>
                    <button class="btn btn-danger mt-3" data-id="${question.id}">Remove</button>
                </div>
            `;
            questionsList.appendChild(questionElement);
        });

        // Attach event listeners to remove buttons
        document.querySelectorAll('.btn-danger').forEach(button => {
            button.addEventListener('click', async function() {
                const questionId = this.getAttribute('data-id');
                await deleteQuestion(questionId);
            });
        });
    }

    async function deleteQuestion(id) {
        try {
            const response = await fetch(`/admin/game/questions/${id}`, {
                method: 'DELETE'
            });
            if (response.ok) {
                messageDiv.textContent = 'Question removed successfully.';
                messageDiv.classList.add('text-success');
                fetchQuestions(); // Refresh the list
            } else {
                throw new Error('Failed to delete question');
            }
        } catch (error) {
            messageDiv.textContent = `Error: ${error.message}`;
            messageDiv.classList.add('text-danger');
        }
    }

    addOptionButton.addEventListener('click', function() {
        const optionCount = optionsContainer.getElementsByClassName('option').length;
        const newOptionDiv = document.createElement('div');
        newOptionDiv.classList.add('option', 'mb-3');
        newOptionDiv.innerHTML = `
            <input type="text" class="form-control" name="option" placeholder="Option ${optionCount + 2}" required>
            <input type="checkbox" name="correctOption" class="form-check-input ms-2"> Correct
        `;
        optionsContainer.appendChild(newOptionDiv);
    });

    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        const questionText = document.getElementById('question').value;
        const options = Array.from(document.querySelectorAll('.option')).map(optionDiv => {
            const optionText = optionDiv.querySelector('input[name="option"]').value;
            const isCorrect = optionDiv.querySelector('input[name="correctOption"]').checked;
            return { text: optionText, correct: isCorrect };
        });

        if (!questionText || options.length < 2) {
            messageDiv.textContent = 'Please enter a question and at least two options.';
            messageDiv.classList.add('text-danger');
            return;
        }

        try {
            const response = await fetch('/admin/api/questions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ question: questionText, options })
            });

            if (response.ok) {
                const result = await response.json();
                messageDiv.textContent = `Question added successfully! ID: ${result.id}`;
                messageDiv.classList.add('text-success');
                form.reset();
                optionsContainer.innerHTML = '<div class="option mb-3"><input type="text" class="form-control" name="option" placeholder="Option 1" required><input type="checkbox" name="correctOption" class="form-check-input ms-2"> Correct</div>';
                fetchQuestions(); // Refresh the list
            } else {
                throw new Error('Failed to add question');
            }
        } catch (error) {
            messageDiv.textContent = `Error: ${error.message}`;
            messageDiv.classList.add('text-danger');
        }
    });

    // Initial fetch of questions
    fetchQuestions();
});

