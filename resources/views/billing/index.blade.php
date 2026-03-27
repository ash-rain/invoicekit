<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Billing & Subscription') }}</h2>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Current Plan --}}
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Current Plan') }}</h3>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($plan === 'pro') bg-purple-100 text-purple-800
                        @elseif($plan === 'starter') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($plan) }}
                    </span>
                    <p class="mt-2 text-sm text-gray-600">
                        @if($plan === 'free')
                            {{ __('Up to 3 clients and 5 invoices per month.') }}
                        @elseif($plan === 'starter')
                            {{ __('Unlimited clients, up to 20 invoices per month.') }}
                        @else
                            {{ __('Unlimited everything, including recurring invoices and client portal.') }}
                        @endif
                    </p>
                </div>
                @if($plan !== 'free')
                    <form method="POST" action="{{ route('billing.portal') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ __('Manage Billing') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Usage --}}
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Current Usage') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $clientCount }}</div>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ __('Clients') }}
                        @if($clientsLimit !== null)
                            / {{ $clientsLimit }} {{ __('limit') }}
                        @else
                            ({{ __('unlimited') }})
                        @endif
                    </div>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $invoicesThisMonth }}</div>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ __('Invoices this month') }}
                        @if($invoicesLimit !== null)
                            / {{ $invoicesLimit }} {{ __('limit') }}
                        @else
                            ({{ __('unlimited') }})
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Plan Comparison --}}
        @if($plan !== 'pro')
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Upgrade Your Plan') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-{{ $plan === 'free' ? '2' : '1' }} gap-4">

                    @if($plan === 'free')
                        {{-- Starter Plan --}}
                        <div class="border-2 border-blue-500 rounded-xl p-5">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-lg font-bold text-gray-900">Starter</h4>
                                <span class="text-2xl font-bold text-blue-600">€9<span class="text-sm font-normal text-gray-500">/mo</span></span>
                            </div>
                            <ul class="text-sm text-gray-600 space-y-2 mb-4">
                                <li>✓ Unlimited clients</li>
                                <li>✓ 20 invoices per month</li>
                                <li>✓ PDF invoice generation</li>
                                <li>✓ EU VAT automation</li>
                            </ul>
                            <form method="POST" action="{{ route('billing.checkout', 'starter') }}">
                                @csrf
                                <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-lg font-medium text-sm hover:bg-blue-700">
                                    {{ __('Upgrade to Starter') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Pro Plan --}}
                    <div class="border-2 border-purple-500 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-bold text-gray-900">Pro</h4>
                            <span class="text-2xl font-bold text-purple-600">€29<span class="text-sm font-normal text-gray-500">/mo</span></span>
                        </div>
                        <ul class="text-sm text-gray-600 space-y-2 mb-4">
                            <li>✓ Everything in Starter</li>
                            <li>✓ Unlimited invoices</li>
                            <li>✓ Recurring invoices</li>
                            <li>✓ Client portal</li>
                            <li>✓ Priority support</li>
                        </ul>
                        <form method="POST" action="{{ route('billing.checkout', 'pro') }}">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-purple-600 text-white rounded-lg font-medium text-sm hover:bg-purple-700">
                                {{ __('Upgrade to Pro') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
