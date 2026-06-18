<?php

namespace App\Http\Controllers;

use App\Support\RideWrench;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('public.home');
    }

    public function faq(): View
    {
        return view('public.faq');
    }

    public function legalNotice(): View
    {
        return view('public.legal-notice');
    }

    public function privacy(): View
    {
        return view('public.privacy');
    }

    public function feedback(Request $request): View
    {
        return view('public.feedback', [
            'user' => $request->user(),
            'recaptchaSiteKey' => (string) config('services.recaptcha.site_key'),
        ]);
    }

    public function feedbackSubmit(Request $request): RedirectResponse
    {
        $user = $request->user();

        $type = (string) $request->input('type', 'bug');
        $name = trim((string) $request->input('name', ''));
        $email = strtolower(trim((string) $request->input('email', '')));
        $message = trim((string) $request->input('message', ''));
        $pageUrl = trim((string) $request->input('page_url', ''));

        if (!in_array($type, ['bug', 'idea', 'question'], true)) {
            $type = 'bug';
        }

        if ($name === '' && $user) {
            $name = (string) ($user->name ?? '');
        }

        if ($email === '' && $user) {
            $email = (string) ($user->email ?? '');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect('/feedback')->with('error', 'emailInvalid');
        }

        if ($message === '') {
            return redirect('/feedback')->with('error', 'feedbackMessageRequired');
        }

        $recaptchaSiteKey = (string) config('services.recaptcha.site_key');
        $recaptchaSecretKey = (string) config('services.recaptcha.secret_key');

        if ($recaptchaSiteKey !== '' || $recaptchaSecretKey !== '') {
            $recaptchaToken = (string) $request->input('g-recaptcha-response', '');

            if (!$this->verifyRecaptcha($recaptchaToken, $request->ip())) {
                return redirect('/feedback')->with('error', 'recaptchaFailed');
            }
        }

        $to = (string) config('services.feedback.email');

        if ($to === '') {
            Log::error('Feedback email failed: config("services.feedback.email") is empty.');

            return redirect('/feedback')->with('error', 'feedbackFailed');
        }

        $typeLabel = __('feedback.type.' . $type);
        $subject = '[RideWrench] ' . $typeLabel;
        $submittedAt = RideWrench::formatDateTime(now()->toDateTimeString());

        $metaRows = [
            'Type' => $typeLabel,
            'Name' => $name !== '' ? $name : '-',
            'Email' => $email !== '' ? $email : '-',
            'Page URL' => $pageUrl !== '' ? $pageUrl : '-',
            'User ID' => (string) ($user->id ?? '-'),
            'Language' => App::getLocale(),
            'Submitted' => $submittedAt,
            'IP' => (string) ($request->ip() ?? '-'),
            'User agent' => (string) ($request->userAgent() ?? '-'),
        ];

        $body = '<!doctype html>';
        $body .= '<html lang="en">';
        $body .= '<head>';
        $body .= '<meta charset="utf-8">';
        $body .= '<title>' . e($subject) . '</title>';
        $body .= '</head>';
        $body .=
            '<body style="margin:0;padding:0;background:#f4f6f8;color:#111827;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.5;">';
        $body .= '<div style="max-width:720px;margin:0 auto;padding:24px;">';

        $body .= '<div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">';

        $body .= '<div style="padding:20px 24px;background:#0f141b;color:#ffffff;">';
        $body .= '<div style="font-size:13px;opacity:.75;margin-bottom:4px;">RideWrench Feedback</div>';
        $body .= '<h1 style="margin:0;font-size:22px;line-height:1.25;">' . e($typeLabel) . '</h1>';
        $body .= '</div>';

        $body .= '<div style="padding:24px;">';

        $body .= '<h2 style="margin:0 0 12px;font-size:16px;">Message</h2>';
        $body .=
            '<div style="white-space:pre-wrap;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px;margin-bottom:24px;">';
        $body .= e($message);
        $body .= '</div>';

        $body .= '<h2 style="margin:0 0 12px;font-size:16px;">Details</h2>';
        $body .=
            '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;">';

        foreach ($metaRows as $label => $value) {
            $body .= '<tr>';
            $body .=
                '<td style="width:140px;padding:8px 10px;border-top:1px solid #e5e7eb;color:#6b7280;font-weight:bold;vertical-align:top;">' .
                e($label) .
                '</td>';
            $body .= '<td style="padding:8px 10px;border-top:1px solid #e5e7eb;vertical-align:top;">' . e($value) . '</td>';
            $body .= '</tr>';
        }

        $body .= '</table>';
        $body .= '</div>';
        $body .= '</div>';

        $body .= '<div style="padding:14px 4px;color:#6b7280;font-size:12px;text-align:center;">';
        $body .= 'RideWrench · ' . e(rtrim((string) config('app.url'), '/'));
        $body .= '</div>';

        $body .= '</div>';
        $body .= '</body>';
        $body .= '</html>';

        $debug = null;

        if ($this->sendFeedbackEmail($to, $subject, $body, $debug, $email !== '' ? $email : null, $name !== '' ? $name : null)) {
            return redirect('/feedback/thank-you')->with('success', 'feedbackSent');
        }

        Log::error('Feedback email failed: ' . (string) $debug);

        return redirect('/feedback')->with('error', 'feedbackFailed');
    }

    public function feedbackThankYou(Request $request): View
    {
        return view('public.feedback-thank-you', [
            'user' => $request->user(),
        ]);
    }

    private function verifyRecaptcha(string $token, ?string $ip = null): bool
    {
        $secret = (string) config('services.recaptcha.secret_key');

        if ($secret === '') {
            return false;
        }

        if ($token === '') {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            if (!$response->ok()) {
                return false;
            }

            return (bool) ($response->json('success') ?? false);
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }

    private function sendFeedbackEmail(
        string $to,
        string $subject,
        string $body,
        ?string &$debug = null,
        ?string $replyToEmail = null,
        ?string $replyToName = null,
    ): bool {
        try {
            Mail::html($body, function ($message) use ($to, $subject, $replyToEmail, $replyToName): void {
                $message->to($to);
                $message->subject($subject);

                if ($replyToEmail) {
                    $message->replyTo($replyToEmail, $replyToName ?: null);
                }
            });

            $debug = 'SMTP accepted.';

            return true;
        } catch (\Throwable $e) {
            $debug = $e->getMessage();

            Log::error('RideWrench feedback email failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }
}
