<div id="tab-profile" class="tab-view">
    <div class="page-header" style="margin-bottom: var(--nx-spacing-6);">
        <h1 class="page-title" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-1); display: flex; align-items: center; gap: var(--nx-spacing-2);">
            <span class="material-symbols-rounded" style="font-size: 28px; color: var(--nx-primary-500);">account_circle</span>
            <span>{{ __('User Profile & Account') }}</span>
        </h1>
        <p class="page-subtitle" style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary);">{{ __('Manage your digital identity, avatar, contact details, skills, social links, and security.') }}</p>
    </div>

    <!-- Profile Hero Card (3D Soft Neumorphic) -->
    <div class="card" style="margin-bottom: var(--nx-spacing-6); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-6); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); background: var(--nx-bg-surface); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; gap: var(--nx-spacing-6); flex-wrap: wrap;">
            <!-- Avatar with Upload Overlay -->
            <div style="position: relative; width: 88px; height: 88px; border-radius: var(--nx-radius-2xl); overflow: hidden; box-shadow: var(--nx-shadow-card); border: 2px solid var(--nx-primary-500); background: var(--nx-accent-gradient); flex-shrink: 0;">
                <img id="user-profile-preview-avatar" src="{{ $user->avatar_url ? $user->avatar_url : '' }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; {{ $user->avatar_url ? '' : 'display: none;' }}">
                <div id="user-profile-avatar-fallback" style="width: 100%; height: 100%; display: {{ $user->avatar_url ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-size: 30px; font-weight: var(--nx-font-weight-black); color: white; font-family: var(--nx-font-mono);">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            </div>

            <!-- User Identity Details -->
            <div style="flex: 1; min-width: 220px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h2 style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin: 0;">{{ $user->name }}</h2>
                    @if($user->nickname)
                        <span class="nav-badge-pill" style="font-size: 12px; font-family: var(--nx-font-mono);">{{ '@' . $user->nickname }}</span>
                    @endif
                    <span class="nav-badge-pill" style="background: var(--nx-primary-surface); color: var(--nx-primary-500); font-size: 11px;">{{ $membership->role->name ?? __('Member') }}</span>
                </div>
                <div style="font-size: var(--nx-font-size-xs); color: var(--nx-text-secondary); margin-top: 6px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span style="display: flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">work</span>
                        <span>{{ $myProfile->job_title ?? __('Workspace Member') }}</span>
                    </span>
                    <span>•</span>
                    <span style="display: flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">apartment</span>
                        <span>{{ $organization->name }}</span>
                    </span>
                    <span>•</span>
                    <span style="display: flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">mail</span>
                        <span>{{ $user->email }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Form (Personal, Professional, Hobbies, Skills, Social Links, Notes) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: var(--nx-spacing-6);">

        <!-- Left Column: Personal, Contact & Work Details -->
        <div class="card" style="margin-bottom: 0; border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-6); background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card);">
            <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-5); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--nx-primary-500);">badge</span>
                <span>{{ __('Personal & Professional Info') }}</span>
            </h3>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: var(--nx-spacing-4);">
                @csrf

                <!-- Profile Photo Upload -->
                <div>
                    <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                        <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">photo_camera</span>
                        <span>{{ __('Change Profile Photo') }}</span>
                    </label>
                    <input type="file" name="avatar" accept="image/*" onchange="previewUserAvatar(this)" style="font-size: var(--nx-font-size-xs); color: var(--nx-text-secondary); width: 100%; background: var(--nx-bg-surface-subtle); padding: 10px; border-radius: var(--nx-radius-md); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-inset-3d);">
                    <span style="font-size: 10px; color: var(--nx-text-muted); display: block; margin-top: 4px;">{{ __('JPEG, PNG, WebP up to 4MB.') }}</span>
                </div>

                <!-- Full Name -->
                <div>
                    <label style="display: block; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                        {{ __('Full Name') }} <span style="color: #D96B5F;">*</span>
                    </label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-sm); font-weight: 600; box-shadow: var(--nx-shadow-inset-3d);">
                </div>

                <!-- Nickname -->
                <div>
                    <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                        <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">alternate_email</span>
                        <span>{{ __('Nickname / Display Handle') }}</span>
                    </label>
                    <input type="text" name="nickname" value="{{ old('nickname', $user->nickname) }}" placeholder="e.g. sparky, alex_dev" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-primary-500); font-size: var(--nx-font-size-sm); font-weight: 700; font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-inset-3d);">
                </div>

                <!-- Email -->
                <div>
                    <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                        <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">mail</span>
                        <span>{{ __('Email Address') }}</span> <span style="color: #D96B5F;">*</span>
                    </label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-sm); font-weight: 600; box-shadow: var(--nx-shadow-inset-3d);">
                </div>

                <!-- Date of Birth & Phone in 2 Columns -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--nx-spacing-3);">
                    <div>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">cake</span>
                            <span>{{ __('Date of Birth') }}</span>
                        </label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $myProfile->date_of_birth ? $myProfile->date_of_birth->format('Y-m-d') : '') }}" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-weight: 600; font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">phone</span>
                            <span>{{ __('Phone Number') }}</span>
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $myProfile->phone) }}" placeholder="+966 50 123 4567" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-weight: 600; font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-inset-3d);">
                    </div>
                </div>

                <!-- Job Title & Work Mode -->
                <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: var(--nx-spacing-3);">
                    <div>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">work</span>
                            <span>{{ __('Job Title') }}</span>
                        </label>
                        <input type="text" name="job_title" value="{{ old('job_title', $myProfile->job_title) }}" placeholder="e.g. Senior Fullstack Engineer" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-sm); font-weight: 600; box-shadow: var(--nx-shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">apartment</span>
                            <span>{{ __('Work Mode') }}</span>
                        </label>
                        <select name="work_mode" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-weight: 600; box-shadow: var(--nx-shadow-inset-3d);">
                            <option value="remote" {{ ($myProfile->work_mode ?? 'remote') === 'remote' ? 'selected' : '' }}>Remote</option>
                            <option value="hybrid" {{ ($myProfile->work_mode ?? '') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                            <option value="onsite" {{ ($myProfile->work_mode ?? '') === 'onsite' ? 'selected' : '' }}>On-site</option>
                        </select>
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                        <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">description</span>
                        <span>{{ __('Bio / About Me') }}</span>
                    </label>
                    <textarea name="bio" rows="3" placeholder="{{ __('Tell the team about yourself, your background, and what you love working on...') }}" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-weight: 500; resize: vertical; box-shadow: var(--nx-shadow-inset-3d); font-family: inherit;">{{ old('bio', $myProfile->bio) }}</textarea>
                </div>

                <div style="padding-top: 10px; border-top: 1px solid var(--nx-border-subtle);">
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: var(--nx-font-size-sm); cursor: pointer; display: inline-flex; align-items: center; gap: var(--nx-spacing-2);">
                        <span class="material-symbols-rounded" style="font-size: 18px;">save</span>
                        <span>{{ __('Save Profile Details') }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Hobbies, Skills, Social Links & Security -->
        <div style="display: flex; flex-direction: column; gap: var(--nx-spacing-6);">

            <!-- Hobbies, Skills & Social Links Card -->
            <div class="card" style="margin-bottom: 0; border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-6); background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card);">
                <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-5); display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="font-size: 20px; color: var(--nx-primary-500);">interests</span>
                    <span>{{ __('Hobbies, Skills & Social Links') }}</span>
                </h3>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: var(--nx-spacing-4);">
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
                        <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">sports_esports</span>
                            <span>{{ __('Hobbies & Interests') }}</span>
                        </label>
                        <input type="text" name="hobbies" value="{{ old('hobbies', $myProfile->hobbies) }}" placeholder="e.g. Chess, Reading, Video Games, Football, Travel" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-weight: 600; box-shadow: var(--nx-shadow-inset-3d);">
                    </div>

                    <!-- Skills -->
                    <div>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">psychology</span>
                            <span>{{ __('Skills & Expertise') }}</span>
                        </label>
                        <input type="text" name="skills" value="{{ old('skills', $myProfile->skills) }}" placeholder="e.g. Laravel, PHP, Vue.js, Architecture, UI/UX, Docker" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-weight: 600; box-shadow: var(--nx-shadow-inset-3d);">
                    </div>

                    <!-- Social Media Links -->
                    @php
                        $social = (array)($myProfile->social_links ?? []);
                    @endphp
                    <div>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">language</span>
                            <span>{{ __('Social Media & Portfolio Links') }}</span>
                        </label>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="width: 80px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">LinkedIn</span>
                                <input type="url" name="linkedin" value="{{ old('linkedin', $social['linkedin'] ?? '') }}" placeholder="https://linkedin.com/in/username" style="flex: 1; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 8px 12px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-inset-3d);">
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="width: 80px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">GitHub</span>
                                <input type="url" name="github" value="{{ old('github', $social['github'] ?? '') }}" placeholder="https://github.com/username" style="flex: 1; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 8px 12px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-inset-3d);">
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="width: 80px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">X (Twitter)</span>
                                <input type="url" name="twitter" value="{{ old('twitter', $social['twitter'] ?? '') }}" placeholder="https://x.com/username" style="flex: 1; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 8px 12px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-inset-3d);">
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="width: 80px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">Website</span>
                                <input type="url" name="website" value="{{ old('website', $social['website'] ?? '') }}" placeholder="https://mywebsite.com" style="flex: 1; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 8px 12px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-inset-3d);">
                            </div>
                        </div>
                    </div>

                    <!-- Personal Work Notes -->
                    <div>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">sticky_note_2</span>
                            <span>{{ __('Work Notes & Preferences') }}</span>
                        </label>
                        <textarea name="notes" rows="3" placeholder="{{ __('Any personal work notes, focus time rules, or reminders...') }}" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-weight: 500; resize: vertical; box-shadow: var(--nx-shadow-inset-3d); font-family: inherit;">{{ old('notes', $myProfile->notes) }}</textarea>
                    </div>

                    <div style="padding-top: 10px; border-top: 1px solid var(--nx-border-subtle);">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: var(--nx-font-size-sm); cursor: pointer; display: inline-flex; align-items: center; gap: var(--nx-spacing-2);">
                            <span class="material-symbols-rounded" style="font-size: 18px;">save</span>
                            <span>{{ __('Save Hobbies, Skills & Social') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security & Password Change Card -->
            <div class="card" style="margin-bottom: 0; border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-6); background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card);">
                <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-5); display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="font-size: 20px; color: var(--nx-primary-500);">lock</span>
                    <span>{{ __('Account Security & Password') }}</span>
                </h3>

                <form method="POST" action="{{ route('profile.password.update') }}" style="display: flex; flex-direction: column; gap: var(--nx-spacing-4);">
                    @csrf

                    <div>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">key</span>
                            <span>{{ __('Current Password') }}</span>
                        </label>
                        <input type="password" name="current_password" required placeholder="••••••••" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-sm); box-shadow: var(--nx-shadow-inset-3d);">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--nx-spacing-3);">
                        <div>
                            <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                                <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">password</span>
                                <span>{{ __('New Password') }}</span>
                            </label>
                            <input type="password" name="password" required placeholder="Min 8 chars" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-sm); box-shadow: var(--nx-shadow-inset-3d);">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                                <span class="material-symbols-rounded" style="font-size: 15px; color: var(--nx-text-muted);">sync_lock</span>
                                <span>{{ __('Confirm Password') }}</span>
                            </label>
                            <input type="password" name="password_confirmation" required placeholder="Repeat new password" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 10px 14px; color: var(--nx-text-primary); font-size: var(--nx-font-size-sm); box-shadow: var(--nx-shadow-inset-3d);">
                        </div>
                    </div>

                    <div style="padding-top: 10px; border-top: 1px solid var(--nx-border-subtle);">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: var(--nx-font-size-sm); cursor: pointer; display: inline-flex; align-items: center; gap: var(--nx-spacing-2);">
                            <span class="material-symbols-rounded" style="font-size: 18px;">lock_reset</span>
                            <span>{{ __('Update Password') }}</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
