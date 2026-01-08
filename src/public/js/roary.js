/**
 * Roary - Dynamic Post Loading with API
 */

// API Configuration
const API_BASE = '/api/posts.php';

// State
let posts = [];
let isLoading = false;

// DOM Elements
let feedContainer = null;
let postForm = null;
let postInput = null;

/**
 * Initialize the app
 */
document.addEventListener('DOMContentLoaded', () => {
    feedContainer = document.getElementById('feed-container');
    postForm = document.getElementById('post-form');
    postInput = document.getElementById('postInput');

    if (feedContainer) {
        loadPosts();
    }

    if (postForm) {
        postForm.addEventListener('submit', handlePostSubmit);
    }
});

/**
 * Fetch posts from API
 */
async function loadPosts() {
    if (isLoading) return;
    
    isLoading = true;
    showLoading();

    try {
        const response = await fetch(API_BASE);
        const result = await response.json();

        if (response.status === 401 || result.requiresAuth) {
            window.location.href = '/login';
            return;
        }

        if (result.success && result.data) {
            posts = result.data;
            renderPosts();
        } else {
            showError('Failed to load posts: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error loading posts:', error);
        showError('Failed to load posts. Please try again later.');
    } finally {
        isLoading = false;
    }
}

/**
 * Create a new post
 */
async function createPost(content) {
    if (!navigator.onLine) {
        showError('Cannot create posts while offline. Please check your connection.');
        return false;
    }

    try {
        const response = await fetch(API_BASE, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ content })
        });

        const result = await response.json();

        if (response.status === 401 || result.requiresAuth) {
            window.location.href = '/login';
            return false;
        }

        if (result.success && result.data) {
            // Add new post to beginning of array
            posts.unshift(result.data);
            renderPosts();
            return true;
        } else {
            showError(result.message || 'Failed to create post');
            return false;
        }
    } catch (error) {
        console.error('Error creating post:', error);
        showError('Failed to create post. Please try again.');
        return false;
    }
}

/**
 * Delete a post
 */
async function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this roar?')) {
        return;
    }

    if (!navigator.onLine) {
        showError('Cannot delete posts while offline. Please check your connection.');
        return;
    }

    try {
        const response = await fetch(API_BASE, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: postId })
        });

        const result = await response.json();

        if (response.status === 401 || result.requiresAuth) {
            window.location.href = '/login';
            return;
        }

        if (result.success) {
            // Remove post from array
            posts = posts.filter(post => post.id !== postId);
            renderPosts();
            showSuccess('Post deleted successfully');
        } else {
            showError(result.message || 'Failed to delete post');
        }
    } catch (error) {
        console.error('Error deleting post:', error);
        showError('Failed to delete post. Please try again.');
    }
}

/**
 * Handle post form submission
 */
async function handlePostSubmit(e) {
    e.preventDefault();

    const content = postInput.value.trim();

    if (!content) {
        showError('Please enter some content');
        return;
    }

    if (content.length > 280) {
        showError('Post must be 280 characters or less');
        return;
    }

    const success = await createPost(content);

    if (success) {
        postInput.value = '';
        showSuccess('Post created successfully!');
    }
}

/**
 * Render posts to the DOM
 */
function renderPosts() {
    if (!feedContainer) return;

    if (posts.length === 0) {
        feedContainer.innerHTML = `
            <article>
                <p>No roars yet. Be the first to roar!</p>
            </article>
        `;
        return;
    }

    feedContainer.innerHTML = posts.map(post => createPostHTML(post)).join('');

    // Attach delete button event listeners
    feedContainer.querySelectorAll('[data-delete-post]').forEach(button => {
        button.addEventListener('click', (e) => {
            const postId = parseInt(e.target.dataset.deletePost);
            deletePost(postId);
        });
    });
}

/**
 * Create HTML for a single post using template
 */
function createPostHTML(post) {
    const currentUser = feedContainer.dataset.currentUser;
    const isOwner = currentUser && currentUser === post.username;
    const avatar = post.avatar || '🐧';
    const timeAgo = formatTimeAgo(post.created_at);

    return `
        <article>
            <header style="display: flex; justify-content: space-between; align-items: flex-start;">
                <hgroup>
                    <p>
                        <span aria-label="Avatar">${escapeHtml(avatar)}</span>
                        <strong>${escapeHtml(post.username)}</strong>
                    </p>
                    <p><small>${escapeHtml(timeAgo)}</small></p>
                </hgroup>
                ${isOwner ? `
                    <button 
                        type="button" 
                        class="outline" 
                        data-delete-post="${post.id}"
                        aria-label="Delete post">
                        🗑️
                    </button>
                ` : ''}
            </header>
            <p>${escapeHtml(post.content)}</p>
        </article>
    `;
}

/**
 * Format timestamp to relative time
 */
function formatTimeAgo(timestamp) {
    const now = new Date();
    const postDate = new Date(timestamp * 1000); // Convert Unix timestamp (seconds) to milliseconds
    const seconds = Math.floor((now - postDate) / 1000);

    const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60
    };

    for (const [unit, secondsInUnit] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / secondsInUnit);
        if (interval >= 1) {
            return `${interval} ${unit}${interval !== 1 ? 's' : ''} ago`;
        }
    }

    return 'just now';
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show loading state
 */
function showLoading() {
    if (feedContainer) {
        feedContainer.innerHTML = `
            <article aria-busy="true">
                <p>Loading roars...</p>
            </article>
        `;
    }
}

/**
 * Show error message
 */
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.setAttribute('role', 'alert');
    errorDiv.innerHTML = `
        <article style="background-color: var(--pico-del-color); margin-bottom: 1rem;">
            <p><strong>Error:</strong> ${escapeHtml(message)}</p>
        </article>
    `;
    
    const main = document.querySelector('main');
    if (main) {
        main.insertBefore(errorDiv, main.firstChild);
        setTimeout(() => errorDiv.remove(), 5000);
    }
}

/**
 * Show success message
 */
function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.setAttribute('role', 'status');
    successDiv.innerHTML = `
        <article style="background-color: var(--pico-ins-color); margin-bottom: 1rem;">
            <p>${escapeHtml(message)}</p>
        </article>
    `;
    
    const main = document.querySelector('main');
    if (main) {
        main.insertBefore(successDiv, main.firstChild);
        setTimeout(() => successDiv.remove(), 3000);
    }
}

// Auto-refresh posts every 30 seconds (only when online)
setInterval(() => {
    if (feedContainer && !isLoading && navigator.onLine) {
        loadPosts();
    }
}, 30000);
