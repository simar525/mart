<?php

namespace App\Jobs\Reviewer;

use App\Classes\SendMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReviewerItemPendingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reviewer;
    public $item;

    public function __construct($reviewer, $item)
    {
        $this->reviewer = $reviewer;
        $this->item = $item;
    }

    public function handle()
    {
        $reviewer = $this->reviewer;
        $item = $this->item;

        SendMail::send($reviewer->email, 'reviewer_item_pending', [
            'author_username' => $item->author->username,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_preview_image' => '<img src="' . $item->getPreviewImageLink() . '" width="100%"/>',
            'review_link' => route('reviewer.items.review', $item->id),
            'website_name' => @settings('general')->site_name,
        ]);
    }
}