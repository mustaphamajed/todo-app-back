<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    //
    public function getStats(Request $request)
    {
        try {
            $timeframe = $request->input('timeframe', 'daily');
            $startDate = $this->getStartDate($timeframe);
            $endDate = $this->getEndDate($timeframe, $startDate);

            $completedTasks = $this->getCompletedTasks($startDate, $endDate);

            $completedTasksCount = $completedTasks->count();
            $totalTasksCount = $this->getTotalTasksCount($startDate, $endDate);
            $totalCompletionTime = $this->getTotalCompletionTime($completedTasks);

            $averageTime = $completedTasksCount > 0 ? $totalCompletionTime / $completedTasksCount : 0;

            $totalAssignedTasksCount = $this->getTotalAssignedTasksCount($startDate, $endDate);

            return response()->json([
                'total_tasks_count' => $totalTasksCount,
                'completed_tasks_count' => $completedTasksCount,
                'average_completion_time' => $averageTime,
                'total_assigned_tasks_count' => $totalAssignedTasksCount,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    private function getTotalAssignedTasksCount($startDate, $endDate)
    {
        return Task::whereNotNull('user_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }


    private function getCompletedTasks($startDate, $endDate)
    {
        return Task::where('status', 'completed')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get();
    }

    private function getTotalTasksCount($startDate, $endDate)
    {
        return Task::whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    private function getTotalCompletionTime($completedTasks)
    {
        $totalCompletionTime = 0;

        foreach ($completedTasks as $task) {
            $totalCompletionTime += $task->updated_at->diffInHours($task->created_at);
        }

        return $totalCompletionTime;
    }

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
