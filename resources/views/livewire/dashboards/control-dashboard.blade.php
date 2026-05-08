<div class="rounded-lg bg-white p-6 shadow-sm">
    <div class="mb-6 flex justify-end">
        <button
            type="button"
            wire:click="exportControlDashboard"
            class="inline-flex items-center gap-2 rounded-md bg-[#1AAD8A] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#127A62]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 12 4-4m-4 4-4-4M4 20h16" />
            </svg>
            Descargar Excel
        </button>
    </div>
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#D4F5ED]">
                <tr>
                    <th class="w-14 px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-black"></th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-black">Etapa</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-black">Problema</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-black">Cantidad de PO</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-black">%</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-black">Responsable</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($summaryRows as $stageRow)
                    <tr class="bg-[#F8FBFA] hover:bg-[#F1F8F5]">
                        <td class="px-4 py-4 text-sm text-[#2E2E2E]">
                            <button
                                type="button"
                                wire:click="toggleStage('{{ $stageRow['stage'] }}')"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 text-[#127A62] hover:bg-[#F4FCF9]">
                                <svg class="h-4 w-4 transition-transform {{ $stageRow['is_expanded'] ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#2E2E2E]">{{ $stageRow['stage'] }}</td>
                        <td class="px-4 py-4 text-sm text-[#5C5C5C]">Resumen de etapa</td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">{{ $stageRow['error_pos'] }}/{{ $stageRow['total_pos'] }}</td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">{{ rtrim(rtrim(number_format($stageRow['percentage'], 1), '0'), '.') }}%</td>
                        <td class="px-4 py-4 text-sm text-[#5C5C5C]">-</td>
                    </tr>

                    @if ($stageRow['is_expanded'])
                        @foreach ($stageRow['rules'] as $ruleRow)
                            @php
                                $hasDetails = ($ruleRow['po_count'] ?? 0) > 0;
                            @endphp
                            <tr class="bg-white hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm text-[#2E2E2E]">
                                    @if ($hasDetails)
                                        <button
                                            type="button"
                                            wire:click="toggleRule('{{ $ruleRow['key'] }}')"
                                            class="ml-6 inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 text-[#127A62] hover:bg-[#F4FCF9]">
                                            <svg class="h-4 w-4 transition-transform {{ $ruleRow['is_expanded'] ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-[#2E2E2E]">{{ $ruleRow['stage'] }}</td>
                                <td class="px-4 py-4 text-sm text-[#2E2E2E]">{{ $ruleRow['title'] }}</td>
                                <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">
                                    @if ($hasDetails)
                                        {{ $ruleRow['po_count'] }}
                                    @else
                                        <span class="text-[#898989]">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">
                                    @if ($ruleRow['percentage'] !== null)
                                        {{ rtrim(rtrim(number_format($ruleRow['percentage'], 1), '0'), '.') }}%
                                    @else
                                        <span class="text-[#898989]">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-[#2E2E2E]">{{ $ruleRow['responsible'] }}</td>
                            </tr>

                            @if ($ruleRow['is_expanded'] && $hasDetails)
                                <tr class="bg-[#FAFAFA]">
                                    <td colspan="6" class="px-6 py-5">
                                        @php
                                            $detailRows = $this->detailRows($ruleRow['key']);
                                            $showTimingColumns = $this->shouldShowTimingColumns($ruleRow['key']);
                                            $showElapsedDaysColumn = $this->shouldShowElapsedDaysColumn($ruleRow['key']);
                                            $showAllowedDaysColumn = $this->shouldShowAllowedDaysColumn($ruleRow['key']);
                                        @endphp
                                        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5C5C5C]">PO</th>
                                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5C5C5C]">Etapa</th>
                                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5C5C5C]">Datos que generan el problema</th>
                                                        @if ($showTimingColumns)
                                                            @if ($showElapsedDaysColumn)
                                                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5C5C5C]">Días transcurridos</th>
                                                            @endif
                                                            @if ($showAllowedDaysColumn)
                                                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5C5C5C]">Límite</th>
                                                            @endif
                                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5C5C5C]">Días atraso</th>
                                                        @endif
                                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5C5C5C]">Responsable</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach ($detailRows as $detail)
                                                        <tr>
                                                            <td class="px-4 py-3 text-sm font-medium text-[#127A62]">
                                                                <a href="{{ route('purchase-orders.edit', $detail['id']) }}" class="hover:underline">
                                                                    {{ $detail['order_number'] }}
                                                                </a>
                                                            </td>
                                                            <td class="px-4 py-3 text-sm text-[#2E2E2E]">{{ $detail['stage'] }}</td>
                                                            <td class="whitespace-pre-line px-4 py-3 text-sm text-[#2E2E2E]">{{ $detail['problem_data'] }}</td>
                                                            @if ($showTimingColumns)
                                                                @if ($showElapsedDaysColumn)
                                                                    <td class="px-4 py-3 text-sm text-[#2E2E2E]">{{ $detail['current_days'] ?? '-' }}</td>
                                                                @endif
                                                                @if ($showAllowedDaysColumn)
                                                                    <td class="px-4 py-3 text-sm text-[#2E2E2E]">{{ $detail['allowed_days'] ?? '-' }}</td>
                                                                @endif
                                                                <td class="px-4 py-3 text-sm text-[#2E2E2E]">{{ $detail['delay_days'] ?? '-' }}</td>
                                                            @endif
                                                            <td class="px-4 py-3 text-sm text-[#2E2E2E]">{{ $detail['responsible'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                @if ($summaryRows->isNotEmpty())
                    <tr class="bg-[#F8FBFA]">
                        <td class="px-4 py-4 text-sm text-[#2E2E2E]"></td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#2E2E2E]">Total</td>
                        <td class="px-4 py-4 text-sm text-[#5C5C5C]"></td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">{{ $stageSummaryTotals['error_pos'] }}/{{ $stageSummaryTotals['total_pos'] }}</td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">{{ rtrim(rtrim(number_format($stageSummaryTotals['percentage'], 1), '0'), '.') }}%</td>
                        <td class="px-4 py-4 text-sm text-[#5C5C5C]">-</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="mt-8 overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#D4F5ED]">
                <tr>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-black">Responsable</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-black">Cantidad de PO</th>
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-black">%</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($responsibleSummaryRows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 text-sm font-medium text-[#2E2E2E]">{{ $row['responsible'] }}</td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">{{ $row['po_count'] }}</td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">{{ rtrim(rtrim(number_format($row['percentage'], 1), '0'), '.') }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-[#898989]">
                            No hay alertas agrupadas por responsable.
                        </td>
                    </tr>
                @endforelse
                @if ($responsibleSummaryRows->isNotEmpty())
                    <tr class="bg-[#F8FBFA]">
                        <td class="px-4 py-4 text-sm font-semibold text-[#2E2E2E]">Total</td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">{{ $responsibleSummaryTotals['po_count'] }}</td>
                        <td class="px-4 py-4 text-sm font-semibold text-[#127A62]">{{ rtrim(rtrim(number_format($responsibleSummaryTotals['percentage'], 1), '0'), '.') }}%</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
