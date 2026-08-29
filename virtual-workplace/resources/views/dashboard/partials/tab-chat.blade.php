        <div id="tab-chat" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">💬 {{ __('Team Chat & Direct Messages') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Realtime company communication, direct colleague messaging, and team collaboration channels.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button onclick="loadChatConversations(true)" class="tactile-btn btn-secondary" style="padding: 8px 16px; font-size: 12px;" title="{{ __('Refresh Messages') }}">
                        🔄 {{ __('Refresh') }}
                    </button>
                </div>
            </div>

            <!-- Chat Split Container (3D Tactile Glass Layout) -->
            <div class="card" style="padding: 0; border-radius: var(--radius-xl); overflow: hidden; display: flex; height: 720px; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); background: var(--bg-surface);">
                
                <!-- Left Pane: Channels & Colleagues Roster -->
                <div style="width: 320px; flex-shrink: 0; border-inline-end: 1px solid var(--border-color); background: var(--bg-surface-subtle); display: flex; flex-direction: column;">
                    
                    <!-- Search Bar -->
                    <div style="padding: 16px; border-bottom: 1px solid var(--border-color);">
                        <div style="position: relative;">
                            <input type="text" id="chat-search-input" onkeyup="filterChatRoster()" placeholder="{{ __('Search colleagues & channels...') }}" style="width: 100%; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px 9px 34px; font-size: 12px; color: var(--text-primary); outline: none; box-shadow: var(--shadow-inset-3d);">
                            <span style="position: absolute; inset-inline-start: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; color: var(--text-muted);">🔍</span>
                        </div>
                    </div>

                    <!-- Scrollable Roster Lists -->
                    <div style="flex: 1; overflow-y: auto; padding: 12px 8px; display: flex; flex-direction: column; gap: 16px;">
                        
                        <!-- Channels Section -->
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; padding: 0 8px 6px 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span>📢 {{ __('Company Channels') }}</span>
                            </div>
                            <div id="chat-channels-list" style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="padding: 10px 12px; font-size: 11px; color: var(--text-muted); text-align: center;">
                                    {{ __('Loading channels...') }}
                                </div>
                            </div>
                        </div>

                        <!-- Direct Messages Section -->
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; padding: 0 8px 6px 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span>👥 {{ __('Direct Messages') }}</span>
                                <span class="nav-badge-pill" id="chat-roster-count" style="font-size: 9px;">0</span>
                            </div>
                            <div id="chat-members-list" style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="padding: 10px 12px; font-size: 11px; color: var(--text-muted); text-align: center;">
                                    {{ __('Loading colleagues...') }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right Pane: Active Chat Conversation -->
                <div style="flex: 1; display: flex; flex-direction: column; background: var(--bg-surface);">
                    
                    <!-- Empty State (No Chat Selected) -->
                    <div id="chat-empty-state" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; text-align: center;">
                        <div style="width: 80px; height: 80px; border-radius: 24px; background: rgba(79, 155, 95, 0.12); display: flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 16px; border: 1px solid rgba(79, 155, 95, 0.3); box-shadow: var(--shadow-soft-3d);">
                            💬
                        </div>
                        <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin-bottom: 6px;">{{ __('Welcome to Company Workplace Chat') }}</h3>
                        <p style="font-size: 13px; color: var(--text-secondary); max-width: 380px; margin-bottom: 20px;">
                            {{ __('Select a colleague from the list on the left to start a direct 1-on-1 conversation or join a company collaboration channel.') }}
                        </p>
                        <button onclick="selectFirstColleagueChat()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: 13px;">
                            🚀 {{ __('Start First Conversation') }}
                        </button>
                    </div>

                    <!-- Active Conversation Container (Hidden by default until selected) -->
                    <div id="chat-active-state" style="display: none; flex: 1; flex-direction: column; height: 100%;">
                        
                        <!-- Chat Conversation Top Header -->
                        <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-color); background: var(--bg-surface); display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                <div id="chat-active-avatar-box" style="position: relative; width: 42px; height: 42px; border-radius: 12px; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; color: white; flex-shrink: 0; box-shadow: var(--shadow-soft-3d);">
                                    <span id="chat-active-avatar-initials">AB</span>
                                    <div style="position: absolute; bottom: -2px; inset-inline-end: -2px; width: 12px; height: 12px; border-radius: 50%; background: #4F9B5F; border: 2px solid var(--bg-surface);" title="Online"></div>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h3 id="chat-active-title" style="font-size: 15px; font-weight: 900; color: var(--text-primary); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Colleague Name</h3>
                                        <span id="chat-active-badge" class="nav-badge-pill" style="font-size: 10px;">Member</span>
                                    </div>
                                    <div id="chat-active-subtitle" style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">Senior Engineer • Active Now</div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button id="chat-view-profile-btn" onclick="viewActiveChatUserProfile()" class="tactile-btn btn-secondary" style="padding: 6px 14px; font-size: 11px; font-weight: 800;" title="{{ __('View Member Profile') }}">
                                    <span>👤</span> {{ __('Profile') }}
                                </button>
                                <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 11px; text-decoration: none;" title="{{ __('Meet in Virtual Office') }}">
                                    <span>🚀</span> {{ __('Meet in Office') }}
                                </a>
                            </div>
                        </div>

                        <!-- Chat Messages History Feed -->
                        <div id="chat-messages-container" style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 14px; background: var(--bg-surface-subtle);">
                            <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 20px;">
                                {{ __('Loading message history...') }}
                            </div>
                        </div>

                        <!-- Chat Composer Bar -->
                        <div style="padding: 14px 20px; border-top: 1px solid var(--border-color); background: var(--bg-surface);">
                            <form onsubmit="handleSendChatMessage(event)" style="display: flex; gap: 10px; align-items: flex-end;">
                                <div style="flex: 1; position: relative; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 8px 12px; box-shadow: var(--shadow-inset-3d);">
                                    <textarea id="chat-message-input" rows="1" onkeydown="handleChatInputKeydown(event)" placeholder="{{ __('Type a message... (Press Enter to send, Shift+Enter for new line)') }}" style="width: 100%; background: transparent; border: none; outline: none; color: var(--text-primary); font-size: 13px; font-weight: 500; resize: none; max-height: 120px; font-family: inherit;"></textarea>
                                </div>
                                <button type="submit" id="chat-send-btn" class="tactile-btn btn-primary" style="padding: 11px 20px; font-size: 13px; flex-shrink: 0; border-radius: 12px;">
                                    <span>{{ __('Send') }}</span>
                                    <span>🚀</span>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- 2. TEAM MEMBERS TAB -->
