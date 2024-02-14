<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    /**
     * Get statistics based on the specified timeframe.
     */
    public function getStats(Request $request)
    {
        try {
            // Get the timeframe from the request, default is 'daily'
            $timeframe = $request->input('timeframe', 'daily');
            // Calculate the start and end date based on the timeframe
            $startDate = $this->getStartDate($timeframe);
            $endDate = $this->getEndDate($timeframe, $startDate);

            // Retrieve completed tasks within the specified timeframe
            $completedTasks = $this->getCompletedTasks($startDate, $endDate);

            // Calculate statistics based on completed tasks
            $completedTasksCount = $completedTasks->count();
            $totalTasksCount = $this->getTotalTasksCount($startDate, $endDate);
            $totalCompletionTime = $this->getTotalCompletionTime($completedTasks);

            // Calculate average completion time per task
            $averageTime = $completedTasksCount > 0 ? $totalCompletionTime / $completedTasksCount : 0;

            // Get the total count of tasks assigned to users
            $totalAssignedTasksCount = $this->getTotalAssignedTasksCount($startDate, $endDate);

            return response()->json([
                'total_tasks_count' => $totalTasksCount,
                'completed_tasks_count' => $completedTasksCount,
                'average_completion_time' => $averageTime,
                'total_assigned_tasks_count' => $totalAssignedTasksCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating statistics: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Get the total count of tasks assigned to users within a specified timeframe.
     */

    private function getTotalAssignedTasksCount($startDate, $endDate)
    {
        return Task::whereNotNull('user_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get completed tasks within a specified timeframe.
     */
    private function getCompletedTasks($startDate, $endDate)
    {
        return Task::where('status', 'completed')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Get the total count of tasks created within a specified timeframe.
     */
    private function getTotalTasksCount($startDate, $endDate)
    {
        return Task::whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }


    /**
     * Calculate the total completion time of completed tasks.
     */
    private function getTotalCompletionTime($completedTasks)
    {
        $totalCompletionTime = 0;

        foreach ($completedTasks as $task) {
            $totalCompletionTime += $task->updated_at->diffInHours($task->created_at);
        }

        return $totalCompletionTime;
    }

    /**
     * Get the start date based on the specified timeframe.
     */
    private function getStartDate($timeframe)
    {
        switch ($timeframe) {
            case 'daily':
                return Carbon::today();
            case 'weekly':
                return Carbon::now()->startOfWeek();
            case 'monthly':
                return Carbon::now()->startOfMonth();
            default:
                return Carbon::today();
        }
    }


    /**
     * Get the end date based on the specified timeframe.
     */

    private function getEndDate($timeframe, $startDate)
    {
        switch ($timeframe) {
            case 'daily':
                return $startDate->copy()->endOfDay();
            case 'weekly':
                return $startDate->copy()->endOfWeek();
            case 'monthly':
                return $startDate->copy()->endOfMonth();
            default:
                return $startDate->copy()->endOfDay();
        }
    }
}
