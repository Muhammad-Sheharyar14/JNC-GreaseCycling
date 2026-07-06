<x-filament-panels::page>
    <style>
        .set-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            max-width: 600px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .set-card {
            background-color: #0f172a;
            border-color: #1e293b;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.2);
        }
        .set-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .set-group {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }
        .set-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
        }
        .dark .set-label {
            color: #cbd5e1;
        }
        .set-input {
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #0f172a;
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .dark .set-input {
            background-color: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
        .set-input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }
        .set-help {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.15rem;
        }
    </style>

    <div class="set-card">
        <form wire:submit.prevent="save" class="set-form">
            <!-- Default Reimbursement Rate -->
            <div class="set-group">
                <label for="rate" class="set-label">Default Reimbursement Rate ($/lb)</label>
                <input 
                    id="rate"
                    type="number" 
                    step="0.01" 
                    min="0" 
                    wire:model="default_reimbursement_rate" 
                    class="set-input"
                    required 
                />
                <p class="set-help">The standard rate per pound paid to customers if no specific rate is configured for their location.</p>
            </div>

            <!-- Default Payout Frequency -->
            <div class="set-group">
                <label for="frequency" class="set-label">Default Payout Frequency</label>
                <select 
                    id="frequency"
                    wire:model="default_payout_frequency" 
                    class="set-input"
                    required
                >
                    <option value="weekly">Weekly</option>
                    <option value="biweekly">Biweekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                </select>
                <p class="set-help">The standard billing cycle frequency for customer payout calculations.</p>
            </div>

            <!-- Save Action Button -->
            <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                <x-filament::button type="submit" color="amber">
                    Save Settings
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
