<x-filament-panels::page>
    <style>
        .rep-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            font-family: inherit;
        }
        .rep-card {
            background-color: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .rep-card {
            background-color: #0f172a;
            border-color: #1e293b;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.2);
        }
        /* Flex Header */
        .rep-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .rep-date-inputs {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .rep-date-field {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            color: #0f172a;
            font-weight: 500;
            outline: none;
            transition: border-color 0.2s;
        }
        .dark .rep-date-field {
            background-color: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
        .rep-date-field:focus {
            border-color: #f59e0b;
        }
        /* Summary Grid */
        .rep-summary-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .rep-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .rep-summary-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .summary-stat {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .stat-val {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }
        .dark .stat-val {
            color: #ffffff;
        }
        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .dark .stat-label {
            color: #94a3b8;
        }
        /* Breakdowns Grid */
        .rep-grid-two {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 1024px) {
            .rep-grid-two {
                grid-template-columns: 1fr 1fr;
            }
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .dark .card-header {
            border-bottom-color: #1e293b;
        }
        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }
        .dark .card-title {
            color: #ffffff;
        }
        /* Tables */
        .rep-table-wrapper {
            overflow-x: auto;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }
        .dark .rep-table-wrapper {
            border-color: #1e293b;
        }
        .rep-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.85rem;
        }
        .rep-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .rep-table th {
            background-color: #1e293b;
            color: #94a3b8;
            border-bottom-color: #334155;
        }
        .rep-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .dark .rep-table td {
            border-bottom-color: #1e293b;
            color: #cbd5e1;
        }
        .rep-table tr:hover {
            background-color: #f8fafc;
        }
        .dark .rep-table tr:hover {
            background-color: rgba(30, 41, 59, 0.2);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        .badge-red { background-color: #fee2e2; color: #b91c1c; }
        .dark .badge-red { background-color: #7f1d1d; color: #f87171; }
    </style>

    <div class="rep-container">
        <!-- Date Selector panel -->
        <div class="rep-card rep-header-bar">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white" style="font-size: 1.125rem;">Business Analytics</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 0.25rem;">Select a date range to filter summaries, collection breakdowns, and export accounting reports.</p>
            </div>
            <div class="rep-date-inputs">
                <input type="date" wire:model.live="startDate" class="rep-date-field" />
                <span class="text-gray-400">to</span>
                <input type="date" wire:model.live="endDate" class="rep-date-field" />
            </div>
        </div>

        <!-- Metrics Grid summaries -->
        <div class="rep-summary-grid">
            <div class="rep-card summary-stat">
                <span class="stat-label">Total Pounds Collected</span>
                <span class="stat-val" style="color: #10b981;">{{ number_format($this->summary->total_pounds, 2) }} lbs</span>
            </div>
            <div class="rep-card summary-stat">
                <span class="stat-label">Stops Completed</span>
                <span class="stat-val" style="color: #f59e0b;">{{ $this->summary->completed_count }}</span>
            </div>
            <div class="rep-card summary-stat">
                <span class="stat-label">Stops Skipped</span>
                <span class="stat-val" style="color: #ef4444;">{{ $this->summary->skipped_count }}</span>
            </div>
            <div class="rep-card summary-stat">
                <span class="stat-label">Average Weight / Stop</span>
                <span class="stat-val" style="color: #3b82f6;">{{ number_format($this->summary->avg_pounds, 2) }} lbs</span>
            </div>
        </div>

        <!-- Breakdowns Grid: Driver & Route -->
        <div class="rep-grid-two">
            <!-- Driver Breakdown Card -->
            <div class="rep-card">
                <div class="card-header">
                    <span class="card-title">Performance by Driver</span>
                    <x-filament::button size="xs" color="gray" icon="heroicon-o-arrow-down-tray" wire:click="exportCsv('drivers')">
                        Export CSV
                    </x-filament::button>
                </div>
                <div class="rep-table-wrapper">
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Stops Serviced</th>
                                <th style="text-align: right;">Total Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->driverBreakdown as $row)
                                <tr>
                                    <td class="font-semibold text-gray-900 dark:text-white">{{ $row->name }}</td>
                                    <td>{{ $row->completed_count }}</td>
                                    <td style="text-align: right; font-weight: bold; color: #10b981;">{{ number_format($row->total_pounds, 2) }} lbs</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-400 py-4">No driver data in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Route Breakdown Card -->
            <div class="rep-card">
                <div class="card-header">
                    <span class="card-title">Performance by Route</span>
                    <x-filament::button size="xs" color="gray" icon="heroicon-o-arrow-down-tray" wire:click="exportCsv('routes')">
                        Export CSV
                    </x-filament::button>
                </div>
                <div class="rep-table-wrapper">
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>Route Name</th>
                                <th>Stops Serviced</th>
                                <th style="text-align: right;">Total Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->routeBreakdown as $row)
                                <tr>
                                    <td class="font-semibold text-gray-900 dark:text-white">{{ $row->name }}</td>
                                    <td>{{ $row->completed_count }}</td>
                                    <td style="text-align: right; font-weight: bold; color: #10b981;">{{ number_format($row->total_pounds, 2) }} lbs</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-400 py-4">No route data in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Full row breakdown: Customer collection sum -->
        <div class="rep-card">
            <div class="card-header">
                <span class="card-title">Pounds Collected by Customer</span>
                <x-filament::button size="xs" color="gray" icon="heroicon-o-arrow-down-tray" wire:click="exportCsv('customers')">
                    Export CSV
                </x-filament::button>
            </div>
            <div class="rep-table-wrapper">
                <table class="rep-table">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Total Pickups Completed</th>
                            <th style="text-align: right;">Total Weight Owed / Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->customerBreakdown as $row)
                            <tr>
                                <td class="font-semibold text-gray-900 dark:text-white">{{ $row->name }}</td>
                                <td>{{ $row->completed_count }}</td>
                                <td style="text-align: right; font-weight: bold; color: #10b981;">{{ number_format($row->total_pounds, 2) }} lbs</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-gray-400 py-4">No customer data in this range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Full row breakdown: Skipped Stop logs -->
        <div class="rep-card">
            <div class="card-header">
                <span class="card-title">Skipped Stop Operational Report</span>
                <x-filament::button size="xs" color="gray" icon="heroicon-o-arrow-down-tray" wire:click="exportCsv('skips')">
                    Export CSV
                </x-filament::button>
            </div>
            <div class="rep-table-wrapper">
                <table class="rep-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Restaurant Site</th>
                            <th>Customer Account</th>
                            <th>Route</th>
                            <th>Driver</th>
                            <th>Reason</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->skippedLogs as $row)
                            <tr>
                                <td>{{ $row->occurred_at->format('M d, Y H:i') }}</td>
                                <td class="font-semibold text-gray-900 dark:text-white">{{ $row->location->name }}</td>
                                <td>{{ $row->location->customer->name }}</td>
                                <td>{{ $row->route->name }}</td>
                                <td>{{ $row->driver?->name ?? 'Dispatcher/Admin' }}</td>
                                <td>
                                    <span class="badge badge-red">{{ $row->skip_reason }}</span>
                                </td>
                                <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $row->notes }}">
                                    {{ $row->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-400 py-4">No skipped stops in this date range. Great work!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
