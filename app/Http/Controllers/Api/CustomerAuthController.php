<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AccountActivityMail;
use App\Mail\CustomerWelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Throwable;

class CustomerAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate(['email' => 'required|email|max:255', 'password' => 'required|string|max:255']);
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'Invalid email or password.']);
        }
        $request->session()->regenerate();
        $this->sendActivityMail($request->user(), 'login');

        return $this->user($request);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => 'required|string|max:100', 'lastName' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', PasswordRule::min(8)],
            'locale' => ['nullable', Rule::in(['en-US', 'mm', 'th'])],
        ]);

        $user = DB::transaction(function () use ($data): User {
            $customer = Role::findOrCreate('customer', 'web');
            $user = User::create([
                'name' => trim($data['firstName'].' '.($data['lastName'] ?? '')),
                'email' => $data['email'], 'password' => $data['password'],
            ]);
            $user->assignRole($customer);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        try {
            Mail::to($user->email)->queue((new CustomerWelcomeMail($user->name, $data['locale'] ?? 'en-US'))->onConnection('database')->onQueue('mail'));
        } catch (Throwable $exception) {
            Log::error('Customer welcome email could not be sent.', [
                'user_id' => $user->id, 'exception' => $exception,
            ]);
        }

        return $this->user($request);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $names = $user ? explode(' ', $user->name, 2) : [];

        return response()->json(['data' => $user ? [
            'id' => $user->id, 'name' => $user->name, 'email' => $user->email,
            'first_name' => $names[0], 'last_name' => $names[1] ?? '',
            'profile_details' => $user->profile_details ?? [],
        ] : null]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['data' => null]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100', 'last_name' => 'nullable|string|max:100',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'profile_details' => 'sometimes|array:phone,company,job_title,address,city,state,country,postal_code',
            'profile_details.phone' => ['nullable', 'string', 'max:40', 'regex:/^\+?[0-9][0-9\s().-]{5,39}$/'],
            'profile_details.company' => 'nullable|string|max:255',
            'profile_details.job_title' => 'nullable|string|max:100',
            'profile_details.address' => 'nullable|string|max:1000',
            'profile_details.city' => 'nullable|string|max:100',
            'profile_details.state' => 'nullable|string|max:100',
            'profile_details.country' => 'nullable|string|max:100',
            'profile_details.postal_code' => 'nullable|string|max:20',
        ]);
        $request->user()->fill([
            'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
            'email' => $data['email'],
        ]);
        if (array_key_exists('profile_details', $data)) {
            $request->user()->profile_details = array_replace($request->user()->profile_details ?? [], $data['profile_details']);
        }
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }
        $request->user()->save();

        return $this->user($request);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)],
        ]);
        $request->user()->forceFill(['password' => Hash::make($data['password']), 'remember_token' => Str::random(60)])->save();
        $request->session()->regenerate();

        $this->sendActivityMail($request->user(), 'password_changed');

        return response()->json(['message' => 'Password updated.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|max:255']);
        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'If the account exists, a password reset link will be sent.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email|max:255', 'token' => 'required|string|max:2048',
            'password' => ['required', 'string', 'max:255', PasswordRule::min(8)],
        ]);
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
            $this->sendActivityMail($user, 'password_changed');
        });
        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => __($status)]);
    }
    private function sendActivityMail(User $user, string $activity): void
    {
        try {
            Mail::to($user->email)->queue((new AccountActivityMail(
                $user->name, $activity, now()->format('Y-m-d H:i T'),
            ))->onConnection('database')->onQueue('mail'));
        } catch (Throwable $exception) {
            Log::error('Account activity email could not be queued.', [
                'user_id' => $user->id, 'exception' => $exception,
            ]);
        }
    }

}
