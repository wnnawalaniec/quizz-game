function refreshForEveryMS(everyMs) {
    let date = new Date();
    let ms = date.getSeconds() * 1000 + date.getMilliseconds();
    let start = everyMs - (ms % everyMs);

    setTimeout(function () {
        refresh();
        window.setInterval(refresh, everyMs);
    }, start);

    function refresh() {
        window.location.reload();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('questionForm');
    const optionsContainer = document.getElementById('options');
    const messageDiv = document.getElementById('message');
    const questionsList = document.getElementById('questionsList');

    if (!messageDiv || !questionsList || !form || !optionsContainer) {
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
                throw new Error('Nie udało się pobrać pytań');
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
            questionElement.classList.add('mb-3', 'border', 'rounded');
            questionElement.innerHTML = `
                <div class="accordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${question.id}" aria-expanded="true" aria-controls="collapse-${question.id}">
                             ${question.text}
                            </button>
                        </h2>
                    </div>
                    <div id="collapse-${question.id}" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            ${question.answers.map(option =>
                                `
                                    <div class="option d-flex mb-3 align-items-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input me-2" type="checkbox" role="switch" name="correctOption" disabled ${option.is_correct ? 'checked' : ''}>
                                        </div>
                                        <input type="text" class="form-control me-2" name="option" value="${option.text}" readonly>
                                    </div>
                                `
                            ).join('')}
                            <div class="d-flex justify-content-end align-items-end">
                                <button type="button" class="btn btn-outline-danger delete-btn" data-id="${question.id}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
            `
            questionsList.appendChild(questionElement);
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', async function () {
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
                showToast('Pytanie zostało usunięte', 'bg-success');
                fetchQuestions(); // Refresh the list
            } else {
                throw new Error('Nie udało się usunąć pytania');
            }
        } catch (error) {
            showToast('Nie udało się usunąć pytania', 'bg-danger');
        }
    }

    function addOptionDiv(isPlaceholder = false) {
        const optionCount = optionsContainer.getElementsByClassName('option').length;
        const newOptionDiv = document.createElement('div');
        newOptionDiv.classList.add('option', 'd-flex', 'mb-3', 'align-items-center');
        if (isPlaceholder) {
            newOptionDiv.classList.add('placeholder');
        }
        newOptionDiv.innerHTML = `
                <div class="form-check form-switch">
                  <input class="form-check-input me-2" type="checkbox" role="switch" name="correctOption">
                </div>
                <input type="text" class="form-control me-2" name="option" placeholder="Odpowiedź ${optionCount + 1}" ${isPlaceholder ? '' : 'required'}>
            `;
        if (!isPlaceholder) {
            newOptionDiv.innerHTML += `
                <button type="button" name="removeOption" class="btn btn-outline-danger" ${optionCount < 2 ? 'disabled' : ''}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                    </svg>
                </button>
            `;
        }
        optionsContainer.appendChild(newOptionDiv);
        attachInputListeners();
        return newOptionDiv;
    }

    function attachInputListeners() {
        const inputs = optionsContainer.querySelectorAll('input[type="text"]');
        inputs.forEach(input => {
            input.removeEventListener('input', handleInputChange);
            input.addEventListener('input', handleInputChange);
            input.removeEventListener('focus', handleFocus);
            input.addEventListener('focus', handleFocus);
            input.addEventListener('blur', handleBlur);
            input.addEventListener('blur', handleBlur);
        });
    }

    function handleInputChange(event) {
        const inputs = optionsContainer.querySelectorAll('input[type="text"]');
        const lastInput = inputs[inputs.length - 1];
        const input = event.target;
        const optionDiv = input.closest('.option');

        if (optionDiv && optionDiv.classList.contains('placeholder')) {
            optionDiv.remove();
            const newOption = addOptionDiv(false);
            const newInput = newOption.querySelector('input[type="text"]')
            newInput.value = input.value
            const newCheckbox = newOption.querySelector('input[role="switch"]')
            newCheckbox.checked = optionDiv.querySelector('input[role="switch"]').checked
            newInput.focus()
        }

        if (event.target === lastInput && lastInput.value.trim() !== "") {
            addOptionDiv(true);
        }
    }

    function handleFocus(event) {
        const input = event.target;
        const optionDiv = input.closest('.option');
        // if (optionDiv && optionDiv.classList.contains('placeholder')) {
        //     // optionDiv.classList.remove('placeholder');
        //     optionDiv.remove();
        //     addOptionDiv(false).querySelector('input[type="text"]').focus()
        // }
    }

    function handleBlur(event) {
        const input = event.target;
        const optionDiv = input.closest('.option');
        const allOptions = Array.from(optionsContainer.querySelectorAll('.option'));
        const optionIndex = allOptions.indexOf(optionDiv);

        // Ensure that optionDiv is actually in the list
        if (optionIndex === -1) return;

        // Check if the current optionDiv is empty
        if (input.value.trim() === "" && !optionDiv.classList.contains('placeholder')) {
            // Check if there is a next option and if it is a placeholder
            const nextOptionDiv = allOptions[optionIndex + 1];
            if (nextOptionDiv && nextOptionDiv.classList.contains('placeholder') && optionIndex > 1) {
                optionDiv.remove();
            }
        }
        updatePlaceholders();
    }

    function resetQuestionForm() {
        form.reset();
        optionsContainer.innerHTML = '';
        addOptionDiv();
        addOptionDiv();
    }

    // Event delegation for dynamically added remove buttons
    optionsContainer.addEventListener('click', function (event) {
        if (event.target.closest('button[name="removeOption"]')) {
            const button = event.target.closest('button[name="removeOption"]');
            const parentDiv = button.closest('.option');
            if (parentDiv) {
                parentDiv.remove();
            }
            // Update placeholders
            updatePlaceholders();
        }
    });

    function updatePlaceholders() {
        const options = optionsContainer.getElementsByClassName('option');
        for (let i = 0; i < options.length; i++) {
            const input = options[i].querySelector('input[type="text"]');
            if (input) {
                input.placeholder = `Odpowiedź ${i + 1}`;
            }
        }
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const questionText = form.querySelector('#question').value;
        const options = Array.from(form.querySelectorAll('.option:not(.placeholder)')).map(optionDiv => {
            const optionText = optionDiv.querySelector('input[name="option"]').value;
            const isCorrect = optionDiv.querySelector('input[name="correctOption"]').checked;
            return { text: optionText, correct: isCorrect };
        });

        if (!questionText || options.length < 2) {
            showToast('Pytanie powinno mieć przynajmniej dwie odpowiedzi.', 'bg-danger');
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
                showToast('Pytanie zostało dodane', 'bg-success');
                resetQuestionForm();
                fetchQuestions(); // Refresh the list
                return;
            }

            if (!response.ok) {
                const error = (await response.json()).error;
                switch (error) {
                    case 'QUESTION_MUST_HAVE_AT_LEAST_1_CORRECT_ANSWER':
                        throw new Error('Przynajmniej jedna odpowiedź musi być prawidłowa');
                    default:
                        throw new Error('Błędne pytanie');
                }
            }
        } catch (error) {
            showToast(error.message, 'bg-danger');
        }
    });

    function showToast(message, bgColor) {
        const toastElement = document.getElementById('toast');
        const toastBody = toastElement.querySelector('.toast-body');

        toastBody.textContent = message;
        toastElement.classList.remove('bg-success', 'bg-danger');
        toastElement.classList.add(bgColor);

        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    }

    // Initial fetch of questions
    resetQuestionForm();
    fetchQuestions();
});

