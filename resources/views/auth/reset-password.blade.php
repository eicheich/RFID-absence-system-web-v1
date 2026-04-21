<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50">

        <div class="mb-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl shadow-lg mb-4 transform -rotate-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Atur Ulang Sandi</h1>
            <p class="text-sm text-gray-500 italic">Sistem Keamanan Akses RFID</p>
        </div>

        <div class="w-full sm:max-w-md px-8 py-10 bg-white shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] overflow-hidden sm:rounded-3xl border border-gray-100">

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="group">
                    <x-input-label for="email" :value="__('Konfirmasi Email')" class="text-gray-600 font-semibold ml-1" />
                    <x-text-input id="email"
                        class="block mt-1 w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all duration-200"
                        type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="password" :value="__('Password Baru')" class="text-gray-600 font-semibold ml-1" />
                    <x-text-input id="password"
                        class="block mt-1 w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all duration-200"
                        type="password" name="password" required autocomplete="new-password"
                        placeholder="Minimal 8 karakter" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" class="text-gray-600 font-semibold ml-1" />
                    <x-text-input id="password_confirmation"
                        class="block mt-1 w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all duration-200"
                        type="password"
                        name="password_confirmation" required autocomplete="new-password"
                        placeholder="Ulangi password baru" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all duration-200 active:scale-[0.98]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ __('Simpan Perubahan Sandi') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                &larr; Kembali ke halaman Login
            </a>
        </div>
    </div>
</x-guest-layout>
