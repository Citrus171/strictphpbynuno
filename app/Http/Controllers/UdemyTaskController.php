<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UdemyTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final readonly class UdemyTaskController
{
    public function index(): JsonResponse
    {
        $tasks = UdemyTask::with('project')->get();

        return response()->json($tasks, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'exists:udemy_projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'due_date' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var array<string, mixed> $validated */
        $validated = $validator->validated();
        $task = UdemyTask::query()->create($validated);

        return response()->json($task, 201);
    }

    public function show(string $id): JsonResponse
    {
        $task = UdemyTask::with('project')->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($task, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $task = UdemyTask::with('project')->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'exists:udemy_projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'due_date' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var array<string, mixed> $validated */
        $validated = $validator->validated();
        $task->update($validated);

        return response()->json(['message' => 'Task updated successfully', 'task' => $task], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $task = UdemyTask::query()->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}
