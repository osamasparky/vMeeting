<?php

namespace App\Mail;

use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Support\EmailTokens;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email copy of a task notification (assignment, comment, mention, status,
 * approval, due reminder). Rendered in the recipient's app locale.
 */
class TaskActivityMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WorkplaceNotification $notification,
        public string $recipientName,
        public string $actionUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->notification->title);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-activity',
            with: ['c' => EmailTokens::light()],
        );
    }
}
