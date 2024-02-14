<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    /**
     * Get a list of tasks with optional sorting.
     */
    public function index(Request $request)
    {
        try {
            // Get the sorting field from the request
            $sortField = $request->query('sort');

            // Initialize the task query with user relationships
            $query = Task::with('user');

            // Get the authenticated user
            $user = Auth::user();

            // Apply sorting based on the specified field
            if ($sortField === 'user_name') {
                $query->leftJoin('users', 'tasks.user_id', '=', 'users.id')
                    ->orderBy('users.name', 'desc')
                    ->select('tasks.*');
            } elseif ($sortField === 'status') {
                $query->orderBy('status', 'desc');
            } elseif ($sortField === 'received') {
                $query->where('tasks.user_id', $user->id)->orderBy('created_at', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Retrieve tasks based on the query
            $tasks = $query->get();

            Log::info('Tasks retrieved successfully');

            return response()->json(['tasks' => $tasks], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving tasks: ' . $e->getMessage());


            return response()->json(['message' => 'Error retrieving tasks'], 500);
        }
    }


    /**
     * Get details of a specific task by ID.
     */

    public function show($id)
    {
        try {
            // Find the task by ID
            $task = Task::findOrFail($id);

            Log::info('Task retrieved successfully', ['task' => $task->id]);

            return response()->json(['task' => $task], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving task: ' . $e->getMessage());

            return response()->json(['message' => 'Error retrieving task'], 404);
        }
    }

    /**
     * Create a new task.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
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

            Log::info('Task created successfully', ['task' => $task->id]);

            return response()->json(['message' => 'Task created successfully', 'task' => $task], 201);
        } catch (ValidationException $e) {
            Log::error('Validation failed during task creation', ['errors' => $e->errors()]);

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage());

            return response()->json(['message' => 'Error creating task'], 500);
        }
    }

    /**
     * Update an existing task.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request data
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:255',
            ]);

            // Find the task by ID
            $task = Task::findOrFail($id);

            // Update task details
            $task->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
            ]);

            // Reload user relationship for the updated task
            $task->load('user');

            Log::info('Task updated successfully', ['task' => $task->id]);

            return response()->json(['message' => 'Task updated successfully', 'task' => $task], 200);
        } catch (ValidationException $e) {
            Log::error('Validation failed during task update', ['errors' => $e->errors()]);

            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating task: ' . $e->getMessage());

            return response()->json(['message' => 'Error updating task'], 500);
        }
    }
    /**
     * Mark a task as completed.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsCompleted($id)
    {
        try {
            // Find the task by ID
            $task = Task::findOrFail($id);

            // Update task status to completed
            $task->update([
                'status' => 'completed',
            ]);

            // Reload user relationship for the updated task
            $task->load('user');

            Log::info('Task marked as completed', ['task' => $task->id]);

            return response()->json(['message' => 'Task marked as completed', 'task' => $task], 200);
        } catch (\Exception $e) {
            Log::error('Error marking task as completed: ' . $e->getMessage());

            return response()->json(['message' => 'Task not found'], 404);
        }
    }

    /**
     * Assign a task to a specific user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignTaskToUser(Request $request, $id)
    {
        try {
            // Validate the request data
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
            // Find the task by ID
            $task = Task::findOrFail($id);
            // Update task with assigned user and status
            $task->update([
                'user_id' => $request->input('user_id'),
                'status' => 'pending',
            ]);
            $task->load('user');
            Log::info('Task assigned to user successfully', ['task' => $task->id]);
            return response()->json(['message' => 'Task assigned to user successfully', 'task' => $task], 200);
        } catch (ValidationException $e) {
            Log::error('Validation failed during task assignment', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error assigning task to user: ' . $e->getMessage());
            return response()->json(['message' => 'Task not found'], 404);
        }
    }
}
