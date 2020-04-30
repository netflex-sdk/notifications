<?php

namespace Netflex\Notifications\Providers;

use Illuminate\Mail\MailServiceProvider as ServiceProvider;
use Netflex\Notifications\Transport\NotificationsTransport;

class NotifcationsServiceProvider extends ServiceProvider
{
  public function boot()
  {
    if ($this->app->has('mail.manager')) {
      $this->app->make('mail.manager')
        ->extend('netflex', function () {
          return new NotificationsTransport;
        });
    }
  }
}
