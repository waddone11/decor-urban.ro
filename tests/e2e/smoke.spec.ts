import { test, expect } from '@playwright/test';

const ADMIN_EMAIL = 'admin@decor-urban.ro';
const ADMIN_PASSWORD = 'DecorAdmin2026!';

test.describe('Decor Urban — Faza 0 smoke', () => {
  test('homepage returns 200 and shows the placeholder', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.status()).toBe(200);

    await expect(page.getByRole('heading', { name: 'Decor Urban' })).toBeVisible();
    await expect(page.getByText('Faza 0 — stack ready')).toBeVisible();
  });

  test('homepage Livewire counter is interactive', async ({ page }) => {
    await page.goto('/');

    const button = page.getByRole('button', { name: /Livewire test/ });
    await expect(button).toContainText('apăsat de 0 ori');

    await button.click();
    await expect(button).toContainText('apăsat de 1 ori');

    await button.click();
    await expect(button).toContainText('apăsat de 2 ori');
  });

  test('Filament admin login form renders', async ({ page }) => {
    const response = await page.goto('/admin/login');
    expect(response?.status()).toBe(200);

    await expect(page.getByLabel(/E-?mail/i)).toBeVisible();
    await expect(page.locator('input[type="password"]')).toBeVisible();
  });

  test('admin can log in and reach the dashboard', async ({ page }) => {
    await page.goto('/admin/login');

    await page.getByLabel(/E-?mail/i).fill(ADMIN_EMAIL);
    await page.locator('input[type="password"]').fill(ADMIN_PASSWORD);
    await page.locator('input[type="password"]').press('Enter');

    await page.waitForURL('**/admin');
    // Filament dashboard heading — Romanian locale renders "Panoul de control".
    await expect(
      page.getByRole('heading', { name: /Panoul de control|Dashboard/ })
    ).toBeVisible();
  });
});
