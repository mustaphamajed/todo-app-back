<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
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
                'status' => 'active',
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
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:255',
            ]);

            $task = Task::findOrFail($id);

            $task->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
            ]);
            $task->load('user');

            return response()->json(['message' => 'Task updated successfully', 'task' => $task], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function markAsCompleted($id)
    {
        try {

            $task = Task::findOrFail($id);
            $task->update([
                'status' => 'completed',
            ]);
            $task->load('user');

            return response()->json(['message' => 'Task updated successfully', 'task' => $task], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Task not found'], 404);
        }
    }
    public function assignTaskToUser(Request $request, $id)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $task = Task::findOrFail($id);
            $task->update([
                'user_id' => $request->input('user_id'),
                'status' => 'pending',
            ]);
            $task->load('user');
            return response()->json(['message' => 'Task assigned to user successfully', 'task' => $task], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Task not found'], 404);
        }
    }
}
