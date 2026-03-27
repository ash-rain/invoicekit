<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">{{ __('Profile') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Manage your account settings') }}</p>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
                @include('profile.partials.update-password-form')
            </div>

            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
