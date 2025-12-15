import { sleep } from 'k6';
import { setupSession, fetchPostsAPI, createPostAPI, getRandomContent } from './utils.js';

export const options = {
  vus: 100,
  duration: '30s',
};

export default function () {
  setupSession();

  // 95% read, 5% write pattern
  if (Math.random() <= 0.05) {
    // 5% chance to create a new post via API
    createPostAPI(getRandomContent());
  } else {
    // 95% chance to fetch posts via API
    fetchPostsAPI();
  }

  sleep(0.5);
}
