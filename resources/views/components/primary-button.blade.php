<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-[#0f1117] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#1a1f2e] focus:bg-[#1a1f2e] active:bg-[#1a1f2e] focus:outline-none focus:ring-2 focus:ring-gray-700 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
