// File: assets/js/app.js
// Main JavaScript functionality for Judge Scoreboard Application

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the application
    initApp();
});

// Global variables
const UPDATE_INTERVAL = 5000; // 5 seconds
let updateTimer = null;

// Initialize the application
function initApp() {
    // Setup event listeners
    setupEventListeners();
    
    // Start auto-update for scoreboard if on index page
    if (document.getElementById('scoreboard')) {
        startAutoUpdate();
    }
    
    // Initialize score submission form if on judge page
    if (document.getElementById('score-form')) {
        initScoreForm();
    }
}

// Setup event listeners
function setupEventListeners() {
    // Logout button
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
    
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleMobileMenu);
    }
}

// Start auto-update for scoreboard
function startAutoUpdate() {
    // Initial load
    updateScoreboard();
    
    // Set interval for updates
    updateTimer = setInterval(updateScoreboard, UPDATE_INTERVAL);
}

// Update scoreboard data
async function updateScoreboard() {
    try {
        const response = await fetch('api/get_scores.php');
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        if (data.success) {
            renderScoreboard(data.scores);
        } else {
            console.error('Error updating scoreboard:', data.message);
        }
    } catch (error) {
        console.error('Error fetching scores:', error);
    }
}

// Render scoreboard data
function renderScoreboard(scores) {
    const tbody = document.querySelector('#scoreboard tbody');
    if (!tbody) return;
    
    tbody.innerHTML = scores.map((score, index) => `
        <tr class="score-row ${index < 3 ? 'top-' + (index + 1) : ''}">
            <td class="rank">${index + 1}</td>
            <td class="name">${escapeHtml(score.name)}</td>
            <td class="score">${parseFloat(score.average_score).toFixed(2)}</td>
            <td class="judges">${score.judge_count}</td>
            <td class="total">${parseFloat(score.total_score).toFixed(2)}</td>
            <td class="updated">${formatTimeAgo(score.last_updated)}</td>
        </tr>
    `).join('');
}

// Initialize score submission form
function initScoreForm() {
    const form = document.getElementById('score-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        try {
            const response = await fetch('api/submit_score.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            showNotification(data.message, data.success ? 'success' : 'error');
            
            if (data.success) {
                form.reset();
            }
        } catch (error) {
            showNotification('Error submitting score', 'error');
        }
    });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Handle logout
async function handleLogout() {
    try {
        const response = await fetch('api/logout.php');
        if (response.ok) {
            window.location.href = 'login.php';
        }
    } catch (error) {
        console.error('Error logging out:', error);
    }
}

// Toggle mobile menu
function toggleMobileMenu() {
    const nav = document.querySelector('nav');
    nav.classList.toggle('active');
}

// Utility: Escape HTML to prevent XSS
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Utility: Format time ago
function formatTimeAgo(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'just now';
    if (seconds < 3600) return Math.floor(seconds/60) + ' minutes ago';
    if (seconds < 86400) return Math.floor(seconds/3600) + ' hours ago';
    
    return date.toLocaleDateString();
}
