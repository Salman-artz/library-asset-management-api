<?php

namespace App\Jobs;

use App\Mail\BorrowingReminderMail;
use App\Mail\OverdueNotificationMail;
use App\Models\Borrowing;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBorrowingReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send H-1 reminders (due_date is tomorrow)
        $this->sendRemindersForDueDate(Carbon::tomorrow()->toDateString());

        // Send overdue notifications (due_date is before today and not returned)
        $this->sendOverdueNotifications();

        Log::info('Borrowing reminder job completed.');
    }

    private function sendRemindersForDueDate(string $dueDate): void
    {
        $borrowings = Borrowing::query()
            ->with(['user', 'collection'])
            ->where('status', 'borrowed')
            ->whereDate('due_date', $dueDate)
            ->get();

        foreach ($borrowings as $borrowing) {
            try {
                Mail::to($borrowing->user->email)
                    ->send(new BorrowingReminderMail($borrowing));

                Log::info("Reminder sent to {$borrowing->user->email} for borrowing #{$borrowing->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send reminder for borrowing #{$borrowing->id}: {$e->getMessage()}");
            }
        }
    }

    private function sendOverdueNotifications(): void
    {
        $overdueBorrowings = Borrowing::query()
            ->with(['user', 'collection'])
            ->where('status', 'borrowed')
            ->whereDate('due_date', '<', Carbon::today()->toDateString())
            ->get();

        foreach ($overdueBorrowings as $borrowing) {
            // Mark as overdue in database
            $borrowing->update(['status' => 'overdue']);

            try {
                Mail::to($borrowing->user->email)
                    ->send(new OverdueNotificationMail($borrowing));

                Log::info("Overdue notification sent to {$borrowing->user->email} for borrowing #{$borrowing->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send overdue notification for borrowing #{$borrowing->id}: {$e->getMessage()}");
            }
        }
    }
}

