<?php

namespace App\Mail;

use App\Models\ProjectInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteProjectMember extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public ProjectInvite $invite)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join {$this->invite->project->name}",
            to: $this->invite->email,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invite-project-member',
            with: [
                'invite' => $this->invite,
                'project' => $this->invite->project,
                'inviter' => $this->invite->invitedBy,
                'acceptUrl' => route('invites.show', ['token' => $this->invite->token]),
            ],
        );
    }
}

