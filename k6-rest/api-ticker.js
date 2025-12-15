import { sleep } from 'k6';
import { setupSession, fetchPostsAPI, createPostAPI, getRandomContent } from './utils.js';

export const options = {
  vus: 100,
  duration: '30s',
};

export default function () {
  setupSession();

  // 80% write, 20% read pattern (live ticker scenario)
  if (Math.random() <= 0.80) {
    // 80% chance to create a new post via API
    createPostAPI(getRandomContent());
  } else {
    // 20% chance to fetch posts via API
    fetchPostsAPI();
  }

  sleep(0.5);
}
