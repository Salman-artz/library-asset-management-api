<?php

namespace App\Mail;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorrowingReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Borrowing $borrowing
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat: Jatuh Tempo Peminjaman Koleksi Perpustakaan',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: <<<'HTML'
            <h2>Yth. {{ $borrowing->user->name }},</h2>
            <p>Berikut adalah pengingat untuk peminjaman koleksi perpustakaan Anda:</p>
            <table style="border-collapse: collapse; width: 100%;">
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Koleksi</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $borrowing->collection->title }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Kode</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $borrowing->collection->code }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Tanggal Pinjam</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $borrowing->borrowed_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Jatuh Tempo</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($borrowing->due_date)->format('d/m/Y') }}</td>
                </tr>
            </table>
            <p>Harap kembalikan koleksi tepat waktu untuk menghindari denda keterlambatan.</p>
            <p>Terima kasih.</p>
            HTML,
        );
    }
}

