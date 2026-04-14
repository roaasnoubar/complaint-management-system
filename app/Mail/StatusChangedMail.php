<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $complainNumber,
        public string $title,
        public string $status
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Complaint Status Updated - ' . $this->complainNumber,
        );
    }

    public function content(): Content
    {
        $statusClass = match($this->status) {
            'In Progress' => 'progress',
            'Resolved'    => 'resolved',
            default       => 'pending',
        };

        return new Content(
            view: 'emails.status_changed',
            with: [
                'name'          => $this->name,
                'complainNumber'=> $this->complainNumber,
                'title'         => $this->title,
                'status'        => $this->status,
                'statusClass'   => $statusClass,
            ],
        );
    }
}