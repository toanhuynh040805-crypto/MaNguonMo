<?php

namespace App\Providers;

use App\View\Composers\NavigationComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.app', NavigationComposer::class);
        View::composer('layouts.app', fn ($view) => $view->with(
            'unreadNotificationCount',
            auth()->user()?->unreadNotifications()->count() ?? 0,
        ));

        // Vietnamese password reset email (default notification is English).
        ResetPassword::toMailUsing(function ($user, string $token): MailMessage {
            $url = route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]);

            return (new MailMessage)
                ->subject('OpenShop | Đặt lại mật khẩu')
                ->greeting('Xin chào ' . $user->name . ',')
                ->line('Bạn nhận được email này vì có yêu cầu đặt lại mật khẩu cho tài khoản OpenShop.')
                ->action('Đặt lại mật khẩu', $url)
                ->line('Liên kết có hiệu lực trong ' . config('auth.passwords.users.expire') . ' phút.')
                ->line('Nếu bạn không yêu cầu, hãy bỏ qua email này; mật khẩu sẽ không thay đổi.')
                ->salutation('OpenShop');
        });
    }
}
