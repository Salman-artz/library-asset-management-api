<?php

namespace App\Mail;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Borrowing $borrowing
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PEMBERITAHUAN: Koleksi Perpustakaan Melewati Jatuh Tempo',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: <<<'HTML'
            <h2>Yth. {{ $borrowing->user->name }},</h2>
            <p><strong>Koleksi yang Anda pinjam telah melewati batas waktu pengembalian.</strong></p>
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
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Hari Terlambat</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ now()->diffInDays(\Carbon\Carbon::parse($borrowing->due_date)) }} hari</td>
                </tr>
            </table>
            <p>Segera kembalikan koleksi dan lunasi denda yang terkumpul di perpustakaan.</p>
            <p>Terima kasih.</p>
            HTML,
        );
    }
}

