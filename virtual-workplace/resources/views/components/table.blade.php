@props([
    'headers' => [],
])

<div class="w-full overflow-hidden rounded-[var(--ula-radius-lg)] border border-[var(--ula-border-subtle)] bg-[var(--ula-surface-card)] shadow-[var(--ula-shadow-xs)]">
    <div class="overflow-x-auto">
        <table class="w-full text-start border-collapse text-[14px]">
            @if(!empty($headers))
                <thead>
                    <tr class="border-b border-[var(--ula-border-subtle)] bg-[var(--ula-surface-page-alt)] text-[var(--ula-text-secondary)] font-medium text-[13px]">
                        @foreach($headers as $header)
                            <th scope="col" class="px-5 py-3.5 text-start font-semibold select-none">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-[var(--ula-border-subtle)] text-[var(--ula-text-primary)]">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
