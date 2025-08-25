import http from 'k6/http';
import { check, fail } from 'k6';
import { Rate } from 'k6/metrics';

// Métrica customizada para erros
export let errorRate = new Rate('errors');

// Base URL da API
export const HOST = __ENV.HOST || 'http://backend:9501';
export const BASE_URL = `${HOST}/accounts`;
export const LOGIN_URL = `${HOST}/auth/login`;

// Função de setup para autenticação
export function setup() {
    const loginRes = http.post(LOGIN_URL, JSON.stringify({
        email: 'user@example.com',
        password: 'secret123'
    }), {
        headers: { 'Content-Type': 'application/json' }
    });

    check(loginRes, { 'login ok': (r) => r.status === 200 });

    const token = loginRes.json().data.access_token;
    return { 
        token,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    };
}

// Função para criar uma conta
export function createAccount(headers, initialBalance = 10000) {
    const payload = JSON.stringify({
        name: `Test User ${Math.floor(Math.random() * 10000)}`,
        balance: initialBalance
    });

    const res = http.post(`${BASE_URL}`, payload, { headers });

    const success = check(res, {
        'Conta criada com sucesso (201)': r => r.status === 201 || r.status === 200
    });

    if (!success) {
        fail(`Falha ao criar conta: status ${res.status} body ${res.body}`);
    }

    return res.json('data.id');
}

// Função auxiliar para datas (dias à frente ou atrás)
export function formatDate(daysOffset = 0) {
    const d = new Date();
    d.setDate(d.getDate() + daysOffset);
    return d.toISOString().split('T')[0] + ' 00:00';
}

// Função auxiliar para horas (adiciona horas à data atual)
export function formatHours(hours) {
    const date = new Date();
    date.setHours(date.getHours() + hours);
    return date.toISOString();
}

// Função para validar respostas da API
export function validateResponse(res, body, testCase) {
    if (testCase.expectSuccess) {
        const success = check(res, { 'Sucesso esperado (2xx)': r => r.status >= 200 && r.status < 300 });
        console.log(success ? `\x1b[32m[SUCCESS]\x1b[0m ${testCase.desc}` : `\x1b[31m[FAIL]\x1b[0m ${testCase.desc}`);
        if (!success) errorRate.add(1);
    } else {
        let expectedStatus = 400;
        if (testCase.expectedError === 'ACCOUNT_NOT_FOUND') expectedStatus = 404;
        if (testCase.expectedError === 'INSUFFICIENT_BALANCE') expectedStatus = 409;
        if (testCase.expectedError === 'INTERNAL_ERROR') expectedStatus = 500;

        const statusCheck = check(res, { [`Erro esperado (${expectedStatus})`]: r => r.status === expectedStatus });
        const codeCheck = testCase.expectedError
            ? check(body, { 
                ["Código de erro esperado: " + testCase.expectedError]: b => b.error && b.error.error_code === testCase.expectedError 
            })
            : true;

        const passed = statusCheck && codeCheck;
        console.log(passed ? `\x1b[32m[SUCCESS]\x1b[0m ${testCase.desc}` : `\x1b[31m[FAIL]\x1b[0m ${testCase.desc}`);
        console.log(`\x1b[34m[INFO]\x1b[0m ${JSON.stringify(body)}`);
        if (!passed) errorRate.add(1);
    }
}
