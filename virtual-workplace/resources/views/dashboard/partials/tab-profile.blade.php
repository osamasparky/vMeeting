                    <div id="tab-profile" class="tab-view">
            <div class="page-header" style="margin-bottom: 24px;">
                <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">👤 {{ __('User Profile & Account') }}</h1>
                <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage your digital identity, avatar, contact details, skills, social links, and security.') }}</p>
            </div>

            <!-- Profile Hero Card (3D Soft Neumorphic) -->
            <div class="card" style="margin-bottom: 24px; border-radius: var(--radius-xl); padding: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); background: var(--bg-surface); position: relative; overflow: hidden;">
                <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                    <!-- Avatar with Upload Overlay -->
                    <div style="position: relative; width: 88px; height: 88px; border-radius: 22px; overflow: hidden; box-shadow: var(--shadow-card); border: 2px solid var(--brand-forest); background: var(--accent-gradient); flex-shrink: 0;">
                        <img id="user-profile-preview-avatar" src="{{ $user->avatar_url ? $user->avatar_url : '' }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; {{ $user->avatar_url ? '' : 'display: none;' }}">
                        <div id="user-profile-avatar-fallback" style="width: 100%; height: 100%; display: {{ $user->avatar_url ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-size: 30px; font-weight: 900; color: white;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    </div>

                    <!-- User Identity Details -->
                    <div style="flex: 1; min-width: 220px;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h2 style="font-size: 24px; font-weight: 900; color: var(--text-primary); margin: 0;">{{ $user->name }}</h2>
                            @if($user->nickname)
                                <span class="nav-badge-pill" style="font-size: 12px; font-family: monospace;">{{ '@' . $user->nickname }}</span>
                            @endif
                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 11px;">{{ $membership->role->name ?? __('Member') }}</span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 6px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <span>💼 {{ $myProfile->job_title ?? __('Workspace Member') }}</span>
                            <span>•</span>
                            <span>🏢 {{ $organization->name }}</span>
                            <span>•</span>
                            <span>✉️ {{ $user->email }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Form (Personal, Professional, Hobbies, Skills, Social Links, Notes) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">

                <!-- Left Column: Personal, Contact & Work Details -->
                <div class="card" style="margin-bottom: 0; border-radius: var(--radius-xl); padding: 24px;">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                        <span>👤</span> {{ __('Personal & Professional Info') }}
                    </h3>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
                        @csrf

                        <!-- Profile Photo Upload -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                📷 {{ __('Change Profile Photo') }}
                            </label>
                            <input type="file" name="avatar" accept="image/*" onchange="previewUserAvatar(this)" style="font-size: 12px; color: var(--text-secondary); width: 100%; background: var(--bg-surface-subtle); padding: 10px; border-radius: 10px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <span style="font-size: 10px; color: var(--text-muted); display: block; margin-top: 4px;">{{ __('JPEG, PNG, WebP up to 4MB.') }}</span>
                        </div>

                        <!-- Full Name -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                {{ __('Full Name') }} <span style="color: #D96B5F;">*</span>
                            </label>
                            <input type="text" name="name" required value="{{ old('name', $user->name) }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                        </div>

                        <!-- Nickname -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                🏷️ {{ __('Nickname / Display Handle') }}
                            </label>
                            <input type="text" name="nickname" value="{{ old('nickname', $user->nickname) }}" placeholder="e.g. sparky, alex_dev" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--brand-forest); font-size: 13px; font-weight: 700; font-family: monospace; box-shadow: var(--shadow-inset-3d);">
                        </div>

                        <!-- Email -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                ✉️ {{ __('Email Address') }} <span style="color: #D96B5F;">*</span>
                            </label>
                            <input type="email" name="email" required value="{{ old('email', $user->email) }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                        </div>

                        <!-- Date of Birth & Phone in 2 Columns -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🎂 {{ __('Date of Birth') }}
                                </label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $myProfile->date_of_birth ? $myProfile->date_of_birth->format('Y-m-d') : '') }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    📱 {{ __('Phone Number') }}
                                </label>
                                <input type="text" name="phone" value="{{ old('phone', $myProfile->phone) }}" placeholder="+966 50 123 4567" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                        </div>

                        <!-- Job Title & Work Mode -->
                        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    💼 {{ __('Job Title') }}
                                </label>
                                <input type="text" name="job_title" value="{{ old('job_title', $myProfile->job_title) }}" placeholder="e.g. Senior Fullstack Engineer" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🏢 {{ __('Work Mode') }}
                                </label>
                                <select name="work_mode" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                                    <option value="remote" {{ ($myProfile->work_mode ?? 'remote') === 'remote' ? 'selected' : '' }}>🏠 Remote</option>
                                    <option value="hybrid" {{ ($myProfile->work_mode ?? '') === 'hybrid' ? 'selected' : '' }}>🔄 Hybrid</option>
                                    <option value="onsite" {{ ($myProfile->work_mode ?? '') === 'onsite' ? 'selected' : '' }}>🏢 On-site</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bio -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                📝 {{ __('Bio / About Me') }}
                            </label>
                            <textarea name="bio" rows="3" placeholder="{{ __('Tell the team about yourself, your background, and what you love working on...') }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 500; resize: vertical; box-shadow: var(--shadow-inset-3d);">{{ old('bio', $myProfile->bio) }}</textarea>
                        </div>

                        <div style="padding-top: 10px; border-top: 1px solid var(--border-color);">
                            <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: 13px; cursor: pointer;">
                                💾 {{ __('Save Profile Details') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Hobbies, Skills, Social Links & Security -->
                <div style="display: flex; flex-direction: column; gap: 24px;">

                    <!-- Hobbies, Skills & Social Links Card -->
                    <div class="card" style="margin-bottom: 0; border-radius: var(--radius-xl); padding: 24px;">
                        <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                            <span>🌟</span> {{ __('Hobbies, Skills & Social Links') }}
                        </h3>

                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
                            @csrf
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="nickname" value="{{ $user->nickname }}">
                            <input type="hidden" name="phone" value="{{ $myProfile->phone }}">
                            <input type="hidden" name="job_title" value="{{ $myProfile->job_title }}">
                            <input type="hidden" name="work_mode" value="{{ $myProfile->work_mode }}">
                            <input type="hidden" name="bio" value="{{ $myProfile->bio }}">
                            <input type="hidden" name="date_of_birth" value="{{ $myProfile->date_of_birth ? $myProfile->date_of_birth->format('Y-m-d') : '' }}">

                            <!-- Hobbies -->
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🎯 {{ __('Hobbies & Interests') }}
                                </label>
                                <input type="text" name="hobbies" value="{{ old('hobbies', $myProfile->hobbies) }}" placeholder="e.g. Chess, Reading, Video Games, Football, Travel" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>

                            <!-- Skills -->
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    ⚡ {{ __('Skills & Expertise') }}
                                </label>
                                <input type="text" name="skills" value="{{ old('skills', $myProfile->skills) }}" placeholder="e.g. Laravel, PHP, Vue.js, Architecture, UI/UX, Docker" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>

                            <!-- Social Media Links -->
                            @php
                                $social = (array)($myProfile->social_links ?? []);
                            @endphp
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🌐 {{ __('Social Media & Portfolio Links') }}
                                </label>
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 80px; font-size: 12px; font-weight: 800; color: var(--text-secondary);">LinkedIn</span>
                                        <input type="url" name="linkedin" value="{{ old('linkedin', $social['linkedin'] ?? '') }}" placeholder="https://linkedin.com/in/username" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; box-shadow: var(--shadow-inset-3d);">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 80px; font-size: 12px; font-weight: 800; color: var(--text-secondary);">GitHub</span>
                                        <input type="url" name="github" value="{{ old('github', $social['github'] ?? '') }}" placeholder="https://github.com/username" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; box-shadow: var(--shadow-inset-3d);">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 80px; font-size: 12px; font-weight: 800; color: var(--text-secondary);">X (Twitter)</span>
                                        <input type="url" name="twitter" value="{{ old('twitter', $social['twitter'] ?? '') }}" placeholder="https://x.com/username" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; box-shadow: var(--shadow-inset-3d);">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 80px; font-size: 12px; font-weight: 800; color: var(--text-secondary);">Website</span>
                                        <input type="url" name="website" value="{{ old('website', $social['website'] ?? '') }}" placeholder="https://mywebsite.com" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; box-shadow: var(--shadow-inset-3d);">
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Work Notes -->
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    📌 {{ __('Work Notes & Preferences') }}
                                </label>
                                <textarea name="notes" rows="3" placeholder="{{ __('Any personal work notes, focus time rules, or reminders...') }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 500; resize: vertical; box-shadow: var(--shadow-inset-3d);">{{ old('notes', $myProfile->notes) }}</textarea>
                            </div>

                            <div style="padding-top: 10px; border-top: 1px solid var(--border-color);">
                                <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: 13px; cursor: pointer;">
                                    💾 {{ __('Save Hobbies, Skills & Social') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security & Password Change Card -->
                    <div class="card" style="margin-bottom: 0; border-radius: var(--radius-xl); padding: 24px;">
                        <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                            <span>🔒</span> {{ __('Account Security & Password') }}
                        </h3>

                        <form method="POST" action="{{ route('profile.password.update') }}" style="display: flex; flex-direction: column; gap: 16px;">
                            @csrf

                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🔑 {{ __('Current Password') }}
                                </label>
                                <input type="password" name="current_password" required placeholder="••••••••" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; box-shadow: var(--shadow-inset-3d);">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                        🆕 {{ __('New Password') }}
                                    </label>
                                    <input type="password" name="password" required placeholder="Min 8 chars" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; box-shadow: var(--shadow-inset-3d);">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                        🔄 {{ __('Confirm Password') }}
                                    </label>
                                    <input type="password" name="password_confirmation" required placeholder="Repeat new password" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; box-shadow: var(--shadow-inset-3d);">
                                </div>
                            </div>

                            <div style="padding-top: 10px; border-top: 1px solid var(--border-color);">
                                <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: 13px; cursor: pointer;">
                                    🔒 {{ __('Update Password') }}
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- 8. PROJECTS PORTFOLIO TAB -->
