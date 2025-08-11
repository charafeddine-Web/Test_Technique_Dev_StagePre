<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Nouvelle tâche créée')
                    ->greeting('Bonjour ' . $notifiable->full_name . ' !')
                    ->line('Une nouvelle tâche a été créée avec succès.')
                    ->line('Titre: ' . $this->task->title)
                    ->line('Description: ' . $this->task->description)
                    ->line('Statut: ' . $this->task->status)
                    ->action('Voir la tâche', url('/tasks/' . $this->task->id))
                    ->line('Merci d\'utiliser notre application !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'task_description' => $this->task->description,
            'task_status' => $this->task->status,
            'message' => 'Une nouvelle tâche "' . $this->task->title . '" a été créée avec succès !',
            'type' => 'task_created',
            'created_at' => now()
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'task_description' => $this->task->description,
            'task_status' => $this->task->status,
            'message' => 'Une nouvelle tâche "' . $this->task->title . '" a été créée avec succès !',
            'type' => 'task_created',
            'created_at' => now()->toISOString()
        ];
    }
}
