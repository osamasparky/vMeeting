@extends('superadmin.layout')

@section('title', __('System Translations & Localization Manager'))
@section('page_title', __('System Translations'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 22px 28px; box-shadow: var(--ula-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 800; color: var(--ula-text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 22px;">translate</span>
                    <span>{{ __('Translation & Localization Manager') }}</span>
                </h3>
                <p style="font-size: 13px; color: var(--ula-text-secondary); margin: 4px 0 0 0;">
                    {{ __('Manage, customize, and edit all platform phrases in Arabic and English directly with immediate real-time synchronization.') }}
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" onclick="openAddPhraseModal()" class="tactile-btn btn-primary" style="padding: 9px 18px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">add</span>
                    <span>{{ __('Add New Phrase') }}</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <form method="GET" action="{{ route('superadmin.translations') }}" style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 260px; position: relative; display: flex; align-items: center;">
                <span class="material-symbols-rounded" style="position: absolute; inset-inline-start: 14px; font-size: 18px; color: var(--ula-text-muted); pointer-events: none;">search</span>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ __('Search keys, Arabic translations or English text...') }}"
                    style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-pill); padding: 10px 16px; padding-inline-start: 40px; color: var(--ula-text-primary); outline: none; font-size: 13px;"
                >
            </div>
            <button type="submit" class="tactile-btn btn-secondary" style="padding: 10px 20px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 15px;">search</span>
                <span>{{ __('Search') }}</span>
            </button>
            @if($search)
                <a href="{{ route('superadmin.translations') }}" class="tactile-btn" style="padding: 10px 16px; font-size: 12px; border-radius: var(--ula-radius-pill); background: rgba(217, 107, 95, 0.12); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.3); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-rounded" style="font-size: 15px;">close</span>
                    <span>{{ __('Clear Filter') }}</span>
                </a>
            @endif
        </form>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 12px; color: var(--ula-text-secondary); font-weight: 600;">
            <span>{{ __('Showing :filtered of :total phrases', ['filtered' => $filteredCount, 'total' => $totalCount]) }}</span>
            <span style="font-family: 'IBM Plex Mono', monospace;">{{ __('Page :page of :total_pages', ['page' => $page, 'total_pages' => $totalPages]) }}</span>
        </div>
    </div>

    <!-- Translations Editor Form -->
    <form method="POST" action="{{ route('superadmin.translations.update') }}" id="translations-form">
        @csrf

        <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 0; overflow: hidden; box-shadow: var(--ula-shadow-sm);">
            <div style="padding: 18px 24px; background: var(--ula-surface-page-alt); border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="font-weight: 800; font-size: 13px; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 18px;">translate</span>
                    <span>{{ __('Bilingual Translation Table (Arabic ⇄ English)') }}</span>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="padding: 9px 24px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                    <span>{{ __('Save All Changes') }}</span>
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: start;">
                    <thead>
                        <tr style="background: var(--ula-surface-card); border-bottom: 1px solid var(--ula-border-subtle); color: var(--ula-text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th style="padding: 14px 20px; width: 30%; text-align: start;">{{ __('Original Key / Identifier') }}</th>
                            <th style="padding: 14px 20px; width: 32%; text-align: start;">{{ __('Arabic Translation') }}</th>
                            <th style="padding: 14px 20px; width: 32%; text-align: start;">{{ __('English Translation') }}</th>
                            <th style="padding: 14px 20px; width: 6%; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedItems as $item)
                            <tr style="border-bottom: 1px solid var(--ula-border-subtle); transition: background 0.15s ease;">
                                <td style="padding: 12px 20px; vertical-align: top;">
                                    <input type="hidden" name="keys[]" value="{{ $item['key'] }}">
                                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; font-weight: 700; color: var(--ula-palm-900); word-break: break-all; max-width: 320px; line-height: 1.4;">
                                        {{ $item['key'] }}
                                    </div>
                                </td>
                                <td style="padding: 12px 20px; vertical-align: top;">
                                    <textarea
                                        name="ar[]"
                                        rows="2"
                                        dir="rtl"
                                        style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: 'IBM Plex Sans Arabic', sans-serif; color: var(--ula-text-primary); resize: vertical; outline: none;"
                                    >{{ $item['ar'] }}</textarea>
                                </td>
                                <td style="padding: 12px 20px; vertical-align: top;">
                                    <textarea
                                        name="en[]"
                                        rows="2"
                                        dir="ltr"
                                        style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: 'IBM Plex Sans', sans-serif; color: var(--ula-text-primary); resize: vertical; outline: none;"
                                    >{{ $item['en'] }}</textarea>
                                </td>
                                <td style="padding: 12px 20px; vertical-align: top; text-align: center;">
                                    <button
                                        type="button"
                                        onclick="deletePhraseAction('{{ addslashes($item['key']) }}')"
                                        class="tactile-btn"
                                        style="padding: 6px 10px; font-size: 11px; background: rgba(217, 107, 95, 0.12); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.3); display: inline-flex; align-items: center;"
                                        title="{{ __('Delete Phrase') }}"
                                    >
                                        <span class="material-symbols-rounded" style="font-size: 15px;">delete</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 48px; color: var(--ula-text-muted);">
                                    <span class="material-symbols-rounded" style="font-size: 36px; display: block; margin-bottom: 8px; opacity: 0.5;">translate</span>
                                    <div style="font-weight: 700; font-size: 14px; color: var(--ula-text-primary);">{{ __('No matching phrases found.') }}</div>
                                    <div style="font-size: 12px; margin-top: 4px;">{{ __('Try changing your search term or add a new phrase.') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bottom Pagination and Save Button -->
            <div style="padding: 18px 24px; background: var(--ula-surface-page-alt); border-top: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; gap: 6px;">
                    @if($page > 1)
                        <a href="{{ route('superadmin.translations', array_merge(request()->query(), ['page' => $page - 1])) }}" class="tactile-btn btn-secondary" style="padding: 8px 14px; font-size: 12px; border-radius: var(--ula-radius-pill); text-decoration: none;">
                            {{ app()->getLocale() === 'ar' ? 'السابق ◀' : '◀ Previous' }}
                        </a>
                    @endif
                    @if($page < $totalPages)
                        <a href="{{ route('superadmin.translations', array_merge(request()->query(), ['page' => $page + 1])) }}" class="tactile-btn btn-secondary" style="padding: 8px 14px; font-size: 12px; border-radius: var(--ula-radius-pill); text-decoration: none;">
                            {{ app()->getLocale() === 'ar' ? '▶ التالي' : 'Next ▶' }}
                        </a>
                    @endif
                </div>

                <button type="submit" class="tactile-btn btn-primary" style="padding: 9px 24px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                    <span>{{ __('Save All Changes') }}</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Add New Phrase -->
<div id="add-phrase-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(20, 43, 36, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
    <div class="modal-card" style="max-width: 520px; width: 95%; border-radius: var(--ula-radius-xl); padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 22px;">add_circle</span>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">{{ __('Add New Translation Phrase') }}</h3>
            </div>
            <button onclick="closeAddPhraseModal()" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; font-size: 16px; cursor: pointer; color: var(--ula-text-primary); display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('superadmin.translations.add') }}" style="display: flex; flex-direction: column; gap: 16px;">
            @csrf

            <div>
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); text-transform: uppercase; margin-bottom: 6px;">
                    {{ __('Key / English Identifier') }} *
                </label>
                <input
                    type="text"
                    name="key"
                    required
                    placeholder="e.g. Schedule Project Meeting"
                    style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); outline: none; font-size: 13px;"
                >
            </div>

            <div>
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); text-transform: uppercase; margin-bottom: 6px;">
                    {{ __('Arabic Translation') }} *
                </label>
                <textarea
                    name="ar"
                    required
                    rows="2"
                    dir="rtl"
                    placeholder="مثال: جدولة اجتماع المشروع"
                    style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); outline: none; font-size: 13px; font-family: 'IBM Plex Sans Arabic', sans-serif; resize: vertical;"
                ></textarea>
            </div>

            <div>
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); text-transform: uppercase; margin-bottom: 6px;">
                    {{ __('English Translation (Optional)') }}
                </label>
                <textarea
                    name="en"
                    rows="2"
                    dir="ltr"
                    placeholder="e.g. Schedule Project Meeting"
                    style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); outline: none; font-size: 13px; font-family: 'IBM Plex Sans', sans-serif; resize: vertical;"
                ></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeAddPhraseModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">add</span>
                    <span>{{ __('Add Phrase') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="delete-phrase-form" method="POST" action="{{ route('superadmin.translations.delete') }}" style="display: none;">
    @csrf
    <input type="hidden" name="key" id="delete-phrase-key">
</form>

<script nonce="{{ $cspNonce ?? '' }}">
    function openAddPhraseModal() {
        document.getElementById('add-phrase-modal').style.display = 'flex';
    }
    function closeAddPhraseModal() {
        document.getElementById('add-phrase-modal').style.display = 'none';
    }
    function deletePhraseAction(key) {
        if (!confirm('{{ __("Are you sure you want to delete this phrase from the system?") }}')) return;
        document.getElementById('delete-phrase-key').value = key;
        document.getElementById('delete-phrase-form').submit();
    }
</script>
@endsection
