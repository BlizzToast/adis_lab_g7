import http from "k6/http";
import { check, fail } from "k6";

// ============================================================================
// CONFIGURATION
// ============================================================================

// Base URL for the application
export const BASE_URL = __ENV.BASE_URL || "http://localhost";

// Enable additional validation checks (stricter testing)
const VALIDATE = __ENV.VALIDATE === "true" || __ENV.VALIDATE === "1";

// Shared user credentials (for doom-scroll and live-ticker)
const SHARED_USERNAME = __ENV.SHARED_USERNAME || 'testuser1';
const SHARED_PASSWORD = __ENV.SHARED_PASSWORD || 'TestPass1234';

// Test user credentials (for shout-out registration testing)
const TEST_USERNAME = __ENV.TEST_USERNAME || 'k6user';
const TEST_PASSWORD = __ENV.TEST_PASSWORD || 'K6TestPass1234';

// Shared session ID - cached and reused across all VUs
let sharedSessionId = null;

// Per-VU iteration counter for unique username generation
let vuIterationCount = 0;

// Test run timestamp - ensures unique usernames across test runs
const testRunTimestamp = Date.now();

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function extractSessionCookie(response) {
  if (!response.headers['Set-Cookie']) {
    return null;
  }
  
  // Handle both single string and array of cookies
  const cookies = Array.isArray(response.headers['Set-Cookie']) 
    ? response.headers['Set-Cookie'] 
    : [response.headers['Set-Cookie']];
  
  // Find roary_session cookie
  for (const cookie of cookies) {
    const match = cookie.match(/roary_session=([^;]+)/);
    if (match) {
      return match[1];
    }
  }
  return null;
}

function extractCsrfToken(body) {
  const match = body.match(/name="csrf_token"\s+value="([^"]+)"/);
  return match ? match[1] : '';
}

function attemptLogin(username, password) {
  const loginPage = http.get(`${BASE_URL}/login`);
  const csrfToken = extractCsrfToken(loginPage.body);
  
  const loginRes = http.post(`${BASE_URL}/login`, {
    username,
    password,
    csrf_token: csrfToken,
  }, {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    redirects: 0,
  });
  
  // Return session cookie if login successful
  if (loginRes.status === 302 && loginRes.headers['Location'] === '/') {
    return extractSessionCookie(loginRes);
  }
  return null;
}

function attemptRegistration(username, password, avatar) {
  const registerPage = http.get(`${BASE_URL}/register`);
  const csrfToken = extractCsrfToken(registerPage.body);
  
  const registerRes = http.post(`${BASE_URL}/register`, {
    username,
    password,
    confirmPassword: password,
    avatar,
    csrf_token: csrfToken,
  }, {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    redirects: 0,
  });
  
  // Return session cookie if registration successful
  if (registerRes.status === 302) {
    return extractSessionCookie(registerRes);
  }
  return null;
}

// ============================================================================
// SESSION MANAGEMENT
// ============================================================================

export function getSharedSessionId() {
  if (sharedSessionId) {
    return sharedSessionId;
  }

  // Attempt 1: Try to login
  let cookie = attemptLogin(SHARED_USERNAME, SHARED_PASSWORD);
  if (cookie) {
    sharedSessionId = cookie;
    return sharedSessionId;
  }

  // Attempt 2: Register new user
  cookie = attemptRegistration(SHARED_USERNAME, SHARED_PASSWORD, '🦖');
  if (cookie) {
    sharedSessionId = cookie;
    return sharedSessionId;
  }

  // Attempt 3: Retry login (another VU might have created the user)
  cookie = attemptLogin(SHARED_USERNAME, SHARED_PASSWORD);
  if (cookie) {
    sharedSessionId = cookie;
    return sharedSessionId;
  }

  // All attempts failed
  fail(`Failed to authenticate ${SHARED_USERNAME}`);
  return null;
}

export function getUniqueSession() {
  vuIterationCount++;
  
  // Generate unique username for this iteration
  const username = `${TEST_USERNAME}${testRunTimestamp}${__VU}i${vuIterationCount}`;
  
  // Try login first (in case user exists from interrupted test)
  const loginCookie = attemptLogin(username, TEST_PASSWORD);
  if (loginCookie) {
    return loginCookie;
  }
  
  // Register new user
  const regCookie = attemptRegistration(username, TEST_PASSWORD, '🤖');
  if (regCookie) {
    return regCookie;
  }
  
  // Both failed
  fail(`Failed to authenticate user ${username}`);
  return null;
}

// Setup shared session (for doom-scroll)
export function setupSharedSession() {
  getSharedSessionId();
}

// Setup unique session (for live-ticker)
export function setupSession() {
  return getUniqueSession();
}

// ============================================================================
// HTTP OPERATIONS - REST API
// ============================================================================

// GET /api/posts - Fetch posts via REST API
export function fetchPostsAPI(sessionCookie) {
  const headers = {
    Accept: "application/json",
  };
  
  // Add session cookie if provided
  if (sessionCookie) {
    headers.Cookie = `roary_session=${sessionCookie}`;
  }

  const response = http.get(`${BASE_URL}/api/posts.php`, { headers });

  // Try to parse JSON for validation
  let parsed = null;
  try {
    parsed = JSON.parse(response.body);
  } catch (e) {
    // parsing failed; leave parsed as null
  }

  if (VALIDATE) {
    check(response, {
      "status is 200": (r) => r.status === 200,
      "response is JSON": (r) =>
        r.headers["Content-Type"] &&
        r.headers["Content-Type"].includes("application/json"),
      "posts array exists": (r) => parsed && parsed.success && Array.isArray(parsed.data),
    });
  } else {
    check(response, {
      "status is 200": (r) => r.status === 200,
    });
  }

  return response;
}

// POST /api/posts - Create a new post via REST API
export function createPostAPI(content, sessionCookie) {
  const payload = JSON.stringify({
    content: content || `API Test Roar at ${new Date().toISOString()}`,
  });

  const headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
  };
  
  // Use provided session cookie or shared session
  if (sessionCookie) {
    headers.Cookie = `roary_session=${sessionCookie}`;
  } else if (sharedSessionId) {
    headers.Cookie = `roary_session=${sharedSessionId}`;
  }

  const response = http.post(`${BASE_URL}/api/posts.php`, payload, { headers });

  if (VALIDATE) {
    check(response, {
      "status is 201": (r) => {
        const success = r.status === 201;
        if (!success) {
          console.log(`POST /api/posts.php failed: status=${r.status}, body=${r.body}`);
        }
        return success;
      },
      "response is JSON": (r) =>
        r.headers["Content-Type"] &&
        r.headers["Content-Type"].includes("application/json"),
      "success response": (r) => {
        try {
          const json = JSON.parse(r.body);
          return json.success === true && json.data !== null;
        } catch {
          return false;
        }
      },
    });
  } else {
    check(response, {
      "post created": (r) => {
        const success = r.status === 201;
        if (!success) {
          console.log(`POST /api/posts.php failed: status=${r.status}, VU=${__VU}`);
        }
        return success;
      },
    });
  }

  return response;
}

// Export a function to get random post content
export function getRandomContent() {
  const contents = [
    "Testing the new REST API! 🚀",
    "Performance testing with k6 is awesome!",
    "RESTful APIs make everything better",
    "JSON responses are so much cleaner",
    "Loving the API-first approach",
    "Frontend-backend separation FTW!",
    "Asynchronous loading is the future",
    "REST API test roar #" + Math.floor(Math.random() * 1000),
  ];

  return contents[Math.floor(Math.random() * contents.length)];
}
