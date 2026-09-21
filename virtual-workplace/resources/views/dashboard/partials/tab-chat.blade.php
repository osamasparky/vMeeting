<div id="tab-chat" class="tab-view">

    <!-- Chat Workspace Split Container (UlaSpace Figma Standard) -->
    <div class="chat-workspace-container" style="display: flex; height: calc(100vh - 210px); min-height: 560px; max-height: 820px; border-radius: var(--ula-radius-xl); overflow: hidden; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs); background: var(--ula-surface-card);">
        
        <!-- Left Pane: Channels & Colleagues Roster (290px Figma Layout) -->
        <div style="width: 290px; flex-shrink: 0; border-inline-end: 1px solid var(--ula-border-subtle); background: var(--ula-surface-page-alt); display: flex; flex-direction: column;">
            
            <!-- Search Bar & Refresh -->
            <div style="padding: var(--ula-space-3) var(--ula-space-4); border-bottom: 1px solid var(--ula-border-subtle); display: flex; gap: var(--ula-space-2); align-items: center;">
                <div style="position: relative; flex: 1;">
                    <input type="text" id="chat-search-input" onkeyup="filterChatRoster()" placeholder="{{ __('Search...') }}" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md); padding: 8px 12px 8px 34px; font-size: var(--ula-size-xs); color: var(--ula-text-primary); outline: none; box-shadow: var(--ula-shadow-xs);">
                    <span class="material-symbols-rounded" style="position: absolute; inset-inline-start: 10px; top: 50%; transform: translateY(-50%); font-size: 16px; color: var(--ula-text-muted);">search</span>
                </div>
                <button onclick="loadChatConversations(true)" class="tactile-btn btn-secondary" style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: var(--ula-radius-md); flex-shrink: 0;" title="{{ __('Refresh Messages') }}">
                    <span class="material-symbols-rounded" style="font-size: 17px;">refresh</span>
                </button>
            </div>

            <!-- Scrollable Roster Lists -->
            <div style="flex: 1; overflow-y: auto; padding: var(--ula-space-4) var(--ula-space-3); display: flex; flex-direction: column; gap: var(--ula-space-4);">
                
                <!-- Channels Section -->
                <div>
                    <div style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); text-transform: uppercase; padding: 0 8px 6px 8px; display: flex; justify-content: space-between; align-items: center; letter-spacing: 0.04em;">
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-accent-default);">campaign</span>
                            <span>{{ __('Company Channels') }}</span>
                        </span>
                    </div>
                    <div id="chat-channels-list" style="display: flex; flex-direction: column; gap: 3px;">
                        <div style="padding: 10px 12px; font-size: var(--ula-size-xs); color: var(--ula-text-muted); text-align: center;">
                            {{ __('Loading channels...') }}
                        </div>
                    </div>
                </div>

                <!-- Direct Messages Section -->
                <div>
                    <div style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); text-transform: uppercase; padding: 0 8px 6px 8px; display: flex; justify-content: space-between; align-items: center; letter-spacing: 0.04em;">
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-accent-default);">group</span>
                            <span>{{ __('Direct Messages') }}</span>
                        </span>
                        <span class="nav-badge-pill" id="chat-roster-count" style="font-size: 10px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">0</span>
                    </div>
                    <div id="chat-members-list" style="display: flex; flex-direction: column; gap: 3px;">
                        <div style="padding: 10px 12px; font-size: var(--ula-size-xs); color: var(--ula-text-muted); text-align: center;">
                            {{ __('Loading colleagues...') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Pane: Active Chat Conversation / Empty State -->
        <div style="flex: 1; display: flex; flex-direction: column; background: var(--ula-surface-card); min-width: 0;">
            
            <!-- Empty State (Figma Component: Empty State #49:16) -->
            <div id="chat-empty-state" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--ula-space-8); text-align: center; background: var(--ula-surface-page-alt);">
                <div style="width: 64px; height: 64px; border-radius: var(--ula-radius-xl); background: var(--ula-surface-accent-soft); display: flex; align-items: center; justify-content: center; margin-bottom: var(--ula-space-5); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs);">
                    <span class="material-symbols-rounded" style="font-size: 32px; color: var(--ula-accent-default);">chat_bubble</span>
                </div>
                <h3 style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin: 0 0 var(--ula-space-2) 0;">
                    {{ __('No Conversation Selected') }}
                </h3>
                <p style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary); max-width: 320px; margin: 0 0 var(--ula-space-5) 0; line-height: 1.5;">
                    {{ __('Select a channel or colleague from the list on the left to view messages and collaborate.') }}
                </p>
                <button onclick="selectFirstColleagueChat()" class="tactile-btn btn-primary" style="padding: 8px 18px; font-size: var(--ula-size-xs); display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 15px;">forum</span>
                    <span>{{ __('Start Conversation') }}</span>
                </button>
            </div>

            <!-- Active Conversation Container (Hidden by default until selected) -->
            <div id="chat-active-state" style="display: none; flex: 1; flex-direction: column; height: 100%; min-width: 0;">
                
                <!-- Chat Conversation Top Header -->
                <div style="height: 60px; padding: 0 20px; border-bottom: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: var(--ula-space-4); min-width: 0;">
                        <div id="chat-active-avatar-box" style="position: relative; width: 38px; height: 38px; border-radius: var(--ula-radius-md); background: var(--ula-gradient-accent); display: flex; align-items: center; justify-content: center; font-weight: var(--ula-weight-bold); font-size: 14px; color: var(--ula-white); flex-shrink: 0; box-shadow: var(--ula-shadow-xs);">
                            <span id="chat-active-avatar-initials" style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">AB</span>
                            <div style="position: absolute; bottom: -2px; inset-inline-end: -2px; width: 10px; height: 10px; border-radius: 50%; background: var(--ula-status-success); border: 2px solid var(--ula-surface-card);" title="Online"></div>
                        </div>
                        <div style="min-width: 0;">
                            <div style="display: flex; align-items: center; gap: var(--ula-space-3);">
                                <h3 id="chat-active-title" style="font-size: var(--ula-size-sm); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Colleague Name</h3>
                                <span id="chat-active-badge" class="nav-badge-pill" style="font-size: 10px;">Member</span>
                            </div>
                            <div id="chat-active-subtitle" style="font-size: 11px; color: var(--ula-text-secondary); margin-top: 1px;">Senior Engineer • Active Now</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: var(--ula-space-3); align-items: center;">
                        <button id="chat-view-profile-btn" onclick="viewActiveChatUserProfile()" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); display: inline-flex; align-items: center; gap: 4px;" title="{{ __('View Member Profile') }}">
                            <span class="material-symbols-rounded" style="font-size: 15px;">person</span>
                            <span>{{ __('Profile') }}</span>
                        </button>
                        <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: var(--ula-size-xs); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="{{ __('Meet in Virtual Office') }}">
                            <span class="material-symbols-rounded" style="font-size: 15px;">meeting_room</span>
                            <span>{{ __('Meet in Office') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Chat Messages History Feed -->
                <div id="chat-messages-container" style="flex: 1; overflow-y: auto; padding: var(--ula-space-5) var(--ula-space-6); display: flex; flex-direction: column; gap: var(--ula-space-4); background: var(--ula-surface-page-alt);">
                    <div style="text-align: center; color: var(--ula-text-muted); font-size: var(--ula-size-xs); padding: 20px;">
                        {{ __('Loading message history...') }}
                    </div>
                </div>

                <!-- Chat Composer Bar -->
                <div style="padding: 12px 18px; border-top: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); flex-shrink: 0;">
                    <form onsubmit="handleSendChatMessage(event)" style="display: flex; gap: var(--ula-space-3); align-items: flex-end; margin: 0;">
                        <div style="flex: 1; position: relative; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 8px 12px; box-shadow: var(--ula-shadow-xs);">
                            <textarea id="chat-message-input" rows="1" onkeydown="handleChatInputKeydown(event)" placeholder="{{ __('Type a message... (Press Enter to send, Shift+Enter for new line)') }}" style="width: 100%; background: transparent; border: none; outline: none; color: var(--ula-text-primary); font-size: var(--ula-size-sm); font-weight: 500; resize: none; max-height: 120px; font-family: inherit; line-height: 1.4;"></textarea>
                        </div>
                        <button type="submit" id="chat-send-btn" class="tactile-btn btn-primary" style="padding: 9px 18px; font-size: var(--ula-size-xs); flex-shrink: 0; border-radius: var(--ula-radius-lg); display: inline-flex; align-items: center; gap: 6px;">
                            <span>{{ __('Send') }}</span>
                            <span class="material-symbols-rounded" style="font-size: 15px;">send</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>
