<div id="tab-profile" class="tab-view">
    <div class="page-header" style="margin-bottom: var(--ula-space-7, 24px);">
        <h1 class="page-title" style="font-size: var(--ula-size-h3, 24px); font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="font-size: 28px; color: var(--ula-palm-700, var(--ula-palm-700));">account_circle</span>
            <span>{{ __('User Profile & Account') }}</span>
        </h1>
        <p class="page-subtitle" style="font-size: var(--ula-size-sm, 14px); color: var(--ula-text-secondary);">{{ __('Manage your digital identity, avatar, contact details, skills, social links, and security.') }}</p>
    </div>

    <!-- Profile Hero Card (UlaSpace 3D Tactile) -->
    <div class="card" style="margin-bottom: var(--ula-space-7, 24px); border-radius: var(--ula-radius-xl, 20px); padding: 24px; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm); background: var(--ula-surface-card); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <!-- Avatar with Stylized Camera Badge -->
            <div style="position: relative; width: 92px; height: 92px; border-radius: var(--ula-radius-xl, 24px); overflow: hidden; box-shadow: var(--ula-shadow-md); border: 2.5px solid var(--ula-palm-700, var(--ula-palm-700)); background: var(--ula-palm-900, var(--ula-palm-900)); flex-shrink: 0;">
                <img id="user-profile-preview-avatar" src="{{ $user->avatar_url ? $user->avatar_url : '' }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; {{ $user->avatar_url ? '' : 'display: none;' }}">
                <div id="user-profile-avatar-fallback" style="width: 100%; height: 100%; display: {{ $user->avatar_url ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; color: var(--ula-sand-100, var(--ula-sand-100)); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            </div>

            <!-- User Identity Details -->
            <div style="flex: 1; min-width: 240px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h2 style="font-size: 22px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">{{ $user->name }}</h2>
                    @if($user->nickname)
                        <span class="nav-badge-pill" style="font-size: 12px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; dir: ltr;">{{ '@' . $user->nickname }}</span>
                    @endif
                    <span class="nav-badge-pill" style="background: rgba(30, 65, 47, 0.1); color: var(--ula-palm-700, var(--ula-palm-700)); font-size: 11px; font-weight: 700;">
                        {{ $membership->role->name ?? __('Member') }}
                    </span>
                </div>
                <div style="font-size: 13px; color: var(--ula-text-secondary); margin-top: 8px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">work</span>
                        <span>{{ $myProfile->job_title ?? __('Workspace Member') }}</span>
                    </span>
                    <span style="color: var(--ula-border-subtle);">•</span>
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">domain</span>
                        <span>{{ $organization->name }}</span>
                    </span>
                    <span style="color: var(--ula-border-subtle);">•</span>
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">mail</span>
                        <span style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ $user->email }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Profile Edit Form Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px; margin-bottom: 24px;">

        <!-- Left Card: Personal & Work Info -->
        <div class="card" style="margin-bottom: 0; border-radius: var(--ula-radius-xl, 20px); padding: 24px; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm);">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid var(--ula-border-subtle);">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-palm-700, var(--ula-palm-700));">badge</span>
                <span>{{ __('Personal & Professional Info') }}</span>
            </h3>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
                @csrf

                <!-- Profile Photo Upload -->
                <div>
                    <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 8px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">photo_camera</span>
                        <span>{{ __('Change Profile Photo') }}</span>
                    </label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="file" id="profileAvatarInput" name="avatar" accept="image/*" onchange="previewUserAvatar(this)" style="display: none;">
                        <button type="button" onclick="document.getElementById('profileAvatarInput').click()" class="tactile-btn btn-secondary" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">upload_file</span>
                            <span>{{ __('Choose New Photo') }}</span>
                        </button>
                        <span id="profileAvatarName" style="font-size: 12px; color: var(--ula-text-muted);">{{ __('JPEG, PNG, WebP up to 4MB') }}</span>
                    </div>
                </div>

                <!-- Full Name -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                        {{ __('Full Name') }} <span style="color: var(--ula-status-danger, var(--ula-terracotta-500));">*</span>
                    </label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600; outline: none; text-align: start;">
                </div>

                <!-- Nickname -->
                <div>
                    <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">alternate_email</span>
                        <span>{{ __('Nickname / Display Handle') }}</span>
                    </label>
                    <input type="text" name="nickname" value="{{ old('nickname', $user->nickname) }}" placeholder="e.g. sparky, alex_dev" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-palm-800, var(--ula-palm-800)); font-size: 13px; font-weight: 700; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; outline: none; text-align: start; dir: ltr;">
                </div>

                <!-- Email -->
                <div>
                    <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">mail</span>
                        <span>{{ __('Email Address') }}</span> <span style="color: var(--ula-status-danger, var(--ula-terracotta-500));">*</span>
                    </label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; outline: none; text-align: start;">
                </div>

                <!-- Date of Birth & Phone -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">calendar_today</span>
                            <span>{{ __('Date of Birth') }}</span>
                        </label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $myProfile->date_of_birth ? $myProfile->date_of_birth->format('Y-m-d') : '') }}" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 12px; font-weight: 600; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; outline: none; text-align: start;">
                    </div>
                    <div>
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">phone</span>
                            <span>{{ __('Phone Number') }}</span>
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $myProfile->phone) }}" placeholder="+966 50 123 4567" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 12px; font-weight: 600; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; outline: none; text-align: start; dir: ltr;">
                    </div>
                </div>

                <!-- Job Title & Work Mode -->
                <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">work</span>
                            <span>{{ __('Job Title') }}</span>
                        </label>
                        <input type="text" name="job_title" value="{{ old('job_title', $myProfile->job_title) }}" placeholder="e.g. Senior Fullstack Engineer" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600; outline: none; text-align: start;">
                    </div>
                    <div>
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">apartment</span>
                            <span>{{ __('Work Mode') }}</span>
                        </label>
                        <select name="work_mode" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 12px; font-weight: 600; outline: none;">
                            <option value="remote" {{ ($myProfile->work_mode ?? 'remote') === 'remote' ? 'selected' : '' }}>Remote</option>
                            <option value="hybrid" {{ ($myProfile->work_mode ?? '') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                            <option value="onsite" {{ ($myProfile->work_mode ?? '') === 'onsite' ? 'selected' : '' }}>On-site</option>
                        </select>
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">description</span>
                        <span>{{ __('Bio / About Me') }}</span>
                    </label>
                    <textarea name="bio" rows="3" placeholder="{{ __('Tell the team about yourself, your background, and what you love working on...') }}" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500; resize: vertical; outline: none; font-family: inherit; text-align: start;">{{ old('bio', $myProfile->bio) }}</textarea>
                </div>

                <div style="padding-top: 12px; border-top: 1px solid var(--ula-border-subtle);">
                    <button type="submit" class="nx-btn nx-btn--primary" style="padding: 10px 20px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="font-size: 18px;">save</span>
                        <span>{{ __('Save Profile Details') }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Cards Column: Hobbies, Skills, Social Links & Security -->
        <div style="display: flex; flex-direction: column; gap: 24px;">

            <!-- Hobbies, Skills & Social Links Card -->
            <div class="card" style="margin-bottom: 0; border-radius: var(--ula-radius-xl, 20px); padding: 24px; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm);">
                <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid var(--ula-border-subtle);">
                    <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-palm-700, var(--ula-palm-700));">interests</span>
                    <span>{{ __('Hobbies, Skills & Social Links') }}</span>
                </h3>

                <form method="POST" action="{{ route('profile.update') }}" style="display: flex; flex-direction: column; gap: 18px;">
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
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">sports_esports</span>
                            <span>{{ __('Hobbies & Interests') }}</span>
                        </label>
                        <input type="text" name="hobbies" value="{{ old('hobbies', $myProfile->hobbies) }}" placeholder="e.g. Chess, Reading, Video Games, Football, Travel" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 12px; font-weight: 600; outline: none; text-align: start;">
                    </div>

                    <!-- Skills -->
                    <div>
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">psychology</span>
                            <span>{{ __('Skills & Expertise') }}</span>
                        </label>
                        <input type="text" name="skills" value="{{ old('skills', $myProfile->skills) }}" placeholder="e.g. Laravel, PHP, Vue.js, Architecture, UI/UX, Docker" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 12px; font-weight: 600; outline: none; text-align: start;">
                    </div>

                    <!-- Social Media Links -->
                    @php
                        $social = (array)($myProfile->social_links ?? []);
                    @endphp
                    <div>
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">public</span>
                            <span>{{ __('Social Media & Portfolio Links') }}</span>
                        </label>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="min-width: 70px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">LinkedIn</span>
                                <input type="url" name="linkedin" value="{{ old('linkedin', $social['linkedin'] ?? '') }}" placeholder="https://linkedin.com/in/username" class="form-input" style="flex: 1; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 8px 12px; color: var(--ula-text-primary); font-size: 12px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; outline: none; text-align: start; dir: ltr;">
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="min-width: 70px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">GitHub</span>
                                <input type="url" name="github" value="{{ old('github', $social['github'] ?? '') }}" placeholder="https://github.com/username" class="form-input" style="flex: 1; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 8px 12px; color: var(--ula-text-primary); font-size: 12px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; outline: none; text-align: start; dir: ltr;">
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="min-width: 70px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">X (Twitter)</span>
                                <input type="url" name="twitter" value="{{ old('twitter', $social['twitter'] ?? '') }}" placeholder="https://x.com/username" class="form-input" style="flex: 1; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 8px 12px; color: var(--ula-text-primary); font-size: 12px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; outline: none; text-align: start; dir: ltr;">
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="min-width: 70px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">Website</span>
                                <input type="url" name="website" value="{{ old('website', $social['website'] ?? '') }}" placeholder="https://mywebsite.com" class="form-input" style="flex: 1; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 8px 12px; color: var(--ula-text-primary); font-size: 12px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; outline: none; text-align: start; dir: ltr;">
                            </div>
                        </div>
                    </div>

                    <!-- Personal Work Notes -->
                    <div>
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">sticky_note_2</span>
                            <span>{{ __('Work Notes & Preferences') }}</span>
                        </label>
                        <textarea name="notes" rows="3" placeholder="{{ __('Any personal work notes, focus time rules, or reminders...') }}" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 12px; font-weight: 500; resize: vertical; outline: none; font-family: inherit; text-align: start;">{{ old('notes', $myProfile->notes) }}</textarea>
                    </div>

                    <div style="padding-top: 12px; border-top: 1px solid var(--ula-border-subtle);">
                        <button type="submit" class="nx-btn nx-btn--primary" style="padding: 10px 20px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 18px;">save</span>
                            <span>{{ __('Save Hobbies, Skills & Social') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security & Password Change Card -->
            <div class="card" style="margin-bottom: 0; border-radius: var(--ula-radius-xl, 20px); padding: 24px; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm);">
                <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid var(--ula-border-subtle);">
                    <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-palm-700, var(--ula-palm-700));">lock</span>
                    <span>{{ __('Account Security & Password') }}</span>
                </h3>

                <form method="POST" action="{{ route('profile.password.update') }}" style="display: flex; flex-direction: column; gap: 18px;">
                    @csrf

                    <div>
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">key</span>
                            <span>{{ __('Current Password') }}</span>
                        </label>
                        <input type="password" name="current_password" required placeholder="••••••••" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; outline: none; text-align: start;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                                <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">password</span>
                                <span>{{ __('New Password') }}</span>
                            </label>
                            <input type="password" name="password" required placeholder="Min 8 chars" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; outline: none; text-align: start;">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                                <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">sync_lock</span>
                                <span>{{ __('Confirm Password') }}</span>
                            </label>
                            <input type="password" name="password_confirmation" required placeholder="Repeat new password" class="form-input" style="width: 100%; background: var(--ula-sand-100, var(--ula-sand-100)); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md, 12px); padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; outline: none; text-align: start;">
                        </div>
                    </div>

                    <div style="padding-top: 12px; border-top: 1px solid var(--ula-border-subtle);">
                        <button type="submit" class="nx-btn nx-btn--primary" style="padding: 10px 20px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 18px;">lock_reset</span>
                            <span>{{ __('Update Password') }}</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
