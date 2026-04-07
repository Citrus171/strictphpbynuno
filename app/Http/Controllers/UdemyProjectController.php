<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UdemyProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final readonly class UdemyProjectController
{
    public function index(): JsonResponse
    {
        return response()->json(UdemyProject::query()->get(), 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var array<string, mixed> $validated */
        $validated = $validator->validated();
        $project = UdemyProject::query()->create($validated);

        return response()->json($project, 201);
    }

    public function show(string $id): JsonResponse
    {
        $project = UdemyProject::query()->find($id);
        if (! $project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        return response()->json($project, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $project = UdemyProject::query()->find($id);
        if (! $project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var array<string, mixed> $validated */
        $validated = $validator->validated();
        $project->update($validated);

        return response()->json(['message' => 'Project updated successfully', 'project' => $project], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $project = UdemyProject::query()->find($id);
        if (! $project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully'], 200);
    }
}
