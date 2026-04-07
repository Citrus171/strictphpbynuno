<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UdemyTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final readonly class UdemyTaskController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $tasks = UdemyTask::with('project')->get();

        return response()->json($tasks, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:udemy_projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = UdemyTask::create($validator->validated());

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $task = UdemyTask::with('project')->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($task, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $task = UdemyTask::with('project')->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:udemy_projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->update($validator->validated());

        return response()->json(['message' => 'Task updated successfully', 'task' => $task], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $task = UdemyTask::with('project')->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}
