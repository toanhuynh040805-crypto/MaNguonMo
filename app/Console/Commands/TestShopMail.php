<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestShopMail extends Command
{
    protected $signature = 'openshop:mail-test {--to= : Email nhận thử; mặc định là địa chỉ người gửi}';

    protected $description = 'Send one SMTP test email for OpenShop (no order needed)';

    public function handle(): int
    {
        if (config('mail.default') !== 'smtp') {
            $this->error('MAIL_MAILER chưa là smtp. Chế độ log không gửi email ra Gmail.');

            return self::FAILURE;
        }

        $recipient = $this->option('to') ?: config('mail.from.address');
        if (! is_string($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('Địa chỉ nhận không hợp lệ.');

            return self::FAILURE;
        }

        try {
            Mail::raw(
                'Đây là email thử nghiệm SMTP của OpenShop. Nếu bạn đọc được email này, cấu hình Gmail đã hoạt động.',
                fn ($message) => $message->to($recipient)->subject('OpenShop | Kiểm tra gửi email SMTP')
            );
            $this->info('Laravel SMTP đã chấp nhận gửi email thử nghiệm.');
            $this->line('Kiểm tra Hộp thư đến hoặc Thư rác của người nhận.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Không gửi được email. Hãy kiểm tra MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD và kết nối SMTP.');
            $this->line('Loại lỗi: ' . $exception::class);
            // Do not output exception details that may contain SMTP credentials.
            return self::FAILURE;
        }
    }
}
