/// <reference types="node" />
import { defineConfig } from 'playwright/test';

const defaultBaseURL = 'http://127.0.0.1:8000';
const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? defaultBaseURL;

export default defineConfig({
    testDir: './e2e',
    use: {
        baseURL,
    },
    webServer: process.env.PLAYWRIGHT_BASE_URL
        ? undefined
        : {
              command: 'php artisan serve --host=127.0.0.1 --port=8000',
              url: defaultBaseURL,
              reuseExistingServer: true,
          },
});
