{{-- Rich-text document editor with version history (opened from the Docs tab). --}}
<x-modal id="pm-doc-modal" :title="__('pm.document')" icon="description" maxWidth="1040px" onClose="pmCloseDoc()">
    <div class="pm-doc-head">
        <input class="pm-field pm-doc-icon" id="pm-doc-icon" maxlength="30" aria-label="{{ __('pm.doc_icon') }}">
        <input class="pm-field pm-grow pm-doc-title" id="pm-doc-title" maxlength="255" aria-label="{{ __('pm.doc_title') }}" dir="auto">
        <span class="pm-muted" id="pm-doc-meta" aria-live="polite"></span>
        <button type="button" class="ula-btn ula-btn--ghost ula-btn--md" id="pm-doc-history-btn" aria-expanded="false" onclick="pmToggleDocHistory()">
            <span class="material-symbols-rounded" aria-hidden="true">history</span><span>{{ __('pm.history') }}</span></button>
        <button type="button" class="ula-btn ula-btn--primary ula-btn--md" id="pm-doc-save-btn" onclick="pmSaveDoc()">
            <span class="material-symbols-rounded" aria-hidden="true">save</span><span>{{ __('pm.save') }}</span></button>
    </div>

    <div class="pm-doc-toolbar" id="pm-doc-toolbar" role="toolbar" aria-label="{{ __('pm.formatting') }}">
        @foreach([
            ['formatBlock:h2', 'format_h2', __('pm.fmt_heading')],
            ['formatBlock:h3', 'format_h3', __('pm.fmt_subheading')],
            ['formatBlock:p', 'notes', __('pm.fmt_paragraph')],
            ['bold', 'format_bold', __('pm.fmt_bold')],
            ['italic', 'format_italic', __('pm.fmt_italic')],
            ['underline', 'format_underlined', __('pm.fmt_underline')],
            ['strikeThrough', 'strikethrough_s', __('pm.fmt_strike')],
            ['insertUnorderedList', 'format_list_bulleted', __('pm.fmt_bullets')],
            ['insertOrderedList', 'format_list_numbered', __('pm.fmt_numbers')],
            ['formatBlock:blockquote', 'format_quote', __('pm.fmt_quote')],
            ['formatBlock:pre', 'code_blocks', __('pm.fmt_code')],
            ['link', 'link', __('pm.fmt_link')],
            ['insertHorizontalRule', 'horizontal_rule', __('pm.fmt_divider')],
            ['undo', 'undo', __('pm.fmt_undo')],
            ['redo', 'redo', __('pm.fmt_redo')],
        ] as [$cmd, $icon, $label])
            <button type="button" class="pm-doc-tool" data-cmd="{{ $cmd }}" aria-label="{{ $label }}" title="{{ $label }}">
                <span class="material-symbols-rounded" aria-hidden="true">{{ $icon }}</span>
            </button>
        @endforeach
    </div>

    <div class="pm-doc-body">
        <div class="pm-doc-editor" id="pm-doc-editor" contenteditable="true" dir="auto" role="textbox" aria-multiline="true" aria-label="{{ __('pm.doc_content') }}"></div>

        <aside class="pm-doc-history" id="pm-doc-history" hidden aria-label="{{ __('pm.history') }}">
            <h4 class="pm-extras-label" style="margin: 0 0 var(--ula-space-3);">{{ __('pm.version_history') }}</h4>
            <div id="pm-doc-versions" class="pm-list"></div>
            <div id="pm-doc-preview" class="pm-doc-preview" hidden></div>
        </aside>
    </div>
</x-modal>
