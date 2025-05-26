document.addEventListener('DOMContentLoaded', function() {
    // Initialize the admin panel
    initAdminPanel();
    
    // Initialize score form if it exists
    initScoreForm();
});

function initAdminPanel() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize delete modals
    initDeleteModals();
    
    // Initialize forms
    initForms();
}

function initDeleteModals() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let deleteType = '';
    let deleteId = '';
    
    // User delete buttons
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            deleteType = 'user';
            deleteId = this.dataset.id;
            const username = this.dataset.username;
            
            document.getElementById('deleteMessage').innerHTML = `
                Are you sure you want to delete the user <strong>${escapeHtml(username)}</strong>?
                This action cannot be undone.
            `;
            
            deleteModal.show();
        });
    });
    
    // Participant delete buttons
    document.querySelectorAll('.delete-participant').forEach(button => {
        button.addEventListener('click', function() {
            deleteType = 'participant';
            deleteId = this.dataset.id;
            const name = this.dataset.name;
            
            document.getElementById('deleteMessage').innerHTML = `
                Are you sure you want to delete the participant <strong>${escapeHtml(name)}</strong>?
                All associated scores will also be deleted.
            `;
            
            deleteModal.show();
        });
    });
    
    // Confirm delete button
    document.getElementById('confirmDelete').addEventListener('click', async function() {
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border spinner-border-sm me-1';
        spinner.setAttribute('role', 'status');
        spinner.setAttribute('aria-hidden', 'true');
        
        const originalContent = this.innerHTML;
        this.innerHTML = '';
        this.appendChild(spinner);
        this.appendChild(document.createTextNode(' Deleting...'));
        this.disabled = true;
        
        try {
            let endpoint = '';
            let data = new FormData();
            
            if (deleteType === 'user') {
                endpoint = 'api/manage_users.php';
                data.append('action', 'delete_user');
                data.append('user_id', deleteId);
            } else if (deleteType === 'participant') {
                endpoint = 'api/manage_users.php';
                data.append('action', 'delete_participant');
                data.append('participant_id', deleteId);
            }
            
            const response = await fetch(endpoint, {
                method: 'POST',
                body: data
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('success', result.message);
                // Remove the deleted item from the table
                document.querySelector(`.delete-${deleteType}[data-id="${deleteId}"]`).closest('tr').remove();
            } else {
                showToast('danger', result.message || 'Error deleting item');
            }
        } catch (error) {
            showToast('danger', 'Network error - please try again');
            console.error('Delete error:', error);
        } finally {
            deleteModal.hide();
            this.innerHTML = originalContent;
            this.disabled = false;
        }
    });
}

function initForms() {
    // Add user form
    const addUserForm = document.getElementById('add-user-form');
    if (addUserForm) {
        addUserForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add_user');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Adding...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('api/manage_users.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('success', 'User added successfully');
                    this.reset();
                    // Refresh the page to show new user
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('danger', result.message || 'Error adding user');
                }
            } catch (error) {
                showToast('danger', 'Network error - please try again');
                console.error('Add user error:', error);
            } finally {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            }
        });
    }
    
    // Add participant form
    const addParticipantForm = document.getElementById('add-participant-form');
    if (addParticipantForm) {
        addParticipantForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add_participant');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Adding...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('api/manage_users.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('success', result.message);
                    
                    // Get the form values before resetting
                    const name = formData.get('name');
                    const identifier = formData.get('identifier');
                    
                    // Create new row
                    const tbody = document.querySelector('#add-participant-form').closest('.card').querySelector('tbody');
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td>${escapeHtml(name)}</td>
                        <td>${escapeHtml(identifier)}</td>
                        <td>
                            <span class="badge bg-danger text-dark">0.00</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger delete-participant" 
                                    data-id="${result.participant_id}"
                                    data-name="${escapeHtml(name)}">
                                <i class="fas fa-trash-alt me-1"></i> Delete
                            </button>
                        </td>
                    `;
                    
                    // Add the new row at the top of the table
                    tbody.insertBefore(newRow, tbody.firstChild);
                    
                    // Reset the form
                    this.reset();
                    
                    // Reinitialize delete buttons
                    initDeleteModals();
                } else {
                    showToast('danger', result.message || 'Error adding participant');
                }
            } catch (error) {
                showToast('danger', 'Network error - please try again');
                console.error('Add participant error:', error);
            } finally {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            }
        });
    }
}

function initScoreForm() {
    const scoreForm = document.getElementById('score-form');
    if (scoreForm) {
        // Link range input to number input
        const scoreInput = document.getElementById('score');
        const scoreRange = document.getElementById('scoreRange');
        
        scoreInput.addEventListener('input', function() {
            scoreRange.value = this.value;
        });
        
        scoreRange.addEventListener('input', function() {
            scoreInput.value = this.value;
        });
        
        // Handle form submission
        scoreForm.addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent default form submission
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            // Log form data for debugging
            console.log('Submitting score with data:', {
                judge_name: formData.get('judge_name'),
                participant_id: formData.get('participant_id'),
                score: formData.get('score')
            });
            
            // Show loading state
            spinner.classList.remove('d-none');
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('api/submit_score.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('success', result.message);
                    
                    // Get form values
                    const judgeName = formData.get('judge_name');
                    const score = parseFloat(formData.get('score')).toFixed(2);
                    const participantSelect = document.getElementById('participant');
                    const participantName = participantSelect.options[participantSelect.selectedIndex].text;
                    
                    // Create new score row
                    const tbody = document.querySelector('.table tbody');
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-tie text-primary"></i>
                                </div>
                                <span>${escapeHtml(judgeName)}</span>
                            </div>
                        </td>
                        <td>${escapeHtml(participantName)}</td>
                        <td>
                            <span class="badge bg-${getScoreColorClass(score)} rounded-pill">
                                ${score}
                            </span>
                        </td>
                        <td class="pe-4">
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>just now
                            </small>
                        </td>
                    `;
                    
                    // Add the new row at the top of the table
                    tbody.insertBefore(newRow, tbody.firstChild);
                    
                    // Update the score count badge
                    const countBadge = document.querySelector('.card-header .badge');
                    if (countBadge) {
                        const currentCount = parseInt(countBadge.textContent.match(/\d+/)[0]);
                        countBadge.innerHTML = `<i class="fas fa-list-check me-1"></i>${currentCount + 1}`;
                    }
                    
                    // Reset form
                    this.reset();
                    scoreRange.value = 0; // Reset range input too
                } else {
                    showToast('danger', result.message || 'Error submitting score');
                }
            } catch (error) {
                console.error('Submit score error:', error);
                showToast('danger', 'Network error - please try again');
            } finally {
                // Restore button state
                spinner.classList.add('d-none');
                submitBtn.disabled = false;
            }
        });
    }
}

// Helper function to determine score color class
function getScoreColorClass(score) {
    score = parseFloat(score);
    if (score >= 90) return 'success';
    if (score >= 75) return 'primary';
    if (score >= 60) return 'warning';
    return 'danger';
}

function showToast(type, message) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toastElement = document.createElement('div');
    
    toastElement.className = `toast align-items-center text-white bg-${type} border-0`;
    toastElement.setAttribute('role', 'alert');
    toastElement.setAttribute('aria-live', 'assertive');
    toastElement.setAttribute('aria-atomic', 'true');
    
    toastElement.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toastElement);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove toast after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1100';
    document.body.appendChild(container);
    return container;
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}