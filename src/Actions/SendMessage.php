<?php

namespace Filament\TeamChat\Actions;

use Filament\TeamChat\Models\Attachment;
use Filament\TeamChat\Models\Conversation;
use Filament\TeamChat\Models\Message;
use Filament\TeamChat\Notifications\NewDirectMessageNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SendMessage
{
    /**
     * @param  array<UploadedFile>  $files
     */
    public function execute(Model $messageable, int|string $userId, string $body, ?int $parentId = null, array $files = []): Message
    {
        $bodyHtml = Str::markdown($body);

        $message = Message::create([
            'messageable_type' => $messageable->getMorphClass(),
            'messageable_id' => $messageable->getKey(),
            'user_id' => $userId,
            'parent_id' => $parentId,
            'body' => $body,
            'body_html' => $bodyHtml,
        ]);

        // Parse and store mentions, update body_html with styled spans
        $updatedHtml = app(ParseMentions::class)->execute($message, $bodyHtml);

        if ($updatedHtml !== $bodyHtml) {
            $message->updateQuietly(['body_html' => $updatedHtml]);
        }

        // Store file attachments
        foreach ($files as $file) {
            $path = $file->store(
                config('team-chat.uploads.directory', 'team-chat-attachments'),
                config('team-chat.uploads.disk', 'public'),
            );

            Attachment::create([
                'message_id' => $message->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        // Notify DM participants (excluding the sender)
        if ($messageable instanceof Conversation) {
            $message->load('user');

            $messageable->participants()
                ->where('user_id', '!=', $userId)
                ->get()
                ->each(fn (Model $participant) => $participant->notify(
                    new NewDirectMessageNotification($message, $messageable),
                ));
        }

        return $message;
    }
}
