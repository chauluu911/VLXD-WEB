<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

class BaseSlackNotification extends Notification implements ShouldQueue {
    use Queueable;

    protected $content;
    protected $fields;

	/**
	 * Create a new notification instance.
	 *
	 * @param string $content
	 * @param array $fields
	 */
    public function __construct(string $content, array $fields = []) {
        $this->content = $content;
        $this->fields = $fields;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['slack'];
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array
     */
    public function viaQueues() {
        return [
            'mail' => 'mailQueue',
            'slack' => 'slackQueue',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable) {
        return [
            //
        ];
    }

	/**
	 * Get the Slack representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return SlackMessage
	 */
	public function toSlack($notifiable) {
		return (new SlackMessage())
			->error()
			->from(config('app.name'))
			->image(public_path('images/logo.jpg'))
			->content($this->content)
			->attachment(function (SlackAttachment $attachment) {
				$attachment->fields($this->fields);
			});
	}
}
