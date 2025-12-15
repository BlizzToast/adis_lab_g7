import http from "k6/http";
import { check } from "k6";

// Get base URL from environment or use default
const BASE_URL = __ENV.BASE_URL || "http://localhost";
const PHPSESSID = __ENV.PHPSESSID || "";
const VALIDATE = __ENV.VALIDATE === "true";

// Setup cookie jar with session
export function setupSession() {
  if (PHPSESSID) {
    const jar = http.cookieJar();
    jar.set(BASE_URL, "PHPSESSID", PHPSESSID);
  }
}

// GET /api/posts - Fetch posts via REST API
export function fetchPostsAPI() {
  setupSession();

  const params = {
    headers: {
      Accept: "application/json",
    },
  };

  const response = http.get(`${BASE_URL}/api/posts.php`, params);

  if (VALIDATE) {
    check(response, {
      "status is 200": (r) => r.status === 200,
      "response is JSON": (r) =>
        r.headers["Content-Type"] &&
        r.headers["Content-Type"].includes("application/json"),
      "posts array exists": (r) => {
        try {
          const json = JSON.parse(r.body);
          return (
            Array.isArray(json) || (json.posts && Array.isArray(json.posts))
          );
        } catch {
          return false;
        }
      },
    });
  }

  return response;
}

// POST /api/posts - Create a new post via REST API
export function createPostAPI(content) {
  setupSession();

  const payload = JSON.stringify({
    content: content || `API Test Roar at ${new Date().toISOString()}`,
  });

  const params = {
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  };

  const response = http.post(`${BASE_URL}/api/posts.php`, payload, params);

  if (VALIDATE) {
    check(response, {
      "status is 200 or 201": (r) => r.status === 200 || r.status === 201,
      "response is JSON": (r) =>
        r.headers["Content-Type"] &&
        r.headers["Content-Type"].includes("application/json"),
      "success response": (r) => {
        try {
          const json = JSON.parse(r.body);
          return (
            json.success === true ||
            json.status === "success" ||
            json.id !== undefined
          );
        } catch {
          return false;
        }
      },
    });
  }

  return response;
}

// Hybrid approach: Fetch page then use API
export function fetchPageThenAPI() {
  setupSession();

  // First load the main page (for session/auth check)
  const pageResponse = http.get(BASE_URL);

  if (VALIDATE) {
    check(pageResponse, {
      "page loaded": (r) => r.status === 200,
      "user is logged in": (r) =>
        r.body.includes("Logout") || r.body.includes("logout"),
    });
  }

  // Then fetch posts via API
  return fetchPostsAPI();
}

// Register new user for shout-out scenario
export function registerUserAPI() {
  const username = `api_user_${Date.now()}_${Math.random().toString(36).substring(7)}`;
  const password = "testpass123";

  const payload = JSON.stringify({
    username: username,
    password: password,
    confirm_password: password,
  });

  const params = {
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  };

  // Assuming there's an API endpoint for registration
  // If not, this would need to use the regular form submission
  const response = http.post(`${BASE_URL}/api/register.php`, payload, params);

  if (response.status === 404) {
    // Fallback to regular registration if API endpoint doesn't exist
    return registerUserForm();
  }

  if (VALIDATE) {
    check(response, {
      "registration successful": (r) => r.status === 200 || r.status === 201,
      "response is JSON": (r) =>
        r.headers["Content-Type"] &&
        r.headers["Content-Type"].includes("application/json"),
    });
  }

  return response;
}

// Fallback: Register user using form submission
export function registerUserForm() {
  const username = `user_${Date.now()}_${Math.random().toString(36).substring(7)}`;
  const password = "testpass123";

  const formData = {
    username: username,
    password: password,
    confirm_password: password,
  };

  const response = http.post(`${BASE_URL}/register.php`, formData);

  if (VALIDATE) {
    check(response, {
      "registration successful": (r) =>
        r.status === 200 && !r.body.includes("error"),
      "redirected to login or profile": (r) =>
        r.body.includes("login") || r.body.includes("profile"),
    });
  }

  return response;
}

// Utility to measure API vs SSR performance
export function measureAPIvsSSR() {
  setupSession();

  // Measure SSR approach
  const ssrStart = Date.now();
  const ssrResponse = http.get(BASE_URL);
  const ssrTime = Date.now() - ssrStart;

  // Measure API approach
  const apiStart = Date.now();
  const apiResponse = fetchPostsAPI();
  const apiTime = Date.now() - apiStart;

  return {
    ssr: { response: ssrResponse, time: ssrTime },
    api: { response: apiResponse, time: apiTime },
    improvement: ((ssrTime - apiTime) / ssrTime) * 100,
  };
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
