import { sleep } from 'k6';
import { getSharedSessionId, fetchPostsAPI, createPostAPI, getRandomContent } from './utils.js';

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
  
  // 95% read, 5% write pattern
  if (Math.random() <= 0.05) {
    createPostAPI(getRandomContent(), sessionId);
  } else {
    fetchPostsAPI(sessionId);
  }

  sleep(0.5);
}
