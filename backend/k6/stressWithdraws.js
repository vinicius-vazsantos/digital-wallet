import { setup, createAccount, formatDate, formatHours, validateResponse, BASE_URL } from './utils.js';
export { setup };
import http from 'k6/http';
import { sleep } from 'k6';

export let options = {
    vus: __ENV.STRESS_VUS ? parseInt(__ENV.STRESS_VUS) : 50,
    duration: __ENV.STRESS_DURATION || '30s',
};

export function stressWithdraws(data) {
    const headers = data.headers;

    const accountId = createAccount(headers, 100000);
    if (!accountId) return;

    const stressTestCases = [
        { desc: 'PIX instantâneo', payload: { method: 'PIX', pix: { type: 'email', key: 'no-reply@example.com' }, amount: 1000 }, expectSuccess: true },
        { desc: 'PIX agendado em 1 dia', payload: { method: 'PIX', pix: { type: 'email', key: 'no-reply@example.com' }, amount: 1000, schedule: formatDate(1) }, expectSuccess: true },
        { desc: 'PIX agendado em 3 horas', payload: { method: 'PIX', pix: { type: 'email', key: 'no-reply@example.com' }, amount: 1000, schedule: formatHours(3) }, expectSuccess: true },
    ];

    for (const t of stressTestCases) {
        const res = http.post(`${BASE_URL}/${accountId}/balance/withdraws`, JSON.stringify(t.payload), { headers });
        validateResponse(res, res.json(), t);
        sleep(0.2);
    }
}

export default function (data) {
    stressWithdraws(data); // recebe { token } do setup()
}