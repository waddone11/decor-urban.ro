import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright config — Decor Urban smoke tests.
 *
 * The app is served by the Docker stack (`docker compose up -d --wait`),
 * so we just point Playwright at it. Override the target with PW_BASE_URL.
 */
const baseURL = process.env.PW_BASE_URL ?? 'http://localhost:8080';

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: process.env.CI ? 'list' : [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
