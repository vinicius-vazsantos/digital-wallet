import { setup, createAccount, formatDate, formatHours, validateResponse, BASE_URL, errorRate } from './utils.js';
export { setup };
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
    vus: __ENV.STRESS_VUS ? parseInt(__ENV.STRESS_VUS) : 1,
    iterations: __ENV.STRESS_ITERATIONS ? parseInt(__ENV.STRESS_ITERATIONS) : 1,
    duration: __ENV.STRESS_DURATION || '30s',
};

export function errorResponse(data) {
    // data.headers é retornado pelo setup()
    const headers = data.headers;

    // 1. Teste GET /accounts
    let res = http.get(`${BASE_URL}`, { headers });
    check(res, { 'GET /accounts status 200': (r) => r.status >= 200 && r.status < 300 }) || errorRate.add(1);

    // 2. Teste POST /accounts (criação de conta)
    const accountId = createAccount(headers);
    if (!accountId) return;

    const unifiedTestCases = [
        // Testes CRUD - contas
        { desc: 'GET conta não existente', method: 'GET', url: `${BASE_URL}/invalid-id`, expectSuccess: false, expectedError: 'ACCOUNT_NOT_FOUND' },
        { desc: 'PUT conta não existente', method: 'PUT', url: `${BASE_URL}/invalid-id`, payload: { name: 'Nome qualquer' }, expectSuccess: false, expectedError: 'ACCOUNT_NOT_FOUND' },
        { desc: 'DELETE conta não existente', method: 'DELETE', url: `${BASE_URL}/invalid-id`, expectSuccess: false, expectedError: 'ACCOUNT_NOT_FOUND' },
        { desc: 'Criar conta sem nome', method: 'POST', url: `${BASE_URL}`, payload: { name: '', balance: 1000 }, expectSuccess: false, expectedError: 'REQUIRED_FIELD_MISSING' },
        { desc: 'Criar conta com saldo negativo', method: 'POST', url: `${BASE_URL}`, payload: { name: 'Nome Teste', balance: -100 }, expectSuccess: false, expectedError: 'INVALID_BALANCE' },
        { desc: 'Atualizar conta sem campos', method: 'PUT', url: `${BASE_URL}/${accountId}`, payload: {}, expectSuccess: false, expectedError: 'NO_FIELDS_TO_UPDATE' },
        { desc: 'Saque maior que saldo', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { method: 'PIX', pix: { type: 'email', key: 'no-reply@example.com' }, amount: 1000000 }, expectSuccess: false, expectedError: 'INSUFFICIENT_BALANCE' },

        // Testes de saque
        { desc: 'Sem method', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { pix: { type: 'email', key: 'no-reply@example.com' }, amount: 10000 }, expectSuccess: false, expectedError: 'REQUIRED_FIELD_MISSING' },
        { desc: 'Método inválido', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { method: 'BOLETO', amount: 1000 }, expectSuccess: false, expectedError: 'UNSUPPORTED_WITHDRAW_METHOD' },
        { desc: 'PIX sem type', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { method: 'PIX', pix: { key: 'no-reply@example.com' }, amount: 1000 }, expectSuccess: false, expectedError: 'REQUIRED_FIELD_MISSING' },
        { desc: 'PIX sem key', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { method: 'PIX', pix: { type: 'email' }, amount: 1000 }, expectSuccess: false, expectedError: 'REQUIRED_FIELD_MISSING' },
        { desc: 'Sem amount', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { method: 'PIX', pix: { type: 'email', key: 'no-reply@example.com' } }, expectSuccess: false, expectedError: 'REQUIRED_FIELD_MISSING' },
        { desc: 'Schedule passado', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { method: 'PIX', pix: { type: 'email', key: 'no-reply@example.com' }, amount: 1000, schedule: formatDate(-1) }, expectSuccess: false, expectedError: 'PAST_SCHEDULING_NOT_ALLOWED' },
        { desc: 'Schedule >7 dias', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { method: 'PIX', pix: { type: 'email', key: 'no-reply@example.com' }, amount: 1000, schedule: formatDate(9) }, expectSuccess: false, expectedError: 'SCHEDULING_LIMIT_EXCEEDED' },
        { desc: 'Instantâneo', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { method: 'PIX', pix: { type: 'email', key: 'no-reply@example.com' }, amount: 1000 }, expectSuccess: true },
        { desc: 'PIX agendado em 3 horas', method: 'POST', url: `${BASE_URL}/${accountId}/balance/withdraws`, payload: { method: 'PIX', pix: { type: 'email', key: 'no-reply@example.com' }, amount: 1000, schedule: formatHours(3) }, expectSuccess: true },
    ];

    // Loop de verificações
    for (const t of unifiedTestCases) {
        let res;
        if (t.method === 'GET') res = http.get(t.url, { headers });
        if (t.method === 'PUT') res = http.put(t.url, JSON.stringify(t.payload), { headers });
        if (t.method === 'DELETE') res = http.del(t.url, null, { headers });
        if (t.method === 'POST') res = http.post(t.url, JSON.stringify(t.payload), { headers });

        const body = res.json();
        validateResponse(res, body, t);
        sleep(0.5);
    }

    sleep(1); // tempo entre requisições
}

export default function (data) {
    errorResponse(data); // recebe { token } do setup()
}