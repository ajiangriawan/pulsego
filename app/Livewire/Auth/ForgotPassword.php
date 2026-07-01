namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Password;

#[Layout('layouts.guest')] // Pastikan file resources/views/layouts/guest.blade.php ada
class ForgotPassword extends Component
{
    public $email;

    protected $rules = [
        'email' => 'required|email|exists:users,email',
    ];

    public function sendResetLink()
    {
        $this->validate();

        // Mengirim link reset password ke email
        $status = Password::broker()->sendResetLink(
            ['email' => $this->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            // Gunakan session flash agar terbaca oleh file Blade
            session()->flash('status', __($status)); 
            $this->reset('email'); // Kosongkan input setelah berhasil
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}