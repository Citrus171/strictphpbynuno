---
name: tdd
description: Test-driven development with red-green-refactor loop. Use when user wants to build features or fix bugs using TDD, mentions "red-green-refactor", wants integration tests, or asks for test-first development.
---

# Test-Driven Development

## Philosophy

**Core principle**: Tests should verify behavior through public interfaces, not implementation details. Code can change entirely; tests shouldn't.

**Good tests** are integration-style: they exercise real code paths through public APIs. They describe _what_ the system does, not _how_ it does it. A good test reads like a specification - "user can checkout with valid cart" tells you exactly what capability exists. These tests survive refactors because they don't care about internal structure.

**Bad tests** are coupled to implementation. They mock internal collaborators, test private methods, or verify through external means (like querying a database directly instead of using the interface). The warning sign: your test breaks when you refactor, but behavior hasn't changed. If you rename an internal function and tests fail, those tests were testing implementation, not behavior.

See [tests.md](tests.md) for examples and [mocking.md](mocking.md) for mocking guidelines.

## Anti-Pattern: Horizontal Slices

**DO NOT write all tests first, then all implementation.** This is "horizontal slicing" - treating RED as "write all tests" and GREEN as "write all code."

This produces **crap tests**:

- Tests written in bulk test _imagined_ behavior, not _actual_ behavior
- You end up testing the _shape_ of things (data structures, function signatures) rather than user-facing behavior
- Tests become insensitive to real changes - they pass when behavior breaks, fail when behavior is fine
- You outrun your headlights, committing to test structure before understanding the implementation

**Correct approach**: Vertical slices via tracer bullets. One test → one implementation → repeat. Each test responds to what you learned from the previous cycle. Because you just wrote the code, you know exactly what behavior matters and how to verify it.

```
WRONG (horizontal):
  RED:   test1, test2, test3, test4, test5
  GREEN: impl1, impl2, impl3, impl4, impl5

RIGHT (vertical):
  RED→GREEN: test1→impl1
  RED→GREEN: test2→impl2
  RED→GREEN: test3→impl3
  ...
```

## Workflow

### 1. Planning

Before writing any code:

- [ ] Confirm with user what interface changes are needed
- [ ] Confirm with user which behaviors to test (prioritize)
- [ ] Identify opportunities for [deep modules](deep-modules.md) (small interface, deep implementation)
- [ ] Design interfaces for [testability](interface-design.md)
- [ ] List the behaviors to test (not implementation steps)
- [ ] Get user approval on the plan

Ask: "What should the public interface look like? Which behaviors are most important to test?"

**You can't test everything.** Confirm with the user exactly which behaviors matter most. Focus testing effort on critical paths and complex logic, not every possible edge case.

<!-- 2026-03-24 拡張: テストケース名の命名規則を追加 -->
**テストケース名の命名規則**: 「〜の時、〜であること」形式の日本語で、振る舞いが明確にわかるように書く。

### 2. Tracer Bullet

Write ONE test that confirms ONE thing about the system:

```
RED:   Write test for first behavior → test fails
GREEN: Write minimal code to pass → test passes
```

This is your tracer bullet - proves the path works end-to-end.

### 3. Incremental Loop

For each remaining behavior:

```
RED:   Write next test → fails
GREEN: Minimal code to pass → passes
```

Rules:

- One test at a time
- Only enough code to pass current test
- Don't anticipate future tests
- Keep tests focused on observable behavior

### 4. Refactor

After all tests pass, look for [refactor candidates](refactoring.md):

- [ ] Extract duplication
- [ ] Deepen modules (move complexity behind simple interfaces)
- [ ] Apply SOLID principles where natural
- [ ] Consider what new code reveals about existing code
- [ ] Run tests after each refactor step

**Never refactor while RED.** Get to GREEN first.

## Checklist Per Cycle

```
[ ] Test describes behavior, not implementation
[ ] Test uses public interface only
[ ] Test would survive internal refactor
[ ] Code is minimal for this test
[ ] No speculative features added
```

---
<!-- 2026-03-24 拡張: テストデータライフサイクルとテスト失敗時の判断フローを追加 -->

## テストデータのライフサイクル管理

詳細なパターンは [test-lifecycle.md](test-lifecycle.md) を参照。

### Pest（Laravel）

このプロジェクトのテストは **Pest** を使用する。PHPUnitのクラスベース記法は使わない。

`uses(RefreshDatabase::class)` をファイル先頭またはテスト内で宣言し、各テスト後にトランザクションがロールバックされ、DB がクリーンな状態に戻る。

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('有効なカートの時、注文が確定すること', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/orders', [...]);

    $response->assertCreated();
});
```

## テスト失敗時の判断フロー

テストコードを実装・実行して失敗した場合は以下で判断する：

- **テストコードの誤り** → テストコードを修正する
- **実装または仕様の誤り** → 該当テストをスキップし、ユーザーに確認を求める

## 完了後のアクション

  実装とテストが全て完了したら、必ずPRを作成すること。

## PRレビュー対応後のアクション

- PRを作成後は、実行結果を確定するため、GitHub Actions の run 状態と各ジョブ結論を取得して要点だけ共有すること。
- PRレビューを確認して、レビュー対応の実装とテストが全て完了したら、プッシュして必ずコメントスレッドに返信すること。
- 返信は反映されているかページ確認すること。

## PRの要件が満たされた後のアクション
- PRの要件が満たされていることを確認して、対象Issueは完了としてクローズし、トレース用コメントを残すこと。
- マージを実行し、完了後に main 側への反映を確認すること。
