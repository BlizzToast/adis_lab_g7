import { sleep } from 'k6';
import http from 'k6/http';
import { BASE_URL } from './utils.js';

export const options = {
  vus: 100,
  duration: '30s',
};

export default function () {
  http.get(`${BASE_URL}/api/ping.php`);
  sleep(0.5);
}
