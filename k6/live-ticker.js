import { sleep } from 'k6';
import { setupSharedSession, browseFeed, createRoar } from './utils.js';

export const options = {
  vus: 100,
  duration: '30s',
};

export default function () {
  setupSharedSession();

  if (Math.random() <= 0.80) {
    createRoar('New ADIS exercise just dropped! Let\'s throw some gang signs! ✊✌️✋');
  } else {
    browseFeed();
  }

  sleep(0.5);
}
