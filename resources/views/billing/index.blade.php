<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">{{ __('Billing & Subscription') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Manage your plan and usage') }}</p>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Current Plan --}}
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 mb-6">
            <h3 class="text-sm font-bold text-gray-900 mb-4">{{ __('Current Plan') }}</h3>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                        @if ($plan === 'pro') bg-purple-100 text-purple-800
                        @elseif($plan === 'starter') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ ucfirst($plan) }}
                    </span>
                    <p class="mt-2 text-sm text-gray-600">
                        @if ($plan === 'free')
                            {{ __('Up to 3 clients and 5 invoices per month.') }}
                        @elseif($plan === 'starter')
                            {{ __('Unlimited clients, up to 20 invoices per month.') }}
                        @else
                            {{ __('Unlimited everything, including recurring invoices and client portal.') }}
                        @endif
                    </p>
                </div>
                @if ($plan !== 'free')
                    <form method="POST" action="{{ route('billing.portal') }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            {{ __('Manage Billing') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Usage --}}
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 mb-6">
            <h3 class="text-sm font-bold text-gray-900 mb-4">{{ __('Current Usage') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 bg-[#fafafa] rounded-xl border border-[#eaecf0]">
                    <div class="text-2xl font-bold text-[#0f1117]">{{ $clientCount }}</div>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ __('Clients') }}
                        @if ($clientsLimit !== null)
                            <span class="text-gray-400">/ {{ $clientsLimit }} {{ __('limit') }}</span>
                        @else
                            <span class="text-gray-400">({{ __('unlimited') }})</span>
                        @endif
                    </div>
                </div>
                <div class="p-4 bg-[#fafafa] rounded-xl border border-[#eaecf0]">
                    <div class="text-2xl font-bold text-[#0f1117]">{{ $invoicesThisMonth }}</div>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ __('Invoices this month') }}
                        @if ($invoicesLimit !== null)
                            <span class="text-gray-400">/ {{ $invoicesLimit }} {{ __('limit') }}</span>
                        @else
                            <span class="text-gray-400">({{ __('unlimited') }})</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Plan Comparison --}}
        @if ($plan !== 'pro')
            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-6">{{ __('Upgrade Your Plan') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-{{ $plan === 'free' ? '2' : '1' }} gap-4">

                    @if ($plan === 'free')
                        {{-- Starter Plan --}}
                        <div class="rounded-2xl border-2 border-blue-200 p-6 relative">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-[#0f1117]" style="font-family:'Syne',sans-serif;">{{ __('Starter') }}</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ __('For growing freelancers') }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-bold text-blue-600">€9</span>
                                    <span class="text-sm font-normal text-gray-400">{{ __('/mo') }}</span>
                                </div>
                            </div>
                            <ul class="text-sm text-gray-600 space-y-2 mb-6">
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ __('Unlimited clients') }}</li>
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ __('20 invoices per month') }}</li>
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ __('PDF invoice generation') }}</li>
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ __('EU VAT automation') }}</li>
                            </ul>
                            <form method="POST" action="{{ route('billing.checkout', 'starter') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700">
                                    {{ __('Upgrade to Starter') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Pro Plan --}}
                    <div class="rounded-2xl border-2 border-[#0f1117] p-6 relative">
                        <div class="absolute -top-3 left-6">
                            <span class="bg-[#f59e0b] text-[#0f1117] text-[10px] font-bold uppercase px-3 py-0.5 rounded-full tracking-wider">{{ __('Best Value') }}</span>
                        </div>
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-[#0f1117]" style="font-family:'Syne',sans-serif;">{{ __('Pro') }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('For serious freelancers') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold text-[#0f1117]">€29</span>
                                <span class="text-sm font-normal text-gray-400">{{ __('/mo') }}</span>
                            </div>
                        </div>
                        <ul class="text-sm text-gray-600 space-y-2 mb-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ __('Everything in Starter') }}</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ __('Unlimited invoices') }}</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ __('Recurring invoices') }}</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ __('Client portal') }}</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ __('Priority support') }}</li>
                        </ul>
                        <form method="POST" action="{{ route('billing.checkout', 'pro') }}">
                            @csrf
                            <button type="submit"
                                class="w-full py-2.5 bg-[#0f1117] text-white rounded-xl font-bold text-sm hover:bg-[#1a1f2e]">
                                {{ __('Upgrade to Pro') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
