/**
 * This module provides session management and HTTP operations for load testing.
 * It supports two session strategies:
 * 1. Shared session - All VUs share one user account
 * 2. Unique sessions - Each iteration creates a new user
 */

import http from 'k6/http';
import { check, fail } from 'k6';

// ============================================================================
// CONFIGURATION
// ============================================================================

// Base URL for the application
export const BASE_URL = __ENV.BASE_URL || 'http://localhost';

// Enable additional validation checks (stricter testing)
const VALIDATE = __ENV.VALIDATE === 'true' || __ENV.VALIDATE === '1';

// Shared user credentials (for doom-scroll and live-ticker)
const SHARED_USERNAME = __ENV.SHARED_USERNAME || 'testuser1';
const SHARED_PASSWORD = __ENV.SHARED_PASSWORD || 'TestPass1234';

// Test user credentials (for shout-out registration testing)
const TEST_USERNAME = __ENV.TEST_USERNAME || 'k6user';
const TEST_PASSWORD = __ENV.TEST_PASSWORD || 'K6TestPass1234';

// ============================================================================
// STATE MANAGEMENT
// ============================================================================

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


export function getSession() {
  vuIterationCount++;
  
  // Generate unique username for this iteration
  const username = `${TEST_USERNAME}${testRunTimestamp}${__VU}i${vuIterationCount}`;
  
  // Try login first (in case user exists from interrupted test)
  const loginCookie = attemptLogin(username, TEST_PASSWORD);
  if (loginCookie) {
    return 'logged_in';
  }
  
  // Register new user
  const regCookie = attemptRegistration(username, TEST_PASSWORD, '🤖');
  if (regCookie) {
    return 'logged_in';
  }
  
  // Both failed
  fail(`Failed to authenticate user ${username}`);
  return null;
}


export function setupSharedSession() {
  getSharedSessionId();
}

export function setupSession() {
  getSession();
}

// ============================================================================
// HTTP OPERATIONS
// ============================================================================


export function browseFeed() {
  const headers = sharedSessionId 
    ? { Cookie: `roary_session=${sharedSessionId}` }
    : {};
  
  const res = http.get(`${BASE_URL}/`, { headers });
  
  const checks = {
    'feed loaded': (r) => r.status === 200,
  };
  
  if (VALIDATE) {
    checks['feed contains content'] = (r) => r.body && r.body.length > 1000;
    checks['feed has roars'] = (r) => r.body.includes('roar') || r.body.includes('ROAR');
  }
  
  check(res, checks);
}


export function createRoar(content) {
  const headers = sharedSessionId 
    ? { 'Content-Type': 'application/x-www-form-urlencoded', Cookie: `roary_session=${sharedSessionId}` }
    : { 'Content-Type': 'application/x-www-form-urlencoded' };
  
  // Fetch CSRF token from homepage
  const cookieHeader = sharedSessionId ? { Cookie: `roary_session=${sharedSessionId}` } : {};
  const homePage = http.get(`${BASE_URL}/`, { headers: cookieHeader });
  const csrfToken = extractCsrfToken(homePage.body);
  
  // Submit post
  const res = http.post(`${BASE_URL}/create-post`, {
    content: content || `Roar from k6 at ${Date.now()}`,
    csrf_token: csrfToken,
  }, {
    headers,
    redirects: 0,
  });

  // Validate response
  const checks = {
    'roar created': (r) => {
      const success = r.status === 302 && r.headers['Location'] === '/';
      if (!success) {
        console.log(`POST /create-post failed: status=${r.status}, location=${r.headers['Location']}, VU=${__VU}`);
      }
      return success;
    },
  };
  
  if (VALIDATE) {
    checks['roar redirects to home'] = (r) => {
      const location = r.headers['Location'] || '';
      if (location !== '/') {
        console.log(`createRoar redirect failed: status=${r.status}, location=${location}`);
      }
      return r.status === 302 && (location === '/' || location.includes('index'));
    };
  }

  check(res, checks);
}
