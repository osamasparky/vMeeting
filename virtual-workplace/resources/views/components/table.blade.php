@props([
    'headers' => [],
])

<div class="w-full overflow-hidden rounded-[var(--nx-radius-lg)] border border-[var(--nx-border-subtle)] bg-[var(--nx-bg-surface)] shadow-[var(--nx-shadow-sm)]">
    <div class="overflow-x-auto">
        <table class="w-full text-start border-collapse text-[14px]">
            @if(!empty($headers))
                <thead>
                    <tr class="border-b border-[var(--nx-border-subtle)] bg-[var(--nx-sand-100)] text-[var(--nx-text-secondary)] font-medium text-[13px]">
                        @foreach($headers as $header)
                            <th scope="col" class="px-5 py-3.5 text-start font-semibold select-none">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-[var(--nx-border-subtle)] text-[var(--nx-text-primary)]">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
