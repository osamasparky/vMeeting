    <!-- ── Modals & Overlays ── -->

    <!-- 0a. Device Settings & Pre-Join Test Modal -->
    <div id="device-settings-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px;">
            <div class="modal-header">
                <div class="modal-title"><span><span class="material-symbols-rounded">settings</span></span> {{ __('Audio & Video Device Settings') }}</div>
                <button onclick="closeDeviceSettingsModal()" style="background:none; border:none; color:var(--ula-text-muted); font-size:20px; cursor:pointer;"><span class="material-symbols-rounded">close</span></button>
            </div>
            
            <!-- Video Preview Box -->
            <div style="position: relative; width: 100%; height: 200px; background: var(--ula-palm-950); border-radius: 12px; overflow: hidden; border: 1px solid var(--ula-border-subtle); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                <video id="device-preview-video" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover;"></video>
                <div id="device-no-preview" style="display: none; color: var(--ula-text-muted); font-size: 12px; font-weight: 700;"><span class="material-symbols-rounded">photo_camera</span> {{ __('Camera Preview Inactive') }}</div>
            </div>

            <!-- Mic Volume Level Meter -->
            <div style="margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 4px;">
                    <span><span class="material-symbols-rounded">mic</span> {{ __('Microphone Input Test') }}</span>
                    <span id="mic-level-val">0%</span>
                </div>
                <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 4px; overflow: hidden;">
                    <div id="mic-level-bar" style="width: 0%; height: 100%; background: var(--ula-status-success); transition: width 0.08s ease;"></div>
                </div>
            </div>

            <!-- Selectors -->
            <div class="input-group">
                <label class="input-label"><span class="material-symbols-rounded">videocam</span> {{ __('Camera Device') }}</label>
                <select class="styled-input" id="select-video-input" onchange="onCameraDeviceChanged(this.value)">
                    <option value="default">{{ __('Default Camera') }}</option>
                </select>
            </div>
            <div class="input-group">
                <label class="input-label"><span class="material-symbols-rounded">mic</span> {{ __('Microphone Device') }}</label>
                <select class="styled-input" id="select-audio-input" onchange="onMicDeviceChanged(this.value)">
                    <option value="default">{{ __('Default Microphone') }}</option>
                </select>
            </div>
            <div class="input-group">
                <label class="input-label"><span class="material-symbols-rounded">volume_up</span> {{ __('Audio Output Speaker') }}</label>
                <select class="styled-input" id="select-audio-output" onchange="onSpeakerDeviceChanged(this.value)">
                    <option value="default">{{ __('Default Speaker') }}</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px; margin-top: 8px;">
                <button onclick="closeDeviceSettingsModal()" class="action-link-btn" style="flex: 1; background: var(--ula-palm-900); color: var(--ula-white); justify-content: center; padding: 10px;">
                    <span class="material-symbols-rounded">check</span> {{ __('Done & Save Settings') }}
                </button>
            </div>
        </div>
    </div>

    <!-- 0b. WebRTC & Network Diagnostics Modal -->
    <div id="diagnostics-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 600px;">
            <div class="modal-header">
                <div class="modal-title"><span><span class="material-symbols-rounded">stethoscope</span></span> {{ __('WebRTC & Media Diagnostics') }}</div>
                <button onclick="closeDiagnosticsModal()" style="background:none; border:none; color:var(--ula-text-muted); font-size:20px; cursor:pointer;"><span class="material-symbols-rounded">close</span></button>
            </div>

            <div id="diag-loading" style="text-align: center; padding: 20px; color: var(--ula-text-muted); font-size: 13px;">
                ⏳ {{ __('Running automated WebRTC & STUN/TURN checks...') }}
            </div>

            <div id="diag-content" style="display: none; flex-direction: column; gap: 12px;">
                <!-- Overall Status Banner -->
                <div id="diag-overall-box" style="padding: 12px 16px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(52, 211, 153, 0.3); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 10px; font-weight: 800; color: var(--ula-palm-900); text-transform: uppercase;">{{ __('Overall Connection Quality') }}</div>
                        <div id="diag-overall-text" style="font-size: 16px; font-weight: 900; color: var(--ula-status-success);">{{ __('Excellent') }}</div>
                    </div>
                    <span id="diag-overall-badge" style="font-size: 24px;"><span class="material-symbols-rounded" style="font-size: 14px;">circle</span></span>
                </div>

                <!-- Diagnostics Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--ula-text-muted); font-weight: 800;"><span class="material-symbols-rounded">photo_camera</span> {{ __('Camera Access') }}</div>
                        <div id="diag-cam-status" style="font-size: 13px; font-weight: 800; color: var(--ula-status-success);"><span class="material-symbols-rounded">check</span> {{ __('Verified') }}</div>
                    </div>
                    <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--ula-text-muted); font-weight: 800;"><span class="material-symbols-rounded">mic</span> {{ __('Microphone Access') }}</div>
                        <div id="diag-mic-status" style="font-size: 13px; font-weight: 800; color: var(--ula-status-success);"><span class="material-symbols-rounded">check</span> {{ __('Verified') }}</div>
                    </div>
                    <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--ula-text-muted); font-weight: 800;"><span class="material-symbols-rounded">bolt</span> {{ __('Internet Ping (RTT)') }}</div>
                        <div id="diag-ping-status" style="font-size: 13px; font-weight: 800; color: var(--ula-status-success);">32 ms</div>
                    </div>
                    <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--ula-text-muted); font-weight: 800;"><span class="material-symbols-rounded">public</span> {{ __('STUN & TURN Relay') }}</div>
                        <div id="diag-turn-status" style="font-size: 13px; font-weight: 800; color: var(--ula-status-success);"><span class="material-symbols-rounded">check</span> {{ __('Active (Coturn)') }}</div>
                    </div>
                </div>

                <!-- Telemetry Stats Table -->
                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px; font-family: monospace; font-size: 11px; line-height: 1.6; color: var(--ula-text-secondary);">
                    <div style="display: flex; justify-content: space-between;"><span>SFU Host:</span> <span id="diag-livekit-host" style="color: var(--ula-accent-default);">wss://nextspace.munazzah.com/livekit</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Packet Loss:</span> <span id="diag-packet-loss" style="color: var(--ula-status-success);">0.0%</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Jitter:</span> <span id="diag-jitter" style="color: var(--ula-status-success);">4 ms</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Framerate (FPS):</span> <span id="diag-fps" style="color: var(--ula-status-success);">30 FPS</span></div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button onclick="runDiagnosticsCheck()" class="action-link-btn" style="flex: 1; justify-content: center;"><span class="material-symbols-rounded">refresh</span> {{ __('Re-run Check') }}</button>
                    <button onclick="copyDiagnosticsReport()" class="action-link-btn" style="flex: 1; background: var(--ula-highlight-default); color: var(--ula-white); justify-content: center;"><span class="material-symbols-rounded">content_copy</span> {{ __('Copy Report for Support') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. User Spotlight & Live Video Modal -->
    <div id="user-spotlight-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 650px; padding: 20px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 12px;">
                    <div id="spotlight-avatar-box" style="width: 42px; height: 42px; border-radius: 12px; overflow: hidden; background: var(--ula-surface-card); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; color: var(--ula-palm-900); border: 2px solid var(--ula-border-subtle);">
                    </div>
                    <div>
                        <div id="spotlight-user-name" style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary);"></div>
                        <div id="spotlight-user-subtitle" style="font-size: 11px; color: var(--ula-text-secondary);"></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button id="spotlight-ring-btn" onclick="ringSpotlightUser()" class="action-link-btn" style="background: rgba(245, 158, 11, 0.2); border-color: rgba(245, 158, 11, 0.4); color: var(--ula-gold-400); font-size: 11px; padding: 4px 10px;">
                        <span><span class="material-symbols-rounded">notifications</span></span> {{ __('Ring') }}
                    </button>
                    <button id="spotlight-wave-btn" onclick="sendWaveToSpotlightUser()" class="action-link-btn" style="background: rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.4); color: var(--ula-accent-default); font-size: 11px; padding: 4px 10px;">
                        <span><span class="material-symbols-rounded">front_hand</span></span> {{ __('Wave') }}
                    </button>
                    <button onclick="closeUserSpotlight()" style="background:none; border:none; color:var(--ula-text-muted); font-size:20px; cursor:pointer;"><span class="material-symbols-rounded">close</span></button>
                </div>
            </div>

            <!-- Spotlight Video Viewport -->
            <div id="spotlight-video-container" style="position: relative; width: 100%; height: 320px; background: var(--ula-palm-950); border-radius: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid var(--ula-border-subtle); box-shadow: inset 0 0 40px rgba(0,0,0,0.8);">
                <video id="spotlight-video-player" autoplay playsinline style="width: 100%; height: 100%; object-fit: contain; display: none;"></video>
                <div id="spotlight-no-video" style="display: flex; flex-direction: column; align-items: center; gap: 12px; color: var(--ula-text-muted);">
                    <div id="spotlight-big-avatar" style="width: 86px; height: 86px; border-radius: 24px; background: rgba(16, 185, 129, 0.15); border: 2px solid var(--ula-palm-900); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; color: var(--ula-status-success); overflow: hidden;">
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
                            <div style="font-size: 10px; font-weight: 800; color: var(--ula-palm-900); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Currently Working On:') }}</div>
                            <div id="spotlight-timer-task" style="font-size: 13px; font-weight: 800; color: var(--ula-text-primary);"></div>
                        </div>
                    </div>
                    <div id="spotlight-timer-clock" style="font-family: monospace; font-size: 16px; font-weight: 900; color: var(--ula-status-success); letter-spacing: 1px;"></div>
                </div>

                <!-- Assigned Tasks List -->
                <div>
                    <div style="font-size: 11px; font-weight: 900; color: var(--ula-text-secondary); text-transform: uppercase; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <span><span class="material-symbols-rounded">assignment</span> {{ __('Assigned Tasks & Progress') }}</span>
                        <span id="spotlight-tasks-count" class="guest-badge" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(52, 211, 153, 0.3); color: var(--ula-status-success);">0 Tasks</span>
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
                <div class="modal-title"><span><span class="material-symbols-rounded">videocam</span></span> {{ __('Office Live Cameras Gallery') }}</div>
                <button onclick="closeCameraGalleryModal()" style="background:none; border:none; color:var(--ula-text-muted); font-size:20px; cursor:pointer;"><span class="material-symbols-rounded">close</span></button>
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
                <div class="modal-title"><span><span class="material-symbols-rounded">bolt</span></span> {{ __('Instant Guest Invitation Link') }}</div>
                <button onclick="closeGuestModal()" style="background:none; border:none; color:var(--ula-text-muted); font-size:18px; cursor:pointer;"><span class="material-symbols-rounded">close</span></button>
            </div>
            <div class="input-group">
                <label class="input-label">{{ __('Select Target Meeting Room') }}</label>
                <select class="styled-input" id="invite-room-select">
                    @foreach($map->rooms as $r)
                        <option value="{{ $r->id }}">{{ $r->name }} ({{ ucfirst($r->type) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group">
                <label class="input-label">{{ __('Guest Label / Name') }}</label>
                <input type="text" class="styled-input" id="invite-guest-name" value="Investor / Partner">
            </div>
            <button onclick="generateGuestLink()" class="action-link-btn" style="background: var(--ula-palm-900); color: var(--ula-white); justify-content: center; padding: 12px; font-size: 13px;">
                <span class="material-symbols-rounded">bolt</span> {{ __('Generate Instant Guest Link') }}
            </button>
            <div id="guest-link-result" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--ula-border-subtle); border-radius: 12px; padding: 12px; flex-direction: column; gap: 8px;">
                <input type="text" id="guest-link-input" readonly class="styled-input" style="font-family: monospace; font-size: 11px;">
                <div style="display: flex; gap: 8px;">
                    <button onclick="copyGuestLink()" class="action-link-btn" style="flex: 1; justify-content: center;"><span class="material-symbols-rounded">content_copy</span> {{ __('Copy Link') }}</button>
                    <button onclick="openGuestInNewWindow()" class="action-link-btn" style="flex: 1; background: var(--ula-highlight-default); color: var(--ula-white); justify-content: center;"><span class="material-symbols-rounded">rocket_launch</span> {{ __('Open Guest') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2b. Live Online Occupants Modal -->
    <div id="occupants-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><span><span class="material-symbols-rounded">group</span></span> {{ __('Active People in Office') }}</div>
                <button onclick="closeOccupantsModal()" style="background:none; border:none; color:var(--ula-text-muted); font-size:18px; cursor:pointer;"><span class="material-symbols-rounded">close</span></button>
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
                <div class="modal-title"><span><span class="material-symbols-rounded">folder</span></span> <span id="room-files-title">{{ __('Room Documents & Assets') }}</span></div>
                <button onclick="closeRoomFilesModal()" style="background:none; border:none; color:var(--ula-text-muted); font-size:18px; cursor:pointer;"><span class="material-symbols-rounded">close</span></button>
            </div>
            
            <!-- Upload Box -->
            <div style="background: var(--ula-surface-page); border: 2px dashed var(--ula-border-subtle); border-radius: 14px; padding: 18px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <input type="file" id="room-file-input" style="display:none;" onchange="handleRoomFileUpload(this)">
                <span style="font-size: 28px;"><span class="material-symbols-rounded">upload</span></span>
                <span style="font-size: 12px; font-weight: 700;">{{ __('Upload PDF, Slides, or Images to this Room Repository') }}</span>
                <button onclick="document.getElementById('room-file-input').click()" class="action-link-btn" style="background: var(--ula-palm-900); color: var(--ula-white);">
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
                <button class="wb-tool-btn active" id="wb-tool-pen" onclick="setWbTool('pen')" title="Pen"><span class="material-symbols-rounded">edit</span></button>
                <button class="wb-tool-btn" id="wb-tool-highlighter" onclick="setWbTool('highlighter')" title="Highlighter"><span class="material-symbols-rounded">ink_highlighter</span></button>
                <button class="wb-tool-btn" id="wb-tool-rect" onclick="setWbTool('rect')" title="Rectangle"><span class="material-symbols-rounded">crop_square</span></button>
                <button class="wb-tool-btn" id="wb-tool-circle" onclick="setWbTool('circle')" title="Circle">⭕</button>
                <button class="wb-tool-btn" id="wb-tool-arrow" onclick="setWbTool('arrow')" title="Arrow"><span class="material-symbols-rounded">arrow_forward</span></button>
                <button class="wb-tool-btn" id="wb-tool-line" onclick="setWbTool('line')" title="Straight Line"><span class="material-symbols-rounded">straighten</span></button>
                <button class="wb-tool-btn" id="wb-tool-text" onclick="setWbTool('text')" title="Add Text"><span class="material-symbols-rounded">text_fields</span></button>
                <button class="wb-tool-btn" id="wb-tool-note" onclick="setWbTool('note')" title="Sticky Note"><span class="material-symbols-rounded">push_pin</span></button>
                <button class="wb-tool-btn" id="wb-tool-eraser" onclick="setWbTool('eraser')" title="Eraser"><span class="material-symbols-rounded">cleaning_services</span></button>

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
                <button onclick="clearWhiteboard()" class="wb-tool-btn" title="Clear Board" style="color: var(--ula-status-danger);"><span class="material-symbols-rounded">delete</span></button>
                <button onclick="exportWhiteboard()" class="action-link-btn" style="padding: 6px 12px;"><span class="material-symbols-rounded">save</span> {{ __('Export PNG') }}</button>
                <button onclick="closeWhiteboardModal()" style="background:none; border:none; color:var(--ula-text-muted); font-size:20px; cursor:pointer; margin-inline-start: auto;"><span class="material-symbols-rounded">close</span></button>
            </div>

            <!-- Whiteboard Main Workspace & Sticky Notes Sidebar -->
            <div style="flex: 1; display: flex; position: relative; background: #FFFFFF; overflow: hidden;" id="wb-container">
                <!-- Whiteboard Drawing Canvas -->
                <canvas id="wb-canvas" style="flex: 1; width: 100%; height: 100%; cursor: crosshair;"></canvas>

                <!-- Whiteboard Sticky Notes Sidebar -->
                <div id="wb-sticky-sidebar" style="width: 260px; background: #F8FAFC; border-inline-start: 1px solid #E2E8F0; display: flex; flex-direction: column; z-index: 10;">
                    <!-- Sidebar Header -->
                    <div style="padding: 12px 14px; background: #FFFFFF; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 800; font-size: 12px; color: #1E293B;">
                            <span><span class="material-symbols-rounded">push_pin</span></span>
                            <span>{{ __('Sticky Notes') }}</span>
                        </div>
                        <button type="button" onclick="toggleWbStickyForm()" class="tactile-btn" style="background: #10B981; color: white; padding: 4px 8px; font-size: 11px; font-weight: 800; border-radius: 6px;">
                            + {{ __('Add') }}
                        </button>
                    </div>

                    <!-- Create Sticky Note Drawer/Form -->
                    <div id="wb-sticky-form" style="display: none; padding: 12px; background: #FEF3C7; border-bottom: 1px solid #FDE68A; flex-direction: column; gap: 8px;">
                        <textarea id="wb-sticky-text-input" placeholder="{{ __('Write note content...') }}" rows="3" style="width: 100%; background: #FFFFFF; border: 1px solid #F59E0B; border-radius: 8px; padding: 8px; font-size: 12px; color: #78350F; outline: none; resize: none; font-family: Cairo, Inter, sans-serif;"></textarea>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <!-- Color Swatches for sticky note -->
                            <div style="display: flex; gap: 4px;" id="wb-sticky-color-swatches">
                                <div class="sticky-color-pick active" data-color="#FEF08A" data-border="#FACC15" data-text="#713F12" style="width: 18px; height: 18px; border-radius: 50%; background: #FEF08A; border: 2px solid #CA8A04; cursor: pointer;" onclick="selectWbStickyColor('#FEF08A', '#FACC15', '#713F12', this)"></div>
                                <div class="sticky-color-pick" data-color="#BAE6FD" data-border="#38BDF8" data-text="#0369A1" style="width: 18px; height: 18px; border-radius: 50%; background: #BAE6FD; border: 1px solid #38BDF8; cursor: pointer;" onclick="selectWbStickyColor('#BAE6FD', '#38BDF8', '#0369A1', this)"></div>
                                <div class="sticky-color-pick" data-color="#BBF7D0" data-border="#4ADE80" data-text="#15803D" style="width: 18px; height: 18px; border-radius: 50%; background: #BBF7D0; border: 1px solid #4ADE80; cursor: pointer;" onclick="selectWbStickyColor('#BBF7D0', '#4ADE80', '#15803D', this)"></div>
                                <div class="sticky-color-pick" data-color="#FBCFE8" data-border="#F472B6" data-text="#BE185D" style="width: 18px; height: 18px; border-radius: 50%; background: #FBCFE8; border: 1px solid #F472B6; cursor: pointer;" onclick="selectWbStickyColor('#FBCFE8', '#F472B6', '#BE185D', this)"></div>
                                <div class="sticky-color-pick" data-color="#DDD6FE" data-border="#A78BFA" data-text="#6D28D9" style="width: 18px; height: 18px; border-radius: 50%; background: #DDD6FE; border: 1px solid #A78BFA; cursor: pointer;" onclick="selectWbStickyColor('#DDD6FE', '#A78BFA', '#6D28D9', this)"></div>
                            </div>
                            <div style="display: flex; gap: 4px;">
                                <button type="button" onclick="saveWbStickyNote()" class="tactile-btn" style="background: #D97706; color: white; padding: 4px 10px; font-size: 11px; font-weight: 800; border-radius: 6px;">
                                    <span class="material-symbols-rounded">save</span> {{ __('Save') }}
                                </button>
                                <button type="button" onclick="toggleWbStickyForm()" style="background: none; border: none; color: #92400E; font-size: 14px; cursor: pointer;">
                                    <span class="material-symbols-rounded">close</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sticky Notes Cards Feed -->
                    <div id="wb-sticky-list" style="flex: 1; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 8px;">
                        <div style="text-align: center; color: #94A3B8; font-size: 11px; padding: 20px;">
                            <span class="material-symbols-rounded">push_pin</span> {{ __('No sticky notes saved yet. Click + Add to save notes to your office whiteboard.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Recordings Gallery Modal -->
    <div id="recordings-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 900px; height: 80vh;">
            <div class="modal-header">
                <div class="modal-title"><span><span class="material-symbols-rounded">video_library</span></span> {{ __('Session Recordings & Gallery') }}</div>
                <button onclick="closeRecordingsGallery()" style="background:none; border:none; color:var(--ula-text-muted); font-size:18px; cursor:pointer;"><span class="material-symbols-rounded">close</span></button>
            </div>
            <div id="recordings-list" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;"></div>
        </div>
    </div>

    <!-- 6. Knock Alert Dialog Modal (For Occupants) -->
    <div id="knock-alert-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 420px;">
            <div class="modal-header">
                <div class="modal-title"><span><span class="material-symbols-rounded">door_front</span></span> {{ __('Door Knock Request') }}</div>
            </div>
            <div class="knock-alert-box">
                <div style="font-size: 32px;"><span class="material-symbols-rounded">back_hand</span></div>
                <strong id="knock-requester-name" style="font-size: 14px; color: var(--ula-text-primary);">A colleague is knocking...</strong>
                <span style="font-size: 12px; color: var(--ula-text-secondary);">{{ __('They are requesting permission to enter this locked private room.') }}</span>
                <div style="display: flex; gap: 10px; margin-top: 6px;">
                    <button onclick="respondToKnock(true)" class="action-link-btn" style="flex: 1; justify-content: center; background: var(--ula-palm-900); color: var(--ula-white);">
                        <span class="material-symbols-rounded">check_circle</span> {{ __('Let In') }}
                    </button>
                    <button onclick="respondToKnock(false)" class="action-link-btn" style="flex: 1; justify-content: center; background: rgba(239, 68, 68, 0.15); color: var(--ula-status-danger);">
                        <span class="material-symbols-rounded">close</span> {{ __('Decline') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 7. In-Office My Tasks Drawer & Quick Time Tracker ── -->
    <div class="task-drawer" id="my-task-drawer">
        <div style="padding: 16px; background: var(--ula-surface-card); border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 14px; font-weight: 900; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                <span><span class="material-symbols-rounded">edit_note</span></span> <span>{{ __('My Tasks & Time Tracker') }}</span>
            </div>
            <button onclick="closeMyTaskDrawer()" style="background: none; border: none; color: var(--ula-text-muted); font-size: 18px; cursor: pointer;"><span class="material-symbols-rounded">close</span></button>
        </div>

        <!-- Active Running Task Hero Card -->
        <div id="office-active-timer-hero" style="display: none; padding: 14px 16px; background: rgba(16, 185, 129, 0.12); border-bottom: 1px solid rgba(52, 211, 153, 0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 10px; font-weight: 800; color: var(--ula-palm-900); text-transform: uppercase; display: flex; align-items: center; gap: 4px;">
                    <span class="live-dot" style="width: 6px; height: 6px;"></span>
                    {{ __('Active Task Timer') }}
                </span>
                <span id="office-timer-clock" style="font-family: monospace; font-size: 15px; font-weight: 900; color: var(--ula-status-success); letter-spacing: 1px;">00:00:00</span>
            </div>
            <div id="office-timer-title" style="font-size: 13px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px;"></div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span id="office-timer-project" style="font-size: 11px; font-weight: 700; color: var(--ula-text-secondary);"></span>
                <button onclick="stopActiveOfficeTask()" class="tactile-btn" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: var(--ula-status-danger); padding: 4px 12px; font-size: 11px;">
                    ⏹️ {{ __('Stop Task') }}
                </button>
            </div>
        </div>

        <!-- Task Search & Filters -->
        <div style="padding: 10px 14px; border-bottom: 1px solid var(--ula-border-subtle); background: var(--ula-surface-capsule-strong);">
            <input type="text" id="office-task-search" placeholder="{{ __('Search assigned tasks...') }}" oninput="filterOfficeTasks(this.value)" style="width: 100%; background: var(--ula-surface-page); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 8px 12px; font-size: 12px; color: var(--ula-text-primary); outline: none;">
        </div>

        <!-- Task List Scroll Container -->
        <div id="office-tasks-list" style="flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
            <div style="text-align: center; padding: 20px; color: var(--ula-text-muted); font-size: 12px;">
                ⏳ {{ __('Loading your assigned tasks...') }}
            </div>
        </div>
    </div>

    <!-- ── 8. Smart Inactivity / Idle Check Modal ("Are you still online?") ── -->
    <div id="office-idle-check-modal" class="modal-overlay" style="display: none; z-index: 1000005;">
        <div class="modal-card" style="max-width: 440px; text-align: center; padding: 28px 24px; border: 2px solid rgba(214, 162, 58, 0.5); box-shadow: 0 20px 60px rgba(0,0,0,0.8), 0 0 30px rgba(214, 162, 58, 0.25);">
            <div style="font-size: 44px; margin-bottom: 10px;">⏰</div>
            <h3 style="font-size: 17px; font-weight: 900; color: var(--ula-gold-500); margin-bottom: 8px;">
                {{ __('Are you still online?') }}
            </h3>
            <p style="font-size: 13px; color: var(--ula-text-secondary); line-height: 1.6; margin-bottom: 16px;">
                {{ __('We noticed you have been inactive for a while. Please confirm you are still working so your office attendance time continues calculating.') }}
            </p>

            <!-- Countdown Timer Progress Bar -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 800; color: var(--ula-text-muted); margin-bottom: 6px;">
                    <span>⏳ {{ __('Auto-pause in:') }}</span>
                    <span id="idle-countdown-clock" style="font-family: monospace; font-weight: 900; color: var(--ula-gold-500); font-size: 14px;">03:00</span>
                </div>
                <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 4px; overflow: hidden;">
                    <div id="idle-countdown-bar" style="width: 100%; height: 100%; background: linear-gradient(90deg, var(--ula-gold-500), var(--ula-status-danger)); transition: width 1s linear;"></div>
                </div>
            </div>

            <button type="button" onclick="confirmUserPresence()" class="tactile-btn btn-primary" style="width: 100%; padding: 12px 24px; font-size: 14px; justify-content: center; background: var(--ula-status-success); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);">
                <span class="material-symbols-rounded" style="font-size: 14px;">circle</span> {{ __("Yes, I'm Online") }}
            </button>
        </div>
    </div>

    <!-- ── 9. Inactivity Paused Fullscreen Overlay ── -->
    <div id="office-idle-paused-overlay" class="modal-overlay" style="display: none; z-index: 1000006; background: rgba(5, 12, 8, 0.95); backdrop-filter: blur(20px);">
        <div class="modal-card" style="max-width: 480px; text-align: center; padding: 32px 24px; border: 1px solid rgba(52, 211, 153, 0.3);">
            <div style="font-size: 52px; margin-bottom: 12px;">⏸️</div>
            <h3 style="font-size: 18px; font-weight: 900; color: var(--ula-text-primary); margin-bottom: 10px;">
                {{ __('Office Time Tracking Paused') }}
            </h3>
            <p style="font-size: 13px; color: var(--ula-text-muted); line-height: 1.6; margin-bottom: 24px;">
                {{ __('Your office session calculation was paused due to inactivity. Click below whenever you are ready to resume attendance.') }}
            </p>

            <button type="button" onclick="resumeUserPresenceFromPaused()" class="tactile-btn btn-primary" style="width: 100%; padding: 13px 24px; font-size: 14px; justify-content: center; background: var(--ula-status-success);">
                ▶️ {{ __('Resume Presence') }}
            </button>
        </div>
    </div>

    <!-- ── 10. Direct Ring Attention Alert Modal (Incoming Ring) ── -->
    <div id="incoming-ring-modal" class="modal-overlay" style="display: none; z-index: 1000007;">
        <div class="modal-card" style="max-width: 440px; text-align: center; padding: 26px 22px; border: 2px solid var(--ula-gold-500); box-shadow: 0 20px 60px rgba(0,0,0,0.85), 0 0 40px rgba(245, 158, 11, 0.4); animation: pulseRing 1.2s infinite ease-in-out;">
            <div style="font-size: 54px; margin-bottom: 8px;"><span class="material-symbols-rounded">notifications</span></div>
            <h3 id="incoming-ring-title" style="font-size: 18px; font-weight: 900; color: var(--ula-gold-400); margin-bottom: 6px;">
                {{ __('Incoming Ring Call') }}
            </h3>
            <p id="incoming-ring-desc" style="font-size: 13px; color: var(--ula-text-secondary); line-height: 1.6; margin-bottom: 20px;">
                {{ __('A colleague is ringing you for immediate attention.') }}
            </p>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="acceptIncomingRing()" class="action-link-btn" style="flex: 1; justify-content: center; background: var(--ula-status-success); color: var(--ula-white); padding: 12px; font-size: 14px; font-weight: 800;">
                    <span class="material-symbols-rounded">call</span> {{ __('Answer & Focus') }}
                </button>
                <button type="button" onclick="dismissIncomingRing()" class="action-link-btn" style="flex: 1; justify-content: center; background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: var(--ula-status-danger); padding: 12px; font-size: 14px; font-weight: 800;">
                    <span class="material-symbols-rounded">close</span> {{ __('Dismiss') }}
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes pulseRing {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }
    </style>

    <!-- ── 11. Interactive Sticky Note Viewer Modal ── -->
    <div id="sticky-note-modal" class="modal-overlay" style="display: none; z-index: 1000008; background: rgba(5, 12, 8, 0.75); backdrop-filter: blur(12px);">
        <div id="sticky-note-card" class="modal-card" style="max-width: 420px; background: #FEF3C7; color: #78350F; border: 2px solid #F59E0B; box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 30px rgba(245,158,11,0.25); border-radius: 16px; padding: 24px; position: relative; transform: rotate(-1deg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px dashed rgba(120, 53, 15, 0.3); padding-bottom: 8px;">
                <div style="display: flex; align-items: center; gap: 6px; font-weight: 900; font-size: 14px;">
                    <span style="font-size: 20px;"><span class="material-symbols-rounded">push_pin</span></span>
                    <span id="sticky-modal-title">{{ __('Workplace Sticky Note') }}</span>
                </div>
                <button type="button" onclick="closeStickyNoteModal()" style="background: none; border: none; font-size: 20px; color: #78350F; cursor: pointer; line-height: 1;"><span class="material-symbols-rounded">close</span></button>
            </div>
            <div id="sticky-modal-body" style="font-size: 15px; font-weight: 700; line-height: 1.7; white-space: pre-wrap; word-break: break-word; min-height: 80px; padding: 6px 0; color: #92400E; font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;">
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 14px;">
                <button type="button" onclick="closeStickyNoteModal()" style="background: #D97706; color: white; border: none; border-radius: 8px; padding: 6px 16px; font-size: 12px; font-weight: 800; cursor: pointer;">
                    <span class="material-symbols-rounded">check</span> {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>

    <!-- ── 12. Interactive Custom Image Lightbox Modal ── -->
    <div id="custom-image-modal" class="modal-overlay" style="display: none; z-index: 1000008; background: rgba(0, 0, 0, 0.88); backdrop-filter: blur(16px);" onclick="closeCustomImageModal()">
        <div class="modal-card" style="max-width: 85vw; max-height: 85vh; padding: 12px; background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(255,255,255,0.15); display: flex; flex-direction: column; align-items: center;" onclick="event.stopPropagation()">
            <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 6px 12px 10px;">
                <span id="custom-image-modal-title" style="font-size: 14px; font-weight: 800; color: var(--ula-text-primary);"><span class="material-symbols-rounded">image</span> {{ __('Image Viewer') }}</span>
                <button type="button" onclick="closeCustomImageModal()" style="background: none; border: none; font-size: 22px; color: var(--ula-text-muted); cursor: pointer;"><span class="material-symbols-rounded">close</span></button>
            </div>
            <div style="overflow: auto; max-height: 75vh; display: flex; align-items: center; justify-content: center; width: 100%;">
                <img id="custom-image-modal-img" src="" alt="Custom Artwork" style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px;">
            </div>
        </div>
    </div>

    <!-- ── 13. Interactive Custom Link Modal ── -->
    <div id="custom-link-modal" class="modal-overlay" style="display: none; z-index: 1000008; background: rgba(5, 12, 8, 0.8); backdrop-filter: blur(14px);">
        <div class="modal-card" style="max-width: 460px; text-align: center; padding: 28px 24px; border: 1px solid rgba(59, 130, 246, 0.4);">
            <div style="font-size: 48px; margin-bottom: 10px;"><span class="material-symbols-rounded">link</span></div>
            <h3 id="custom-link-modal-title" style="font-size: 17px; font-weight: 900; color: var(--ula-accent-default); margin-bottom: 8px;">
                {{ __('Open Interactive Portal') }}
            </h3>
            <p id="custom-link-modal-url" style="font-size: 13px; color: var(--ula-text-muted); margin-bottom: 22px; word-break: break-all; background: rgba(15, 23, 42, 0.6); padding: 10px; border-radius: 8px; border: 1px solid var(--ula-border-subtle); font-family: monospace;">
            </p>
            <div style="display: flex; gap: 10px;">
                <a id="custom-link-modal-btn" href="#" target="_blank" rel="noopener noreferrer" class="action-link-btn" style="flex: 1; justify-content: center; background: var(--ula-accent-default); color: var(--ula-white); padding: 12px; font-size: 13px; font-weight: 800; text-decoration: none;">
                    <span class="material-symbols-rounded">rocket_launch</span> {{ __('Visit Link') }}
                </a>
                <button type="button" onclick="closeCustomLinkModal()" class="action-link-btn" style="background: rgba(255,255,255,0.1); color: var(--ula-text-muted); padding: 12px 18px; font-size: 13px; font-weight: 800;">
                    <span class="material-symbols-rounded">close</span> {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast-bubble" class="toast-bubble"></div>

    <!-- ── LiveKit Client SFU SDK (Self-Hosted on Server) & WebRTC Media Layer ── -->
