<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/**
 * 期限切れSanctumトークンの自動削除（毎日1回実行）
 *
 * 本番環境ではサーバーのcronに以下を追加すること：
 * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('sanctum:prune-expired --hours=24')->daily();
