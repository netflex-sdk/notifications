<?php

namespace Netflex\Notifications\Transport;

use Swift_Attachment;
use Swift_Mime_SimpleMessage;

use Netflex\API\Facades\API;
use Netflex\Foundation\Variable;

use Illuminate\Mail\Transport\Transport;

class NotificationsTransport extends Transport
{
  /**
   * {@inheritdoc}
   */
  public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
  {
    $this->beforeSendPerformed($message);

    $replyTo = null;
    
    /** @var array|string|null */
    $replyToHeader = $message->getReplyTo();

    if (is_array($replyToHeader)) {
      $address = array_key_first($replyToHeader);
      $name = $replyToHeader[$address];

      $replyTo = $name ? "{$name} <{$address}>" : $address;      
    }
    
    if (is_string($replyToHeader)) {
      $replyTo = $replyToHeader;
    }

    $attachments = collect($message->getChildren())
      ->filter(function ($child) {
        return $child instanceof Swift_Attachment;
      })
      ->map(function (Swift_Attachment $attachment) {
        return [
          'filename' => $attachment->getFilename(),
          'link' => 'data://' . $attachment->getContentType() . ';base64,' . base64_encode($attachment->getBody())
        ];
      })
      ->values()
      ->toArray();

    $response = API::post('relations/notifications', array_filter([
      'subject' => $message->getSubject(),
      'to' => $this->getTo($message),
      'from' => $this->getFrom($message),
      'reply_to' => $replyTo,
      'body' => base64_encode($message->getBody()),
      'use_blank_template' => true,
      'attachments' => $attachments
    ]));

    $message->getHeaders()->addTextHeader('X-Notification-ID', $response->notification_id);

    $this->sendPerformed($message);

    return $this->numberOfRecipients($message);
  }

  /**
   * @param Swift_Mime_SimpleMessage $message
   * @return array
   */
  public function getTo(Swift_Mime_SimpleMessage $message)
  {
    $recipients = $message->getTo();
    return collect($recipients)
      ->map(function ($display, $address) {
        return [
          'mail' => $address,
          'name' => $display
        ];
      })->values()
      ->toArray();
  }

  /**
   * @param Swift_Mime_SimpleMessage $message
   * @return array
   */
  public function getFrom(Swift_Mime_SimpleMessage $message)
  {
    $from = collect($message->getFrom())
      ->slice(0, 1)
      ->map(function ($display, $address) {
        return [
          'mail' => $address ?? Variable::get('mail_sender_mail'),
          'name' => $display ?? Variable::get('mail_sender_name')
        ];
      })->first();

    if ($from) {
      return $from;
    }

    return [
      'mail' => Variable::get('mail_sender_mail'),
      'name' => Variable::get('mail_sender_name')
    ];
  }
}
