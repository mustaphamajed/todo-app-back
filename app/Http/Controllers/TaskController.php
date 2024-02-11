<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    // Retrieve all tasks
    public function index()
    {
        $tasks = Task::with('user')->get();

        return response()->json(['tasks' => $tasks], 200);
    }

    // Retrieve a specific task by ID
    public function show($id)
    {
        $task = Task::findOrFail($id);

        return response()->json(['task' => $task], 200);
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:255',
            ]);

            // Create a new task
            $task = Task::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'status' => 'pending',
            ]);

            return response()->json(['message' => 'Task created successfully', 'task' => $task], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,completed',
            ]);

            $task = Task::findOrFail($id);
            // Check if the authenticated user is the owner of the task
            if (auth()->id() !== $task->user_id) {
                return response()->json(['message' => 'Unauthorized. You do not have permission to update this task.'], 403);
            }

            $task->update([
                'status' => $request->input('status'),
            ]);

            return response()->json(['message' => 'Task updated successfully', 'task' => $task], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Task not found'], 404);
        }
    }
}
