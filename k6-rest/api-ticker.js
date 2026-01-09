import { sleep } from 'k6';
import { getSharedSessionId, createPostAPI, fetchPostsAPI, getRandomContent } from './utils.js';

export const options = {
  vus: 100,
  duration: '30s',
};

export function setup() {
  const sessionId = getSharedSessionId();
  return { sessionId };
}

export default function (data) {
  // data contains the return value from setup()
  const sessionId = data.sessionId;

  // 80% write, 20% read pattern (live ticker scenario)
  if (Math.random() <= 0.80) {
    createPostAPI(getRandomContent(), sessionId);
  } else {
    fetchPostsAPI(sessionId);
  }

  sleep(0.5);
}
