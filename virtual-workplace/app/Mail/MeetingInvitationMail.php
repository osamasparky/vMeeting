<?php

namespace App\Mail;

use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Meeting $meeting;
    public User $recipient;
    public string $joinUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Meeting $meeting, User $recipient, string $joinUrl)
    {
        $this->meeting = $meeting;
        $this->recipient = $recipient;
        $this->joinUrl = $joinUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $prefix = $this->meeting->project ? "[{$this->meeting->project->name}] " : "";
        return new Envelope(
            subject: "📅 {$prefix}Meeting Scheduled: {$this->meeting->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.meeting-invitation',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
