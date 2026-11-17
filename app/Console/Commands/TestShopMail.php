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
            $this->error('Gmail SMTP chưa chấp nhận email.');
            $this->line('Loại lỗi: ' . $exception::class);
            $this->showSafeDiagnosis($exception);

            return self::FAILURE;
        }
    }

    /**
     * Only reveal a safe error category. Full SMTP exception/debug output
     * can contain server responses and authentication data; never print it.
     */
    private function showSafeDiagnosis(Throwable $error): void
    {
        $message = '';
        $current = $error;
        for ($i = 0; $i < 6 && $current !== null; $i++) {
            $message .= ' ' . strtolower($current->getMessage());
            $current = $current->getPrevious();
        }

        if (preg_match('/certificate|verify failed|unable to get local issuer|self.signed|peer certificate|openssl|ssl operation failed/u', $message)) {
            $this->warn('Chan doan: PHP khong xac thuc duoc chung chi TLS/SSL. Kiem tra openssl.cafile trong php.ini va CA bundle tin cay.');
        } elseif (preg_match('/535|534|5[.]7[.]8|authentication|authenticate|authenticator|invalid credentials|password not accepted/u', $message)) {
            $this->warn('Chan doan: Gmail tu choi xac thuc SMTP. Kiem tra dung App Password 16 ky tu, Gmail cua ung dung va Xac minh 2 buoc.');
        } elseif (preg_match('/starttls|tls handshake|tls connection|unable to connect with tls/u', $message)) {
            $this->warn('Chan doan: STARTTLS gap loi. Kiem tra TLS, chung chi may chu va MAIL_SCHEME=smtp voi MAIL_PORT=587.');
        } elseif (preg_match('/timed out|timeout|connection refused|network is unreachable|getaddrinfo|could not connect|connection could not be established/u', $message)) {
            $this->warn('Chan doan: Khong ket noi on dinh den SMTP. Thu Test-NetConnection smtp.gmail.com -Port 587.');
        } else {
            $this->warn('Chan doan: Chua xac dinh. Vui long kiem tra php --ini, openssl.cafile va trang thai bao ve tai khoan Google.');
        }

        $this->line('Chi hien phan loai loi; KHONG in mat khau ung dung hoac phan hoi SMTP chi tiet.');
    }

}
