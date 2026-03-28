<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto" x-data="{
        showCancelModal: false,
        cancelAtPeriodEnd: '1',
        cancelReason: '',
        showPlanModal: false,
    }">
        <div class="mb-8">
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">
                {{ __('Billing & Subscription') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Manage your plan and usage') }}</p>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (request('checkout') === 'success')
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                {{ __('Subscription activated! Your plan has been upgraded.') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Trial Banner --}}
        @if ($user->isOnTrial())
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-amber-800">
                    <strong>{{ __('Free trial active') }}</strong> —
                    {{ __('Your 14-day Pro trial ends on') }}
                    <strong>{{ $user->trial_ends_at->format('M j, Y') }}</strong>
                    ({{ $user->trial_ends_at->diffForHumans() }}).
                    {{ __('Upgrade now to keep all Pro features.') }}
                </div>
            </div>
        @endif

        {{-- Current Plan --}}
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 mb-6">
            <h3 class="text-sm font-bold text-gray-900 mb-4">{{ __('Current Plan') }}</h3>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                            @if ($plan === 'pro') bg-purple-100 text-purple-800
                            @elseif($plan === 'starter') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($plan) }}
                        </span>
                        @if ($user->subscription_status === 'active')
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ __('Active') }}</span>
                        @elseif($user->subscription_status === 'past_due')
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ __('Payment overdue') }}</span>
                        @elseif($user->subscription_status === 'canceled')
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ __('Canceled') }}</span>
                        @elseif($user->isOnTrial())
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ __('Trial') }}</span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        @if ($plan === 'free')
                            {{ __('Up to 3 clients and 5 invoices per month.') }}
                        @elseif($plan === 'starter')
                            {{ __('Unlimited clients, up to 20 invoices per month.') }}
                        @else
                            {{ __('Unlimited everything, including recurring invoices and client portal.') }}
                        @endif
                    </p>
                    @if ($user->subscribed_until)
                        <p class="mt-1 text-xs text-gray-400">
                            {{ __('Renews') }} {{ $user->subscribed_until->format('M j, Y') }}
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-wrap justify-end">
                    @if ($plan !== 'pro')
                        <button type="button" @click="showPlanModal = true"
                            class="px-4 py-2.5 border border-indigo-300 rounded-xl text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                            {{ __('Compare Plans') }}
                        </button>
                    @endif
                    @if ($user->hasActiveSubscription() || $user->stripe_customer_id)
                        <form method="POST" action="{{ route('billing.portal') }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                {{ __('Manage Billing') }}
                            </button>
                        </form>
                    @endif
                    @if ($user->hasActiveSubscription() && $user->stripe_subscription_id && $user->subscription_status !== 'canceled')
                        <button type="button" @click="showCancelModal = true"
                            class="px-4 py-2.5 border border-red-200 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50">
                            {{ __('Cancel') }}
                        </button>
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
                        @if ($invoicesLimit !== null)
                            @php $pct = min(100, $invoicesLimit > 0 ? (int) round($invoicesThisMonth / $invoicesLimit * 100) : 0); @endphp
                            <div class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all
                                {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-indigo-500') }}"
                                    style="width: {{ $pct }}%"></div>
                            </div>
                            @if ($pct >= 80)
                                <p
                                    class="mt-1 text-xs {{ $pct >= 100 ? 'text-red-600' : 'text-amber-600' }} font-medium">
                                    @if ($pct >= 100)
                                        {{ __('Limit reached.') }}
                                        <a href="#" @click.prevent="showPlanModal = true"
                                            class="underline">{{ __('Upgrade') }}</a>
                                    @else
                                        {{ __('Approaching limit.') }}
                                        <a href="#" @click.prevent="showPlanModal = true"
                                            class="underline">{{ __('Upgrade') }}</a>
                                    @endif
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Billing History --}}
            @if (count($billingHistory) > 0)
                <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 mb-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">{{ __('Billing History') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#f3f4f6]">
                                    <th
                                        class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider pb-3">
                                        {{ __('Date') }}</th>
                                    <th
                                        class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider pb-3">
                                        {{ __('Description') }}</th>
                                    <th
                                        class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider pb-3">
                                        {{ __('Amount') }}</th>
                                    <th
                                        class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider pb-3">
                                        {{ __('Status') }}</th>
                                    <th class="pb-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f9fafb]">
                                @foreach ($billingHistory as $stripeInvoice)
                                    <tr>
                                        <td class="py-3 text-gray-600">
                                            {{ \Carbon\Carbon::createFromTimestamp($stripeInvoice->created)->format('M j, Y') }}
                                        </td>
                                        <td class="py-3 text-gray-800 font-medium">
                                            {{ $stripeInvoice->lines->data[0]->description ?? __('Subscription') }}
                                        </td>
                                        <td class="py-3 text-right font-semibold text-gray-900">
                                            {{ strtoupper($stripeInvoice->currency) }}
                                            {{ number_format($stripeInvoice->amount_paid / 100, 2) }}
                                        </td>
                                        <td class="py-3 text-center">
                                            @if ($stripeInvoice->status === 'paid')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ __('Paid') }}</span>
                                            @elseif($stripeInvoice->status === 'open')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ __('Pending') }}</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ ucfirst($stripeInvoice->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-right">
                                            @if ($stripeInvoice->hosted_invoice_url)
                                                <a href="{{ $stripeInvoice->hosted_invoice_url }}" target="_blank"
                                                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                                    {{ __('Receipt') }} ↗
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

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
                                        <h4 class="text-lg font-bold text-[#0f1117]"
                                            style="font-family:'Syne',sans-serif;">
                                            {{ __('Starter') }}</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ __('For growing freelancers') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl font-bold text-blue-600">€9</span>
                                        <span class="text-sm font-normal text-gray-400">{{ __('/mo') }}</span>
                                    </div>
                                </div>
                                <ul class="text-sm text-gray-600 space-y-2 mb-6">
                                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500 shrink-0"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>{{ __('Unlimited clients') }}</li>
                                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500 shrink-0"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>{{ __('20 invoices per month') }}</li>
                                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500 shrink-0"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>{{ __('PDF invoice generation') }}</li>
                                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-500 shrink-0"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>{{ __('EU VAT automation') }}</li>
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
                                <span
                                    class="bg-[#f59e0b] text-[#0f1117] text-[10px] font-bold uppercase px-3 py-0.5 rounded-full tracking-wider">{{ __('Best Value') }}</span>
                            </div>
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-[#0f1117]"
                                        style="font-family:'Syne',sans-serif;">
                                        {{ __('Pro') }}</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ __('For serious freelancers') }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-bold text-[#0f1117]">€29</span>
                                    <span class="text-sm font-normal text-gray-400">{{ __('/mo') }}</span>
                                </div>
                            </div>
                            <ul class="text-sm text-gray-600 space-y-2 mb-6">
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7" />
                                    </svg>{{ __('Everything in Starter') }}</li>
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7" />
                                    </svg>{{ __('Unlimited invoices') }}</li>
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7" />
                                    </svg>{{ __('Recurring invoices') }}</li>
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7" />
                                    </svg>{{ __('Client portal') }}</li>
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7" />
                                    </svg>{{ __('Priority support') }}</li>
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
            {{-- ─── Plan Comparison Modal ─────────────────────────────────── --}}
            <div x-show="showPlanModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @keydown.escape.window="showPlanModal = false">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                    @click.outside="showPlanModal = false">
                    <div class="p-6 border-b border-[#eaecf0] flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-[#0f1117]" style="font-family:'Syne',sans-serif;">
                                {{ __('Choose a Plan') }}</h2>
                            <p class="text-sm text-gray-500 mt-0.5">{{ __('Upgrade to unlock more features') }}</p>
                        </div>
                        <button type="button" @click="showPlanModal = false"
                            class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 {{ $plan === 'free' ? 'md:grid-cols-2' : '' }} gap-4">
                            @if ($plan === 'free')
                                <div class="rounded-2xl border-2 border-blue-200 p-5">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h4 class="text-base font-bold text-[#0f1117]"
                                                style="font-family:'Syne',sans-serif;">{{ __('Starter') }}</h4>
                                            <p class="text-xs text-gray-500">{{ __('For growing freelancers') }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="text-xl font-bold text-blue-600">€9</span>
                                            <span class="text-xs text-gray-400">{{ __('/mo') }}</span>
                                        </div>
                                    </div>
                                    <ul class="text-sm text-gray-600 space-y-1.5 mb-5">
                                        @foreach (['Unlimited clients', '20 invoices per month', 'PDF invoice generation', 'EU VAT automation'] as $feat)
                                            <li class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                {{ __($feat) }}
                                            </li>
                                        @endforeach
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
                            <div class="rounded-2xl border-2 border-[#0f1117] p-5 relative">
                                <div class="absolute -top-3 left-5">
                                    <span
                                        class="bg-[#f59e0b] text-[#0f1117] text-[10px] font-bold uppercase px-3 py-0.5 rounded-full tracking-wider">{{ __('Best Value') }}</span>
                                </div>
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h4 class="text-base font-bold text-[#0f1117]"
                                            style="font-family:'Syne',sans-serif;">{{ __('Pro') }}</h4>
                                        <p class="text-xs text-gray-500">{{ __('For serious freelancers') }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-xl font-bold text-[#0f1117]">€29</span>
                                        <span class="text-xs text-gray-400">{{ __('/mo') }}</span>
                                    </div>
                                </div>
                                <ul class="text-sm text-gray-600 space-y-1.5 mb-5">
                                    @foreach (['Everything in Starter', 'Unlimited invoices', 'Recurring invoices', 'Client portal', 'Priority support'] as $feat)
                                        <li class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ __($feat) }}
                                        </li>
                                    @endforeach
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
                </div>
            </div>

            {{-- ─── Cancellation Modal ─────────────────────────────────────── --}}
            <div x-show="showCancelModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @keydown.escape.window="showCancelModal = false">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" @click.outside="showCancelModal = false">
                    <div class="p-6 border-b border-[#eaecf0] flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-[#0f1117]" style="font-family:'Syne',sans-serif;">
                                {{ __('Cancel Subscription') }}</h2>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ __('We\'re sorry to see you go. Please confirm below.') }}</p>
                        </div>
                        <button type="button" @click="showCancelModal = false"
                            class="text-gray-400 hover:text-gray-600 p-1 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('billing.cancel') }}" class="p-6 space-y-4">
                        @csrf
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                            {{ __('Your access to paid features will end') }}
                            @if ($user->subscribed_until)
                                {{ __('on') }} <strong>{{ $user->subscribed_until->format('M j, Y') }}</strong>
                            @else
                                {{ __('at the end of the current billing period') }}
                            @endif
                            {{ __('if you choose end-of-period cancellation.') }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">{{ __('When to cancel?') }}</p>
                            <div class="space-y-2">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="radio" name="cancel_at_period_end" value="1"
                                        x-model="cancelAtPeriodEnd"
                                        class="mt-0.5 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <div>
                                        <span
                                            class="text-sm font-medium text-gray-800">{{ __('At end of period') }}</span>
                                        <p class="text-xs text-gray-500">
                                            {{ __('Keep access until the billing period ends') }}</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="radio" name="cancel_at_period_end" value="0"
                                        x-model="cancelAtPeriodEnd"
                                        class="mt-0.5 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <div>
                                        <span
                                            class="text-sm font-medium text-gray-800">{{ __('Immediately') }}</span>
                                        <p class="text-xs text-gray-500">
                                            {{ __('Lose access right away, no refund') }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="cancel_reason"
                                class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Reason (optional)') }}</label>
                            <textarea id="cancel_reason" name="reason" rows="3" x-model="cancelReason"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none resize-none"
                                placeholder="{{ __('What could we do better?') }}"></textarea>
                        </div>
                        <div class="flex items-center gap-3 pt-1">
                            <button type="button" @click="showCancelModal = false"
                                class="flex-1 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                {{ __('Keep Subscription') }}
                            </button>
                            <button type="submit"
                                class="flex-1 py-2.5 bg-red-600 text-white rounded-xl text-sm font-bold hover:bg-red-700">
                                {{ __('Confirm Cancellation') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
</x-app-layout>
