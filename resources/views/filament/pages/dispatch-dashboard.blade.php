<x-filament-panels::page>
    <style>
        .op-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            font-family: inherit;
        }
        .op-card {
            background-color: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .dark .op-card {
            background-color: #0f172a;
            border-color: #1e293b;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.2);
        }
        .op-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .op-date-picker {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            color: #0f172a;
            font-weight: 500;
            outline: none;
            transition: border-color 0.2s;
        }
        .dark .op-date-picker {
            background-color: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
        .op-date-picker:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }
        /* Layout Grid */
        .op-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        .op-grid > div {
            min-width: 0;
            overflow: hidden;
        }
        @media (min-width: 1024px) {
            .op-grid {
                grid-template-columns: 350px 1fr;
            }
        }
        /* Route Cards */
        .route-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .route-card {
            cursor: pointer;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            transition: all 0.2s ease;
        }
        .dark .route-card {
            border-color: #1e293b;
            background-color: #0f172a;
        }
        .route-card:hover {
            border-color: #f59e0b;
            transform: translateY(-1px);
        }
        .route-card.selected {
            border-color: #f59e0b;
            background-color: rgba(245, 158, 11, 0.05);
            box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.1);
        }
        .dark .route-card.selected {
            background-color: rgba(245, 158, 11, 0.08);
        }
        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        .badge-gray { background-color: #f1f5f9; color: #475569; }
        .dark .badge-gray { background-color: #334155; color: #cbd5e1; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .dark .badge-info { background-color: #0c4a6e; color: #38bdf8; }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .dark .badge-success { background-color: #064e3b; color: #4ade80; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
        .dark .badge-danger { background-color: #7f1d1d; color: #f87171; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        .dark .badge-warning { background-color: #78350f; color: #fbbf24; }

        /* Progress Bar */
        .progress-container {
            margin-top: 0.75rem;
        }
        .progress-bar-bg {
            width: 100%;
            background-color: #e2e8f0;
            height: 0.375rem;
            border-radius: 9999px;
            overflow: hidden;
        }
        .dark .progress-bar-bg {
            background-color: #334155;
        }
        .progress-bar-fill {
            background-color: #f59e0b;
            height: 100%;
            border-radius: 9999px;
            transition: width 0.3s ease;
        }
        /* Table styles */
        .table-container {
            overflow-x: auto;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }
        .dark .table-container {
            border-color: #1e293b;
        }
        .stops-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
        }
        .stops-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .stops-table th {
            background-color: #1e293b;
            color: #94a3b8;
            border-bottom-color: #334155;
        }
        .stops-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .dark .stops-table td {
            border-bottom-color: #1e293b;
            color: #cbd5e1;
        }
        .stops-table tr:hover {
            background-color: #f8fafc;
        }
        .dark .stops-table tr:hover {
            background-color: rgba(30, 41, 59, 0.3);
        }
        .btn-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .dark .btn-header {
            border-bottom-color: #1e293b;
        }

        /* Mobile Optimization Queries */
        @media (max-width: 640px) {
            .op-card {
                padding: 1rem 0.75rem;
            }
            .op-header-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .op-header-bar div {
                text-align: center;
            }
            .op-date-picker {
                width: 100%;
                text-align: center;
            }
            .btn-header p {
                display: none;
            }
            .btn-header {
                align-items: center;
                gap: 0.5rem;
            }
            .btn-header h3 {
                font-size: 0.9rem !important;
            }
            .stops-table th, 
            .stops-table td {
                font-size: 0.75rem;
                padding: 0.5rem 0.25rem;
            }
            /* Constrain columns to prevent wide table stretch */
            .stops-table td:nth-child(2) div {
                max-width: 130px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .stops-table td:nth-child(4) span {
                font-size: 0.75rem !important;
            }
            .stops-table td:nth-child(4) div {
                max-width: 80px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            /* Make actions button icons more compact */
            .stops-table td:last-child div {
                gap: 0.15rem !important;
            }
        }
    </style>

    <div class="op-container">
        <!-- Date Selector panel -->
        <div class="op-card op-header-bar">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white" style="font-size: 1.125rem;">Daily Operational Overview</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 0.25rem;">Select a date to view active runs, driver progress, and schedule emergency stops.</p>
            </div>
            <div>
                <input 
                    type="date" 
                    wire:model.live="date" 
                    class="op-date-picker"
                />
            </div>
        </div>

        <!-- Dashboard Layout -->
        <div class="op-grid">
            <!-- Left panel: Routes active -->
            <div>
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Routes Active Today</h4>
                    <span class="badge badge-gray">{{ $this->routes->count() }} active</span>
                </div>

                @if($this->routes->isEmpty())
                    <div class="op-card text-center" style="color: #64748b; padding: 2rem;">
                        <span style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">🚚</span>
                        No scheduled routes run on this day.
                    </div>
                @else
                    <div class="route-list">
                        @foreach($this->routes as $route)
                            <div 
                                wire:click="selectRoute({{ $route->id }})" 
                                class="route-card @if($selectedRouteId === $route->id) selected @endif"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white" style="font-size: 0.95rem;">{{ $route->name }}</h4>
                                        <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">Driver: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $route->driver_name }}</span></p>
                                    </div>
                                    <div>
                                        @if($route->status === 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($route->status === 'in_progress')
                                            <span class="badge badge-info">In Progress</span>
                                        @else
                                            <span class="badge badge-gray">Not Started</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Progress section -->
                                <div class="progress-container">
                                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1" style="font-size: 0.75rem;">
                                        <span>Stops Completed</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $route->completed_stops + $route->skipped_stops }} / {{ $route->total_stops }}
                                        </span>
                                    </div>
                                    <div class="progress-bar-bg">
                                        @php
                                            $percent = $route->total_stops > 0 
                                                ? (($route->completed_stops + $route->skipped_stops) / $route->total_stops) * 100 
                                                : 0;
                                        @endphp
                                        <div class="progress-bar-fill" style="width: {{ $percent }}%"></div>
                                    </div>
                                    @if($route->completed_stops > 0 || $route->skipped_stops > 0)
                                        <div class="flex gap-3 mt-1" style="font-size: 0.65rem; color: #94a3b8;">
                                            <span style="color: #10b981;">{{ $route->completed_stops }} collected</span>
                                            <span style="color: #ef4444;">{{ $route->skipped_stops }} skipped</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right panel: Stops list details -->
            <div>
                @if(!$selectedRouteId)
                    <div class="op-card text-center flex flex-col items-center justify-center" style="color: #64748b; min-h-300; padding: 4rem;">
                        <span style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem;">👈</span>
                        <h4 class="font-bold text-gray-800 dark:text-white" style="font-size: 1.1rem; margin-bottom: 0.25rem;">Select a Route</h4>
                        <p style="font-size: 0.85rem; max-w: 380px; margin: 0 auto;">Click on an active route from the left panel to inspect its stop details and execute management actions.</p>
                    </div>
                @else
                    <div class="op-card" style="padding: 1.5rem;">
                        <div class="btn-header">
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white" style="font-size: 1rem;">Route Stop Schedule</h3>
                                <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">Manage daily stops, reassign containers, or log manual pickups.</p>
                            </div>
                            <div>
                                <x-filament::button 
                                    size="sm"
                                    icon="heroicon-o-plus"
                                    color="amber"
                                    wire:click="mountAction('addEmergencyStop')"
                                >
                                    Add Stop
                                </x-filament::button>
                            </div>
                        </div>

                        @if($this->stops->isEmpty())
                            <div class="text-center" style="color: #64748b; padding: 3rem 0;">
                                No stops scheduled on this route for today. Click the "Add Stop" button above to insert one.
                            </div>
                        @else
                            <div class="table-container">
                                <table class="stops-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px; text-align: center;">#</th>
                                            <th>Location details</th>
                                            <th>Status</th>
                                            <th>Pounds / Logs</th>
                                            <th style="text-align: right; width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->stops as $stop)
                                            <tr>
                                                <td style="text-align: center; font-weight: bold; color: #0f172a;" class="dark:text-white">
                                                    {{ $stop->position }}
                                                </td>
                                                <td>
                                                    <div style="font-weight: 600; color: #0f172a;" class="dark:text-white">{{ $stop->location->name }}</div>
                                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.15rem;">Cust: {{ $stop->location->customer->name }}</div>
                                                    <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 0.25rem;">{{ $stop->location->service_address }}</div>
                                                </td>
                                                <td>
                                                    @if($stop->status === 'completed')
                                                        <span class="badge badge-success">Collected</span>
                                                    @elseif($stop->status === 'skipped')
                                                        <span class="badge badge-danger">Skipped</span>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($stop->status === 'completed' && $stop->pickupEvent)
                                                        <span style="font-weight: bold; color: #10b981; font-size: 0.9rem;">
                                                            {{ number_format($stop->pickupEvent->pounds_collected, 2) }} lbs
                                                        </span>
                                                        @if($stop->pickupEvent->notes)
                                                            <div style="font-size: 0.65rem; color: #94a3b8; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.15rem;" title="{{ $stop->pickupEvent->notes }}">
                                                                Notes: {{ $stop->pickupEvent->notes }}
                                                            </div>
                                                        @endif
                                                    @elseif($stop->status === 'skipped' && $stop->pickupEvent)
                                                        <span style="color: #ef4444; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                                            {{ $stop->pickupEvent->skip_reason }}
                                                        </span>
                                                        @if($stop->pickupEvent->notes)
                                                            <div style="font-size: 0.65rem; color: #94a3b8; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.15rem;" title="{{ $stop->pickupEvent->notes }}">
                                                                Reason: {{ $stop->pickupEvent->notes }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span style="color: #cbd5e1;">-</span>
                                                    @endif
                                                </td>
                                                <td style="text-align: right;">
                                                    <div style="display: inline-flex; gap: 0.25rem; justify-content: flex-end;">
                                                        @if($stop->status === 'pending')
                                                            <x-filament::icon-button 
                                                                icon="heroicon-o-check-circle" 
                                                                color="success" 
                                                                size="sm"
                                                                tooltip="Mark Complete Manually"
                                                                wire:click="mountAction('markCompleteManually', { stopId: {{ $stop->id }} })"
                                                            />
                                                        @endif
                                                        <x-filament::icon-button 
                                                            icon="heroicon-o-arrows-right-left" 
                                                            color="gray" 
                                                            size="sm"
                                                            tooltip="Reassign Stop"
                                                            wire:click="mountAction('reassignStop', { stopId: {{ $stop->id }} })"
                                                        />
                                                        <x-filament::icon-button 
                                                            icon="heroicon-o-trash" 
                                                            color="danger" 
                                                            size="sm"
                                                            tooltip="Delete Stop"
                                                            wire:click="mountAction('deleteStop', { stopId: {{ $stop->id }} })"
                                                        />
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modals renderer for actions -->
    <x-filament-actions::modals />
</x-filament-panels::page>
