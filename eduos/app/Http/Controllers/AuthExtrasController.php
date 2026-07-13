<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RobThree\Auth\Providers\Qr\IQRCodeProvider;
use RobThree\Auth\TwoFactorAuth;

/** AUTH-02/03/04/06: forgot/reset password, TOTP MFA, session management. */
class AuthExtrasController extends Controller
{
    private function tfa(): TwoFactorAuth
    {
        // QR handled by our own bacon renderer; provider only supplies data URIs when asked
        return new TwoFactorAuth(new class implements IQRCodeProvider
        {
            public function getMimeType(): string
            {
                return 'image/svg+xml';
            }

            public function getQRCodeImage(string $qrText, int $size): string
            {
                $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size),
                    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                );

                return (new \BaconQrCode\Writer($renderer))->writeString($qrText);
            }
        }, 'EduOS Cameroon');
    }

    // ---- Forgot / reset (demo mode: no mail relay — the reset link is displayed) ----
    public function forgotForm()
    {
        return view('auth.forgot');
    }

    public function sendReset(Request $request)
    {
        $email = $request->validate(['email' => 'required|email'])['email'];
        $user = User::where('email', $email)->first();
        if (! $user) {
            return back()->with('flash', 'If that account exists, a reset link has been issued.');
        }
        $token = Str::random(48);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $link = route('password.reset', ['token' => $token, 'email' => $email]);
        // Real mailer configured: deliver by email and never disclose the link
        if (! in_array(config('mail.default'), ['log', 'array'])) {
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Reset your EduOS password (valid 60 minutes): {$link}",
                    fn ($m) => $m->to($email)->subject('EduOS Cameroon — password reset')
                );

                return back()->with('flash', 'If that account exists, a reset link has been emailed.');
            } catch (\Throwable $e) {
                report($e);
            }
        }
        if (app()->environment('production')) {
            return back()->with('flash', 'If that account exists, a reset link has been issued.');
        }

        // Demo: shown on screen
        return back()->with('flash', 'Reset link (demo delivery): '.$link);
    }

    public function resetForm(Request $request, string $token)
    {
        return view('auth.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $row = DB::table('password_reset_tokens')->where('email', $data['email'])->first();
        if (! $row || ! Hash::check($data['token'], $row->token) || now()->diffInMinutes($row->created_at) > 60) {
            return back()->with('flash_error', 'Invalid or expired reset token.');
        }
        User::where('email', $data['email'])->update(['password' => Hash::make($data['password']), 'must_change_password' => false]);
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
        \App\Modules\Platform\Models\AuthEvent::log('PASSWORD_RESET', $data['email']);

        return redirect()->route('login')->with('flash', 'Password reset — sign in with your new password.');
    }

    // ---- MFA (AUTH-04): TOTP enrolment on profile, challenge at login ----
    public function mfaSetup()
    {
        $tfa = $this->tfa();
        $secret = session('mfa:setup_secret') ?? $tfa->createSecret();
        session(['mfa:setup_secret' => $secret]);
        $uri = 'otpauth://totp/EduOS%20Cameroon:'.rawurlencode(auth()->user()->email).'?secret='.$secret.'&issuer=EduOS%20Cameroon';
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $qr = (new \BaconQrCode\Writer($renderer))->writeString($uri);

        return view('auth.mfa-setup', ['secret' => $secret, 'qr' => $qr]);
    }

    public function mfaEnable(Request $request)
    {
        $code = $request->validate(['code' => 'required|digits:6'])['code'];
        $secret = session('mfa:setup_secret');
        if (! $secret || ! $this->tfa()->verifyCode($secret, $code)) {
            return back()->with('flash_error', 'Code did not verify — scan the QR again and retry.');
        }
        // AUTH-04: one-time recovery codes, hashed at rest, shown exactly once
        $plain = collect(range(1, 8))->map(fn () => strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)))->all();
        auth()->user()->forceFill([
            'totp_secret' => $secret, 'mfa_enabled' => true,
            'recovery_codes' => json_encode(array_map(fn ($c) => Hash::make($c), $plain)),
        ])->save();
        session()->forget('mfa:setup_secret');

        return redirect()->route('profile')->with('flash', 'Two-factor authentication enabled.')->with('recovery_codes', $plain);
    }

    public function mfaDisable()
    {
        auth()->user()->update(['totp_secret' => null, 'mfa_enabled' => false]);

        return back()->with('flash', 'Two-factor authentication disabled.');
    }

    public function mfaChallenge()
    {
        abort_unless(session()->has('mfa:pending'), 404);

        return view('auth.mfa-challenge');
    }

    public function mfaVerify(Request $request)
    {
        $code = trim($request->validate(['code' => 'required|string|max:20'])['code']);
        $user = User::find(session('mfa:pending'));
        abort_unless($user, 404);
        $ok = preg_match('/^\d{6}$/', $code) && $this->tfa()->verifyCode($user->totp_secret, $code);
        if (! $ok) {
            // AUTH-04: one-time recovery codes as fallback for a lost authenticator
            $codes = json_decode((string) $user->recovery_codes, true) ?: [];
            foreach ($codes as $i => $hashed) {
                if (Hash::check(strtoupper($code), $hashed)) {
                    unset($codes[$i]);
                    $user->forceFill(['recovery_codes' => json_encode(array_values($codes))])->save();
                    $ok = true;
                    break;
                }
            }
        }
        if (! $ok) {
            \App\Modules\Platform\Models\AuthEvent::log('MFA_FAIL', $user->email, $user->id);

            return back()->with('flash_error', 'Invalid code.');
        }
        session()->forget('mfa:pending');
        auth()->login($user, true);
        $request->session()->regenerate();
        \App\Modules\Platform\Models\AuthEvent::log('MFA_OK', $user->email, $user->id);

        return redirect()->intended(route('dashboard'));
    }

    // ---- Sessions (AUTH-06) ----
    public function sessions(Request $request)
    {
        $rows = DB::table('sessions')->where('user_id', auth()->id())
            ->orderByDesc('last_activity')->get()
            ->map(fn ($s) => (object) [
                'id' => $s->id,
                'current' => $s->id === $request->session()->getId(),
                'ip' => $s->ip_address,
                'agent' => Str::limit($s->user_agent, 80),
                'last_activity' => \Carbon\Carbon::createFromTimestamp($s->last_activity),
            ]);

        return view('auth.sessions', [
            'sessions' => $rows,
            'authEvents' => \App\Modules\Platform\Models\AuthEvent::where('email', auth()->user()->email)
                ->orderByDesc('id')->limit(15)->get(),
        ]);
    }

    public function revokeOtherSessions(Request $request)
    {
        DB::table('sessions')->where('user_id', auth()->id())
            ->where('id', '!=', $request->session()->getId())->delete();

        return back()->with('flash', 'All other sessions signed out.');
    }
}
