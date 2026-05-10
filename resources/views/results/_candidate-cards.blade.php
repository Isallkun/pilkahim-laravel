{{-- Initial server-side render of candidate cards (no-JS fallback) --}}
{{-- JS akan replace ini via innerHTML setiap polling --}}
@foreach($candidates as $index => $c)
    @php
        $isLeading = $index === 0 && $c['votes'] > 0;
        $num = str_pad($c['sort_order'], 2, '0', STR_PAD_LEFT);
        $positionLabel = 'Posisi #' . ($index + 1);
    @endphp

    <article class="@if($isLeading) bg-white rounded-3xl border-2 border-[#E5A100] glow-shadow @else bg-white rounded-3xl border border-[#E5E0D5] hover:shadow-md @endif p-4 flex flex-col relative overflow-hidden transition-all hover:-translate-y-1">

        @if($isLeading)
            <div class="absolute top-0 right-0 bg-[#E5A100] text-white font-bold text-[11px] tracking-[0.12em] px-3 py-1.5 rounded-bl-2xl z-10 inline-flex items-center gap-1">
                <span class="material-symbols-outlined fill text-[14px]">workspace_premium</span>
                SEDANG MEMIMPIN
            </div>
        @endif

        {{-- Photo --}}
        <div class="relative w-full aspect-[4/5] rounded-2xl overflow-hidden bg-[#F4EDE3] mb-4">
            @if($c['photo_url'])
                <img src="{{ $c['photo_url'] }}" alt="{{ $c['name'] }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-[#8E8676]">
                    <span class="material-symbols-outlined" style="font-size: 80px;">person</span>
                </div>
            @endif

            {{-- Number badge --}}
            <div class="absolute bottom-2 left-2 @if($isLeading) bg-[#FFFBEA] border-2 border-[#E5A100] @else bg-white border border-[#E5E0D5] @endif w-12 h-12 rounded-full flex items-center justify-center shadow-md z-10">
                <span class="font-bold text-[18px] @if($isLeading) text-[#D97706] @else text-[#5C5648] @endif">{{ $num }}</span>
            </div>
        </div>

        {{-- Name + position --}}
        <div class="flex flex-col items-center text-center gap-1 mb-4">
            <span class="@if($isLeading) bg-[#FFFBEA] text-[#D97706] border border-[#FDE68A] @else bg-[#F4EDE3] text-[#5C5648] @endif font-bold text-[11px] tracking-[0.1em] px-3 py-1 rounded-full uppercase">{{ $positionLabel }}</span>
            <h3 class="font-bold text-[20px] text-[#2D2A24] mt-2">{{ $c['name'] }}</h3>
            <p class="text-sm text-[#8E8676]">Nomor Urut {{ $num }}</p>
        </div>

        {{-- Vote bar --}}
        <div class="mt-auto flex flex-col gap-2">
            <div class="flex justify-between items-end">
                <span class="text-sm text-[#5C5648] font-medium">Suara</span>
                <span class="font-bold text-[28px] @if($isLeading) text-[#D97706] @else text-[#2D2A24] @endif tabular-nums leading-none">
                    {{ number_format($c['votes']) }}
                </span>
            </div>
            <div class="w-full bg-[#F4EDE3] h-3 rounded-full overflow-hidden">
                <div class="@if($isLeading) bg-gradient-to-r from-[#E5A100] to-[#FCD34D] @else bg-[#D5C4AD] @endif h-full rounded-full bar-fill"
                     style="width: {{ $c['percentage'] }}%;"></div>
            </div>
            <div class="text-right font-bold text-[18px] @if($isLeading) text-[#D97706] @else text-[#5C5648] @endif tabular-nums">
                {{ $c['percentage'] }}%
            </div>
        </div>
    </article>
@endforeach
