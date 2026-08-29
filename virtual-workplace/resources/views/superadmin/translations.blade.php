@extends('superadmin.layout')

@section('title', __('System Translations & Localization Manager'))
@section('page_title', __('System Translations (إدارة لغات النظام وترجماته)'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div class="panel-card" style="border-radius: var(--radius-xl); padding: 22px 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <span>🌐</span> {{ __('Translation & Localization Manager') }}
                </h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                    {{ __('Manage, customize, and edit all platform phrases in Arabic and English directly with immediate real-time synchronization.') }}
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" onclick="openAddPhraseModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                    <span>+</span> {{ __('Add New Phrase (إضافة عبارة جديدة)') }}
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <form method="GET" action="{{ route('superadmin.translations') }}" style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 260px; position: relative;">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ __('Search keys, Arabic translations or English text...') }}"
                    style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 11px 16px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);"
                >
            </div>
            <button type="submit" class="tactile-btn btn-secondary" style="padding: 10px 20px; font-size: 13px;">
                🔍 {{ __('Search') }}
            </button>
            @if($search)
                <a href="{{ route('superadmin.translations') }}" class="tactile-btn" style="padding: 10px 16px; font-size: 13px; background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); text-decoration: none;">
                    ✕ {{ __('Clear Filter') }}
                </a>
            @endif
        </form>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 12px; color: var(--text-secondary); font-weight: 700;">
            <span>{{ __('Showing :filtered of :total phrases', ['filtered' => $filteredCount, 'total' => $totalCount]) }}</span>
            <span>{{ __('Page :page of :total_pages', ['page' => $page, 'total_pages' => $totalPages]) }}</span>
        </div>
    </div>

    <!-- Translations Editor Form -->
    <form method="POST" action="{{ route('superadmin.translations.update') }}" id="translations-form">
        @csrf

        <div class="panel-card" style="border-radius: var(--radius-xl); padding: 0; overflow: hidden;">
            <div style="padding: 18px 24px; background: var(--bg-surface-subtle); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="font-weight: 800; font-size: 13px; color: var(--text-primary);">
                    📝 {{ __('Bilingual Translation Table (Arabic ⇄ English)') }}
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="padding: 9px 24px; font-size: 13px;">
                    💾 {{ __('Save All Changes (حفظ التعديلات)') }}
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};">
                    <thead>
                        <tr style="background: var(--bg-surface); border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 11px; font-weight: 800; text-transform: uppercase;">
                            <th style="padding: 14px 20px; width: 30%;">🔑 {{ __('Original Key / Identifier') }}</th>
                            <th style="padding: 14px 20px; width: 32%;">🇸🇦 {{ __('Arabic Translation (العربية)') }}</th>
                            <th style="padding: 14px 20px; width: 32%;">🇬🇧 {{ __('English Translation (الإنجليزية)') }}</th>
                            <th style="padding: 14px 20px; width: 6%; text-align: center;">⚡</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedItems as $item)
                            <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s;" onmouseover="this.style.background='var(--bg-surface-subtle)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 12px 20px; vertical-align: top;">
                                    <input type="hidden" name="keys[]" value="{{ $item['key'] }}">
                                    <div style="font-family: monospace; font-size: 12px; font-weight: 700; color: var(--brand-forest); word-break: break-all; max-width: 320px; line-height: 1.4;">
                                        {{ $item['key'] }}
                                    </div>
                                </td>
                                <td style="padding: 12px 20px; vertical-align: top;">
                                    <textarea
                                        name="ar[]"
                                        rows="2"
                                        dir="rtl"
                                        style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: 'Cairo', sans-serif; color: var(--text-primary); resize: vertical; outline: none; font-weight: 600;"
                                    >{{ $item['ar'] }}</textarea>
                                </td>
                                <td style="padding: 12px 20px; vertical-align: top;">
                                    <textarea
                                        name="en[]"
                                        rows="2"
                                        dir="ltr"
                                        style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: 'Inter', sans-serif; color: var(--text-primary); resize: vertical; outline: none; font-weight: 600;"
                                    >{{ $item['en'] }}</textarea>
                                </td>
                                <td style="padding: 12px 20px; vertical-align: top; text-align: center;">
                                    <button
                                        type="button"
                                        onclick="deletePhraseAction('{{ addslashes($item['key']) }}')"
                                        class="tactile-btn"
                                        style="padding: 6px 10px; font-size: 11px; background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3);"
                                        title="{{ __('Delete Phrase') }}"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    <div style="font-size: 36px; margin-bottom: 8px;">🔍</div>
                                    <div style="font-weight: 800; font-size: 15px;">{{ __('No matching phrases found.') }}</div>
                                    <div style="font-size: 12px; margin-top: 4px;">{{ __('Try changing your search term or add a new phrase.') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bottom Pagination and Save Button -->
            <div style="padding: 18px 24px; background: var(--bg-surface-subtle); border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; gap: 6px;">
                    @if($page > 1)
                        <a href="{{ route('superadmin.translations', array_merge(request()->query(), ['page' => $page - 1])) }}" class="tactile-btn btn-secondary" style="padding: 8px 14px; font-size: 12px; text-decoration: none;">
                            {{ app()->getLocale() === 'ar' ? 'السابق ◀' : '◀ Previous' }}
                        </a>
                    @endif
                    @if($page < $totalPages)
                        <a href="{{ route('superadmin.translations', array_merge(request()->query(), ['page' => $page + 1])) }}" class="tactile-btn btn-secondary" style="padding: 8px 14px; font-size: 12px; text-decoration: none;">
                            {{ app()->getLocale() === 'ar' ? '▶ التالي' : 'Next ▶' }}
                        </a>
                    @endif
                </div>

                <button type="submit" class="tactile-btn btn-primary" style="padding: 10px 28px; font-size: 13px;">
                    💾 {{ __('Save All Changes (حفظ التعديلات)') }}
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Add New Phrase -->
<div id="add-phrase-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="panel-card" style="max-width: 580px; width: 95%; border-radius: var(--radius-xl); padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <span>➕</span> {{ __('Add New Translation Phrase') }}
            </h3>
            <button onclick="closeAddPhraseModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--text-muted);">✕</button>
        </div>

        <form method="POST" action="{{ route('superadmin.translations.add') }}" style="display: flex; flex-direction: column; gap: 16px;">
            @csrf

            <div>
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">
                    🔑 {{ __('Key / English Identifier') }} *
                </label>
                <input
                    type="text"
                    name="key"
                    required
                    placeholder="e.g. Schedule Project Meeting"
                    style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 11px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);"
                >
            </div>

            <div>
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">
                    🇸🇦 {{ __('Arabic Translation (العربية)') }} *
                </label>
                <textarea
                    name="ar"
                    required
                    rows="2"
                    dir="rtl"
                    placeholder="مثال: جدولة اجتماع المشروع"
                    style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 11px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-family: 'Cairo', sans-serif; resize: vertical;"
                ></textarea>
            </div>

            <div>
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">
                    🇬🇧 {{ __('English Translation (Optional)') }}
                </label>
                <textarea
                    name="en"
                    rows="2"
                    dir="ltr"
                    placeholder="e.g. Schedule Project Meeting"
                    style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 11px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-family: 'Inter', sans-serif; resize: vertical;"
                ></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeAddPhraseModal()" class="tactile-btn btn-secondary" style="padding: 10px 18px; font-size: 12px;">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="tactile-btn btn-primary" style="padding: 10px 24px; font-size: 12px;">
                    🚀 {{ __('Add Phrase') }}
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
