<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

final class BrandedEmailMessage
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(string $view, array $data, string $subject): MailMessage
    {
        $data['logoSource'] = 'cid:alumni-tracer-email-logo@alumnitracer';
        $data['mascotSource'] = 'cid:alumni-tracer-email-mascot@alumnitracer';

        return (new MailMessage)
            ->subject($subject)
            ->view($view, $data)
            ->withSymfonyMessage(function (Email $email): void {
                foreach ([
                    'alumni-tracer-email-logo@alumnitracer' => 'alumni-tracer-email-logo.png',
                    'alumni-tracer-email-mascot@alumnitracer' => 'alumni-tracer-email-mascot.png',
                ] as $contentId => $filename) {
                    $email->addPart(
                        DataPart::fromPath(public_path("images/{$filename}"), $filename, 'image/png')
                            ->asInline()
                            ->setContentId($contentId),
                    );
                }
            });
    }
}
