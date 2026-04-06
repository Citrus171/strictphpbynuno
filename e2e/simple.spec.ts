import { expect, test } from 'playwright/test';

test('トップページが表示されること', async ({ page }) => {
    const response = await page.goto('/');

    expect(response).toBeTruthy();
    expect(response?.ok()).toBe(true);
    await expect(page).toHaveURL(/\/$/);
    await expect(page.locator('body')).toContainText('Laravel');
});
