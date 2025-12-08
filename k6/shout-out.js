import { sleep } from 'k6';
import { setupSession } from './utils.js';

export const options = {
  vus: 100,
  duration: '30s',
};

export default function () {
  setupSession();  // Creates new unique user each iteration
  sleep(0.5);
}
