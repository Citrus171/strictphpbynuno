<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('titleを送信した時、Udemyプロジェクトを作成できること', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('udemy-projects.store'), [
        'title' => 'テストプロジェクト',
        'description' => '説明',
        'due_date' => '2026-05-01',
    ]);

    $response->assertCreated()
        ->assertJsonPath('title', 'テストプロジェクト');

    $this->assertDatabaseHas('udemy_projects', [
        'title' => 'テストプロジェクト',
        'description' => '説明',
        'due_date' => '2026-05-01',
    ]);
});

it('titleが未指定の時、バリデーションエラーになること', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('udemy-projects.store'), [
        'description' => '説明',
        'due_date' => '2026-05-01',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});
