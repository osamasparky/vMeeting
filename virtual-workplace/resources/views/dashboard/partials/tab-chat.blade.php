<div id="tab-chat" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-5); flex-wrap: wrap; gap: var(--nx-spacing-4);">
        <div>
            <h1 class="page-title" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-1); display: flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 28px; color: var(--nx-primary-500);">chat</span>
                <span>{{ __('Team Chat & Direct Messages') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary);">{{ __('Realtime company communication, direct colleague messaging, and team collaboration channels.') }}</p>
        </div>
        <div style="display: flex; gap: var(--nx-spacing-3); align-items: center;">
            <button onclick="loadChatConversations(true)" class="tactile-btn btn-secondary" style="padding: 8px 16px; font-size: var(--nx-font-size-xs); display: inline-flex; align-items: center; gap: var(--nx-spacing-2);" title="{{ __('Refresh Messages') }}">
                <span class="material-symbols-rounded" style="font-size: 16px;">refresh</span>
                <span>{{ __('Refresh') }}</span>
            </button>
        </div>
    </div>

    <!-- Chat Split Container (3D Tactile Glass Layout) -->
    <div class="card" style="padding: 0; border-radius: var(--nx-radius-xl); overflow: hidden; display: flex; height: 720px; border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); background: var(--nx-bg-surface);">
        
        <!-- Left Pane: Channels & Colleagues Roster -->
        <div style="width: 320px; flex-shrink: 0; border-inline-end: 1px solid var(--nx-border-subtle); background: var(--nx-bg-surface-subtle); display: flex; flex-direction: column;">
            
            <!-- Search Bar -->
            <div style="padding: var(--nx-spacing-4); border-bottom: 1px solid var(--nx-border-subtle);">
                <div style="position: relative;">
                    <input type="text" id="chat-search-input" onkeyup="filterChatRoster()" placeholder="{{ __('Search colleagues & channels...') }}" style="width: 100%; background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 9px 12px 9px 36px; font-size: var(--nx-font-size-xs); color: var(--nx-text-primary); outline: none; box-shadow: var(--nx-shadow-inset-3d);">
                    <span class="material-symbols-rounded" style="position: absolute; inset-inline-start: 10px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--nx-text-muted);">search</span>
                </div>
            </div>

            <!-- Scrollable Roster Lists -->
            <div style="flex: 1; overflow-y: auto; padding: var(--nx-spacing-3) var(--nx-spacing-2); display: flex; flex-direction: column; gap: var(--nx-spacing-4);">
                
                <!-- Channels Section -->
                <div>
                    <div style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); text-transform: uppercase; padding: 0 8px 6px 8px; display: flex; justify-content: space-between; align-items: center; letter-spacing: 0.04em;">
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-primary-500);">campaign</span>
                            <span>{{ __('Company Channels') }}</span>
                        </span>
                    </div>
                    <div id="chat-channels-list" style="display: flex; flex-direction: column; gap: 4px;">
                        <div style="padding: 10px 12px; font-size: var(--nx-font-size-xs); color: var(--nx-text-muted); text-align: center;">
                            {{ __('Loading channels...') }}
                        </div>
                    </div>
                </div>

                <!-- Direct Messages Section -->
                <div>
                    <div style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); text-transform: uppercase; padding: 0 8px 6px 8px; display: flex; justify-content: space-between; align-items: center; letter-spacing: 0.04em;">
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-primary-500);">group</span>
                            <span>{{ __('Direct Messages') }}</span>
                        </span>
                        <span class="nav-badge-pill" id="chat-roster-count" style="font-size: 10px; font-family: var(--nx-font-mono);">0</span>
                    </div>
                    <div id="chat-members-list" style="display: flex; flex-direction: column; gap: 4px;">
                        <div style="padding: 10px 12px; font-size: var(--nx-font-size-xs); color: var(--nx-text-muted); text-align: center;">
                            {{ __('Loading colleagues...') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Pane: Active Chat Conversation -->
        <div style="flex: 1; display: flex; flex-direction: column; background: var(--nx-bg-surface);">
            
            <!-- Empty State (No Chat Selected) -->
            <div id="chat-empty-state" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--nx-spacing-10); text-align: center;">
                <div style="width: 80px; height: 80px; border-radius: var(--nx-radius-2xl); background: var(--nx-primary-surface); display: flex; align-items: center; justify-content: center; margin-bottom: var(--nx-spacing-4); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-soft-3d);">
                    <span class="material-symbols-rounded" style="font-size: 40px; color: var(--nx-primary-500);">chat</span>
                </div>
                <h3 style="font-size: var(--nx-font-size-lg); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-2);">{{ __('Welcome to Company Workplace Chat') }}</h3>
                <p style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary); max-width: 380px; margin-bottom: var(--nx-spacing-5);">
                    {{ __('Select a colleague from the list on the left to start a direct 1-on-1 conversation or join a company collaboration channel.') }}
                </p>
                <button onclick="selectFirstColleagueChat()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: var(--nx-font-size-sm); display: inline-flex; align-items: center; gap: var(--nx-spacing-2);">
                    <span class="material-symbols-rounded" style="font-size: 18px;">chat</span>
                    <span>{{ __('Start First Conversation') }}</span>
                </button>
            </div>

            <!-- Active Conversation Container (Hidden by default until selected) -->
            <div id="chat-active-state" style="display: none; flex: 1; flex-direction: column; height: 100%;">
                
                <!-- Chat Conversation Top Header -->
                <div style="padding: 14px 20px; border-bottom: 1px solid var(--nx-border-subtle); background: var(--nx-bg-surface); display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: var(--nx-spacing-3); min-width: 0;">
                        <div id="chat-active-avatar-box" style="position: relative; width: 42px; height: 42px; border-radius: var(--nx-radius-lg); background: var(--nx-accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: var(--nx-font-weight-black); font-size: 16px; color: white; flex-shrink: 0; box-shadow: var(--nx-shadow-soft-3d);">
                            <span id="chat-active-avatar-initials" style="font-family: var(--nx-font-mono);">AB</span>
                            <div style="position: absolute; bottom: -2px; inset-inline-end: -2px; width: 12px; height: 12px; border-radius: 50%; background: #4F9B5F; border: 2px solid var(--nx-bg-surface);" title="Online"></div>
                        </div>
                        <div style="min-width: 0;">
                            <div style="display: flex; align-items: center; gap: var(--nx-spacing-2);">
                                <h3 id="chat-active-title" style="font-size: var(--nx-font-size-sm); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Colleague Name</h3>
                                <span id="chat-active-badge" class="nav-badge-pill" style="font-size: 10px;">Member</span>
                            </div>
                            <div id="chat-active-subtitle" style="font-size: var(--nx-font-size-xs); color: var(--nx-text-secondary); margin-top: 2px;">Senior Engineer • Active Now</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: var(--nx-spacing-2); align-items: center;">
                        <button id="chat-view-profile-btn" onclick="viewActiveChatUserProfile()" class="tactile-btn btn-secondary" style="padding: 6px 14px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); display: inline-flex; align-items: center; gap: 4px;" title="{{ __('View Member Profile') }}">
                            <span class="material-symbols-rounded" style="font-size: 15px;">person</span>
                            <span>{{ __('Profile') }}</span>
                        </button>
                        <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: var(--nx-font-size-xs); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="{{ __('Meet in Virtual Office') }}">
                            <span class="material-symbols-rounded" style="font-size: 15px;">meeting_room</span>
                            <span>{{ __('Meet in Office') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Chat Messages History Feed -->
                <div id="chat-messages-container" style="flex: 1; overflow-y: auto; padding: var(--nx-spacing-5); display: flex; flex-direction: column; gap: var(--nx-spacing-3); background: var(--nx-bg-surface-subtle);">
                    <div style="text-align: center; color: var(--nx-text-muted); font-size: var(--nx-font-size-xs); padding: 20px;">
                        {{ __('Loading message history...') }}
                    </div>
                </div>

                <!-- Chat Composer Bar -->
                <div style="padding: 14px 20px; border-top: 1px solid var(--nx-border-subtle); background: var(--nx-bg-surface);">
                    <form onsubmit="handleSendChatMessage(event)" style="display: flex; gap: var(--nx-spacing-3); align-items: flex-end; margin: 0;">
                        <div style="flex: 1; position: relative; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-lg); padding: 8px 12px; box-shadow: var(--nx-shadow-inset-3d);">
                            <textarea id="chat-message-input" rows="1" onkeydown="handleChatInputKeydown(event)" placeholder="{{ __('Type a message... (Press Enter to send, Shift+Enter for new line)') }}" style="width: 100%; background: transparent; border: none; outline: none; color: var(--nx-text-primary); font-size: var(--nx-font-size-sm); font-weight: 500; resize: none; max-height: 120px; font-family: inherit;"></textarea>
                        </div>
                        <button type="submit" id="chat-send-btn" class="tactile-btn btn-primary" style="padding: 11px 20px; font-size: var(--nx-font-size-sm); flex-shrink: 0; border-radius: var(--nx-radius-lg); display: inline-flex; align-items: center; gap: 6px;">
                            <span>{{ __('Send') }}</span>
                            <span class="material-symbols-rounded" style="font-size: 16px;">send</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>
