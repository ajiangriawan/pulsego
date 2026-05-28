<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PulseGo</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-br from-[#1E6B2A] to-[#3A9E3F] p-8 text-white text-center">
            <h2 class="text-2xl font-bold">PulseGo</h2>
            <p class="text-sm text-green-100 mt-1">Silakan masukkan password baru kamu</p>
        </div>

        <form action="{{ route('password.update') }}" method="POST" class="p-8 space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            @if ($errors->any())
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Alamat Email</label>
                <input type="email" name="email" value="{{ $email ?? old('email') }}" required 
                    class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:border-[#3A9E3F]">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru</label>
                <input type="password" name="password" placeholder="Minimal 8 karakter" required
                    class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:border-[#3A9E3F]">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" placeholder="Ulangi password baru" required
                    class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:border-[#3A9E3F]">
            </div>

            <button type="submit" 
                class="w-full bg-[#3A9E3F] hover:bg-[#1E6B2A] text-white font-bold py-3 px-4 rounded-xl transition duration-200 shadow-lg shadow-green-100">
                Perbarui Password
            </button>
        </form>
    </div>
</body>
</html>