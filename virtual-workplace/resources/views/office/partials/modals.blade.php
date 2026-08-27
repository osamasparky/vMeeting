    <!-- ── Modals & Overlays ── -->

    <!-- 0a. Device Settings & Pre-Join Test Modal -->
    <div id="device-settings-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px;">
            <div class="modal-header">
                <div class="modal-title"><span>⚙️</span> {{ __('Audio & Video Device Settings') }}</div>
                <button onclick="closeDeviceSettingsModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;">✕</button>
            </div>
            
            <!-- Video Preview Box -->
            <div style="position: relative; width: 100%; height: 200px; background: #070F0A; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                <video id="device-preview-video" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover;"></video>
                <div id="device-no-preview" style="display: none; color: var(--text-muted); font-size: 12px; font-weight: 700;">📷 {{ __('Camera Preview Inactive') }}</div>
            </div>

            <!-- Mic Volume Level Meter -->
            <div style="margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px;">
                    <span>🎙️ {{ __('Microphone Input Test') }}</span>
                    <span id="mic-level-val">0%</span>
                </div>
                <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 4px; overflow: hidden;">
                    <div id="mic-level-bar" style="width: 0%; height: 100%; background: #10B981; transition: width 0.08s ease;"></div>
                </div>
            </div>

            <!-- Selectors -->
            <div class="input-group">
                <label class="input-label">📹 {{ __('Camera Device') }}</label>
                <select class="styled-input" id="select-video-input" onchange="onCameraDeviceChanged(this.value)">
                    <option value="default">{{ __('Default Camera') }}</option>
                </select>
            </div>
            <div class="input-group">
                <label class="input-label">🎙️ {{ __('Microphone Device') }}</label>
                <select class="styled-input" id="select-audio-input" onchange="onMicDeviceChanged(this.value)">
                    <option value="default">{{ __('Default Microphone') }}</option>
                </select>
            </div>
            <div class="input-group">
                <label class="input-label">🔊 {{ __('Audio Output Speaker') }}</label>
                <select class="styled-input" id="select-audio-output" onchange="onSpeakerDeviceChanged(this.value)">
                    <option value="default">{{ __('Default Speaker') }}</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px; margin-top: 8px;">
                <button onclick="closeDeviceSettingsModal()" class="action-link-btn" style="flex: 1; background: var(--brand-primary); color: white; justify-content: center; padding: 10px;">
                    ✓ {{ __('Done & Save Settings') }}
                </button>
            </div>
        </div>
    </div>

    <!-- 0b. WebRTC & Network Diagnostics Modal -->
    <div id="diagnostics-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 600px;">
            <div class="modal-header">
                <div class="modal-title"><span>🩺</span> {{ __('WebRTC & Media Diagnostics (فحص جودة الاتصال)') }}</div>
                <button onclick="closeDiagnosticsModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;">✕</button>
            </div>

            <div id="diag-loading" style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
                ⏳ {{ __('Running automated WebRTC & STUN/TURN checks...') }}
            </div>

            <div id="diag-content" style="display: none; flex-direction: column; gap: 12px;">
                <!-- Overall Status Banner -->
                <div id="diag-overall-box" style="padding: 12px 16px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(52, 211, 153, 0.3); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 10px; font-weight: 800; color: var(--brand-primary); text-transform: uppercase;">{{ __('Overall Connection Quality') }}</div>
                        <div id="diag-overall-text" style="font-size: 16px; font-weight: 900; color: #6EE7B7;">Excellent (ممتاز)</div>
                    </div>
                    <span id="diag-overall-badge" style="font-size: 24px;">🟢</span>
                </div>

                <!-- Diagnostics Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 800;">📷 {{ __('Camera Access') }}</div>
                        <div id="diag-cam-status" style="font-size: 13px; font-weight: 800; color: #10B981;">✓ Verified</div>
                    </div>
                    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 800;">🎙️ {{ __('Microphone Access') }}</div>
                        <div id="diag-mic-status" style="font-size: 13px; font-weight: 800; color: #10B981;">✓ Verified</div>
                    </div>
                    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 800;">⚡ {{ __('Internet Ping (RTT)') }}</div>
                        <div id="diag-ping-status" style="font-size: 13px; font-weight: 800; color: #6EE7B7;">32 ms</div>
                    </div>
                    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 800;">🌐 {{ __('STUN & TURN Relay') }}</div>
                        <div id="diag-turn-status" style="font-size: 13px; font-weight: 800; color: #10B981;">✓ Active (Coturn)</div>
                    </div>
                </div>

                <!-- Telemetry Stats Table -->
                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; font-family: monospace; font-size: 11px; line-height: 1.6; color: var(--text-secondary);">
                    <div style="display: flex; justify-content: space-between;"><span>SFU Host:</span> <span id="diag-livekit-host" style="color: #93C5FD;">wss://nextspace.munazzah.com/livekit</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Packet Loss:</span> <span id="diag-packet-loss" style="color: #6EE7B7;">0.0%</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Jitter:</span> <span id="diag-jitter" style="color: #6EE7B7;">4 ms</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Framerate (FPS):</span> <span id="diag-fps" style="color: #6EE7B7;">30 FPS</span></div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button onclick="runDiagnosticsCheck()" class="action-link-btn" style="flex: 1; justify-content: center;">🔄 {{ __('Re-run Check') }}</button>
                    <button onclick="copyDiagnosticsReport()" class="action-link-btn" style="flex: 1; background: var(--brand-accent); color: white; justify-content: center;">📋 {{ __('Copy Report for Support') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. User Spotlight & Live Video Modal -->
    <div id="user-spotlight-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 650px; padding: 20px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 12px;">
                    <div id="spotlight-avatar-box" style="width: 42px; height: 42px; border-radius: 12px; overflow: hidden; background: var(--bg-card); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; color: var(--brand-primary); border: 2px solid var(--border-color);">
                    </div>
                    <div>
                        <div id="spotlight-user-name" style="font-size: 16px; font-weight: 800; color: var(--text-primary);"></div>
                        <div id="spotlight-user-subtitle" style="font-size: 11px; color: var(--text-secondary);"></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button id="spotlight-wave-btn" onclick="sendWaveToSpotlightUser()" class="action-link-btn" style="background: rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.4); color: #93C5FD; font-size: 11px; padding: 4px 10px;">
                        <span>👋</span> {{ __('Wave (استئذان)') }}
                    </button>
                    <button onclick="closeUserSpotlight()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;">✕</button>
                </div>
            </div>

            <!-- Spotlight Video Viewport -->
            <div id="spotlight-video-container" style="position: relative; width: 100%; height: 320px; background: #070F0A; border-radius: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); box-shadow: inset 0 0 40px rgba(0,0,0,0.8);">
                <video id="spotlight-video-player" autoplay playsinline style="width: 100%; height: 100%; object-fit: contain; display: none;"></video>
                <div id="spotlight-no-video" style="display: flex; flex-direction: column; align-items: center; gap: 12px; color: var(--text-muted);">
                    <div id="spotlight-big-avatar" style="width: 86px; height: 86px; border-radius: 24px; background: rgba(16, 185, 129, 0.15); border: 2px solid var(--brand-primary); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; color: #6EE7B7; overflow: hidden;">
                    </div>
                    <span style="font-size: 13px; font-weight: 700;">{{ __('Live camera stream is currently offline') }}</span>
                </div>
            </div>

            <!-- Live Work Activity & Task List Section -->
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 4px;">
                <div id="spotlight-active-timer-box" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(52, 211, 153, 0.25); border-radius: 12px; padding: 12px; display: none; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 22px;">⏱️</span>
                        <div>
                            <div style="font-size: 10px; font-weight: 800; color: var(--brand-primary); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Currently Working On:') }}</div>
                            <div id="spotlight-timer-task" style="font-size: 13px; font-weight: 800; color: var(--text-primary);"></div>
                        </div>
                    </div>
                    <div id="spotlight-timer-clock" style="font-family: monospace; font-size: 16px; font-weight: 900; color: #6EE7B7; letter-spacing: 1px;"></div>
                </div>

                <!-- Assigned Tasks List -->
                <div>
                    <div style="font-size: 11px; font-weight: 900; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <span>📋 {{ __('Assigned Tasks & Progress') }}</span>
                        <span id="spotlight-tasks-count" class="guest-badge" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(52, 211, 153, 0.3); color: #6EE7B7;">0 Tasks</span>
                    </div>
                    <div id="spotlight-tasks-list" style="display: flex; flex-direction: column; gap: 6px; max-height: 180px; overflow-y: auto;">
                        <!-- Injected dynamically via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1b. All-Users Camera Gallery Grid Modal -->
    <div id="camera-gallery-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 1100px; height: 85vh;">
            <div class="modal-header">
                <div class="modal-title"><span>🎥</span> {{ __('Office Live Cameras Gallery (شبكة الكاميرات المباشرة)') }}</div>
                <button onclick="closeCameraGalleryModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;">✕</button>
            </div>
            <div id="camera-gallery-grid" style="flex: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; padding: 6px;">
                <!-- Populated dynamically via JS -->
            </div>
        </div>
    </div>

    <!-- 2. Instant Guest Link Modal -->
    <div id="guest-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><span>⚡</span> {{ __('Instant Guest Invitation Link') }}</div>
                <button onclick="closeGuestModal()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            <div class="input-group">
                <label class="input-label">{{ __('Select Target Meeting Room') }}</label>
                <select class="styled-input" id="invite-room-select">
                    @foreach($map->rooms as $r)
                        <option value="{{ $r->id }}">🏢 {{ $r->name }} ({{ ucfirst($r->type) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group">
                <label class="input-label">{{ __('Guest Label / Name') }}</label>
                <input type="text" class="styled-input" id="invite-guest-name" value="Investor / Partner">
            </div>
            <button onclick="generateGuestLink()" class="action-link-btn" style="background: var(--brand-primary); color: white; justify-content: center; padding: 12px; font-size: 13px;">
                ⚡ {{ __('Generate Instant Guest Link') }}
            </button>
            <div id="guest-link-result" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; flex-direction: column; gap: 8px;">
                <input type="text" id="guest-link-input" readonly class="styled-input" style="font-family: monospace; font-size: 11px;">
                <div style="display: flex; gap: 8px;">
                    <button onclick="copyGuestLink()" class="action-link-btn" style="flex: 1; justify-content: center;">📋 {{ __('Copy Link') }}</button>
                    <button onclick="openGuestInNewWindow()" class="action-link-btn" style="flex: 1; background: var(--brand-accent); color: white; justify-content: center;">🚀 {{ __('Open Guest (اختبار)') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2b. Live Online Occupants Modal -->
    <div id="occupants-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><span>👥</span> {{ __('Active People in Office (المتواجدون حالياً)') }}</div>
                <button onclick="closeOccupantsModal()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            <div id="occupants-list" style="display: flex; flex-direction: column; gap: 8px; max-height: 380px; overflow-y: auto;">
                <!-- Populated dynamically via JS -->
            </div>
        </div>
    </div>

    <!-- 3. Room Persistent Files Modal -->
    <div id="room-files-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><span>📁</span> <span id="room-files-title">{{ __('Room Documents & Assets') }}</span></div>
                <button onclick="closeRoomFilesModal()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            
            <!-- Upload Box -->
            <div style="background: var(--bg-input); border: 2px dashed var(--border-color); border-radius: 14px; padding: 18px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <input type="file" id="room-file-input" style="display:none;" onchange="handleRoomFileUpload(this)">
                <span style="font-size: 28px;">📤</span>
                <span style="font-size: 12px; font-weight: 700;">{{ __('Upload PDF, Slides, or Images to this Room Repository') }}</span>
                <button onclick="document.getElementById('room-file-input').click()" class="action-link-btn" style="background: var(--brand-primary); color: white;">
                    <span>⬆️</span> {{ __('Choose File to Upload') }}
                </button>
            </div>

            <!-- Files List -->
            <div id="room-files-list" style="max-height: 280px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;"></div>
        </div>
    </div>

    <!-- 4. Rich Collaborative Whiteboard Modal -->
    <div id="whiteboard-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 1100px; height: 85vh; padding: 0; overflow: hidden;">
            
            <!-- Rich Whiteboard Toolbar -->
            <div class="wb-toolbar">
                <button class="wb-tool-btn active" id="wb-tool-pen" onclick="setWbTool('pen')" title="Pen">✏️</button>
                <button class="wb-tool-btn" id="wb-tool-highlighter" onclick="setWbTool('highlighter')" title="Highlighter">🖍️</button>
                <button class="wb-tool-btn" id="wb-tool-rect" onclick="setWbTool('rect')" title="Rectangle">🔲</button>
                <button class="wb-tool-btn" id="wb-tool-circle" onclick="setWbTool('circle')" title="Circle">⭕</button>
                <button class="wb-tool-btn" id="wb-tool-arrow" onclick="setWbTool('arrow')" title="Arrow">➡️</button>
                <button class="wb-tool-btn" id="wb-tool-line" onclick="setWbTool('line')" title="Straight Line">📏</button>
                <button class="wb-tool-btn" id="wb-tool-text" onclick="setWbTool('text')" title="Add Text">🔤</button>
                <button class="wb-tool-btn" id="wb-tool-note" onclick="setWbTool('note')" title="Sticky Note">📌</button>
                <button class="wb-tool-btn" id="wb-tool-eraser" onclick="setWbTool('eraser')" title="Eraser">🧹</button>

                <div class="dock-divider"></div>

                <!-- Palette -->
                <div style="display: flex; gap: 6px; align-items: center;">
                    <div class="color-dot active" style="background:#0F172A;" onclick="setWbColor('#0F172A')"></div>
                    <div class="color-dot" style="background:#3B82F6;" onclick="setWbColor('#3B82F6')"></div>
                    <div class="color-dot" style="background:#10B981;" onclick="setWbColor('#10B981')"></div>
                    <div class="color-dot" style="background:#F59E0B;" onclick="setWbColor('#F59E0B')"></div>
                    <div class="color-dot" style="background:#EF4444;" onclick="setWbColor('#EF4444')"></div>
                    <div class="color-dot" style="background:#8B5CF6;" onclick="setWbColor('#8B5CF6')"></div>
                </div>

                <div class="dock-divider"></div>

                <button onclick="undoWhiteboard()" class="wb-tool-btn" title="Undo">↩️</button>
                <button onclick="clearWhiteboard()" class="wb-tool-btn" title="Clear Board" style="color: var(--brand-crimson);">🗑️</button>
                <button onclick="exportWhiteboard()" class="action-link-btn" style="padding: 6px 12px;">💾 {{ __('Export PNG') }}</button>
                <button onclick="closeWhiteboardModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer; margin-inline-start: auto;">✕</button>
            </div>

            <!-- Whiteboard Drawing Canvas -->
            <div style="flex: 1; position: relative; background: #FFFFFF;" id="wb-container">
                <canvas id="wb-canvas" style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
            </div>
        </div>
    </div>

    <!-- 5. Recordings Gallery Modal -->
    <div id="recordings-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 900px; height: 80vh;">
            <div class="modal-header">
                <div class="modal-title"><span>📼</span> {{ __('Session Recordings & Gallery') }}</div>
                <button onclick="closeRecordingsGallery()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            <div id="recordings-list" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;"></div>
        </div>
    </div>

    <!-- 6. Knock Alert Dialog Modal (For Occupants) -->
    <div id="knock-alert-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 420px;">
            <div class="modal-header">
                <div class="modal-title"><span>🚪</span> {{ __('Door Knock Request') }}</div>
            </div>
            <div class="knock-alert-box">
                <div style="font-size: 32px;">🚪✊</div>
                <strong id="knock-requester-name" style="font-size: 14px; color: var(--text-primary);">A colleague is knocking...</strong>
                <span style="font-size: 12px; color: var(--text-secondary);">{{ __('They are requesting permission to enter this locked private room.') }}</span>
                <div style="display: flex; gap: 10px; margin-top: 6px;">
                    <button onclick="respondToKnock(true)" class="action-link-btn" style="flex: 1; justify-content: center; background: var(--brand-primary); color: white;">
                        ✅ {{ __('Let In') }}
                    </button>
                    <button onclick="respondToKnock(false)" class="action-link-btn" style="flex: 1; justify-content: center; background: rgba(239, 68, 68, 0.15); color: #F87171;">
                        ✕ {{ __('Decline') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 7. In-Office My Tasks Drawer & Quick Time Tracker ── -->
    <div class="task-drawer" id="my-task-drawer">
        <div style="padding: 16px; background: var(--bg-surface); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 14px; font-weight: 900; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <span>📝</span> <span>{{ __('My Tasks & Time Tracker (مهامي وتتبع الوقت)') }}</span>
            </div>
            <button onclick="closeMyTaskDrawer()" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer;">✕</button>
        </div>

        <!-- Active Running Task Hero Card -->
        <div id="office-active-timer-hero" style="display: none; padding: 14px 16px; background: rgba(16, 185, 129, 0.12); border-bottom: 1px solid rgba(52, 211, 153, 0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 10px; font-weight: 800; color: var(--brand-primary); text-transform: uppercase; display: flex; align-items: center; gap: 4px;">
                    <span class="live-dot" style="width: 6px; height: 6px;"></span>
                    {{ __('Active Task Timer (المهمة الجارية)') }}
                </span>
                <span id="office-timer-clock" style="font-family: monospace; font-size: 15px; font-weight: 900; color: #34D399; letter-spacing: 1px;">00:00:00</span>
            </div>
            <div id="office-timer-title" style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;"></div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span id="office-timer-project" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);"></span>
                <button onclick="stopActiveOfficeTask()" class="tactile-btn" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #F87171; padding: 4px 12px; font-size: 11px;">
                    ⏹️ {{ __('Stop Task (إيقاف)') }}
                </button>
            </div>
        </div>

        <!-- Task Search & Filters -->
        <div style="padding: 10px 14px; border-bottom: 1px solid var(--border-card); background: var(--bg-dock);">
            <input type="text" id="office-task-search" placeholder="{{ __('Search assigned tasks...') }}" oninput="filterOfficeTasks(this.value)" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; font-size: 12px; color: var(--text-primary); outline: none;">
        </div>

        <!-- Task List Scroll Container -->
        <div id="office-tasks-list" style="flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px;">
                ⏳ {{ __('Loading your assigned tasks...') }}
            </div>
        </div>
    </div>

    <!-- ── 8. Smart Inactivity / Idle Check Modal ("Are you still online?") ── -->
    <div id="office-idle-check-modal" class="modal-overlay" style="display: none; z-index: 1000005;">
        <div class="modal-card" style="max-width: 440px; text-align: center; padding: 28px 24px; border: 2px solid rgba(214, 162, 58, 0.5); box-shadow: 0 20px 60px rgba(0,0,0,0.8), 0 0 30px rgba(214, 162, 58, 0.25);">
            <div style="font-size: 44px; margin-bottom: 10px;">⏰</div>
            <h3 style="font-size: 17px; font-weight: 900; color: #F59E0B; margin-bottom: 8px;">
                {{ __('Are you still online? (تأكيد التواجد والنشاط)') }}
            </h3>
            <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 16px;">
                {{ __('We noticed you have been inactive for a while. Please confirm you are still working so your office attendance time continues calculating.') }}
            </p>

            <!-- Countdown Timer Progress Bar -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px;">
                    <span>⏳ {{ __('Auto-pause in:') }}</span>
                    <span id="idle-countdown-clock" style="font-family: monospace; font-weight: 900; color: #F59E0B; font-size: 14px;">03:00</span>
                </div>
                <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 4px; overflow: hidden;">
                    <div id="idle-countdown-bar" style="width: 100%; height: 100%; background: linear-gradient(90deg, #F59E0B, #EF4444); transition: width 1s linear;"></div>
                </div>
            </div>

            <button type="button" onclick="confirmUserPresence()" class="tactile-btn btn-primary" style="width: 100%; padding: 12px 24px; font-size: 14px; justify-content: center; background: #10B981; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);">
                🟢 {{ __("Yes, I'm Online (نعم، أنا متواجد)") }}
            </button>
        </div>
    </div>

    <!-- ── 9. Inactivity Paused Fullscreen Overlay ── -->
    <div id="office-idle-paused-overlay" class="modal-overlay" style="display: none; z-index: 1000006; background: rgba(5, 12, 8, 0.95); backdrop-filter: blur(20px);">
        <div class="modal-card" style="max-width: 480px; text-align: center; padding: 32px 24px; border: 1px solid rgba(52, 211, 153, 0.3);">
            <div style="font-size: 52px; margin-bottom: 12px;">⏸️</div>
            <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin-bottom: 10px;">
                {{ __('Office Time Tracking Paused (تم إيقاف احتساب وقت الحضور)') }}
            </h3>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px;">
                {{ __('Your office session calculation was paused due to inactivity. Click below whenever you are ready to resume attendance.') }}
            </p>

            <button type="button" onclick="resumeUserPresenceFromPaused()" class="tactile-btn btn-primary" style="width: 100%; padding: 13px 24px; font-size: 14px; justify-content: center; background: #10B981;">
                ▶️ {{ __('Resume Presence (استئناف التواجد والحضور)') }}
            </button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast-bubble" class="toast-bubble"></div>

    <!-- ── LiveKit Client SFU SDK (Self-Hosted on Server) & WebRTC Media Layer ── -->
