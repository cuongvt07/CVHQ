<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|string', // chấp nhận cả username lẫn email
        'password' => 'required|min:6',
    ];

    public function login()
    {
        $this->validate();

        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Quá nhiều lần thử. Vui lòng thử lại sau $seconds giây.");
            return;
        }

        // Đăng nhập được bằng email HOẶC username: tự nhận diện theo định dạng.
        $loginField = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$loginField => $this->email, 'password' => $this->password], $this->remember)) {
            // Tài khoản đã ngừng hoạt động (nghỉ việc) -> không cho vào.
            if (!auth()->user()->is_active) {
                Auth::logout();
                $this->addError('email', 'Tài khoản đã ngừng hoạt động. Vui lòng liên hệ quản trị.');
                return;
            }
            RateLimiter::clear($throttleKey);
            session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey);
        $this->addError('email', 'Thông tin đăng nhập không chính xác.');
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.guest');
    }
}
