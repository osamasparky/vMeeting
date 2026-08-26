@extends('superadmin.layout')

@section('title', __('3D & Media Asset Manager'))
@section('page_title', __('Website CMS — 3D & Media Assets (مدير ملفات الـ 3D والوسائط)'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">
                📁 {{ __('Nano Banana & 3D Media Asset Manager') }}
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                {{ __('Upload and manage GLB, GLTF, looping MP4/WebM videos, and architectural floorplans. Swap assets in sections instantly.') }}
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('superadmin.cms.pages') }}" class="tactile-btn btn-outline">
                <span>←</span> {{ __('Back to CMS Pages') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(79, 155, 95, 0.15); border: 1px solid rgba(79, 155, 95, 0.35); color: #4F9B5F; padding: 14px 18px; border-radius: var(--radius-md); font-size: 13px; font-weight: 800;">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Upload Asset Card -->
    <div class="panel-card" style="border-radius: var(--radius-xl); padding: 24px;">
        <div class="panel-header" style="margin-bottom: 20px;">
            <div class="panel-title">
                <span>📤</span>
                <span>{{ __('Upload New Media or 3D GLB/GLTF Model (رفع ملف أو نموذج جديد)') }}</span>
            </div>
            <p class="panel-subtitle">{{ __('Supported formats: GLB, GLTF, MP4, WebM, PNG, JPG, WebP, SVG, Lottie (Max 50MB)') }}</p>
        </div>

        <form method="POST" action="{{ route('superadmin.cms.assets.upload') }}" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                        🏷️ {{ __('Asset Name') }} *
                    </label>
                    <input type="text" name="name" required placeholder="e.g. Hero 3D Office Scene" class="form-input" style="width: 100%;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                        📦 {{ __('Asset Type') }} *
                    </label>
                    <select name="asset_type" required class="form-input" style="width: 100%;">
                        <option value="image">🖼️ Image (JPG, PNG, WebP, SVG)</option>
                        <option value="video">🎥 Video (MP4, WebM)</option>
                        <option value="3d_glb">🧊 3D Model (GLB)</option>
                        <option value="3d_gltf">📐 3D Scene (GLTF)</option>
                        <option value="lottie">✨ Lottie Animation</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                        🏷️ {{ __('Version Tag') }}
                    </label>
                    <input type="text" name="version_tag" placeholder="e.g. hero-office-v2" class="form-input" style="width: 100%;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                        🏷️ {{ __('Tags (Comma separated)') }}
                    </label>
                    <input type="text" name="tags" placeholder="hero, 3d, nano-banana" class="form-input" style="width: 100%;">
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                        📁 {{ __('Select File') }} *
                    </label>
                    <input type="file" name="file" required class="form-input" style="width: 100%;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="tactile-btn btn-primary" style="padding: 10px 28px; font-size: 13px;">
                    🚀 {{ __('Upload & Save Asset') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Asset Library Grid -->
    <div class="panel-card" style="border-radius: var(--radius-xl); padding: 24px;">
        <div class="panel-header" style="margin-bottom: 20px;">
            <div class="panel-title">
                <span>📚</span>
                <span>{{ __('Active Media & 3D Library (مكتبة الوسائط الحالية)') }}</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
            @forelse($assets as $ast)
                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="height: 150px; background: #071A16; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                        @if($ast->asset_type === 'image')
                            <img src="{{ $ast->url }}" alt="{{ $ast->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @elseif($ast->asset_type === 'video')
                            <video src="{{ $ast->url }}" autoplay loop muted style="width: 100%; height: 100%; object-fit: cover;"></video>
                        @elseif(str_starts_with($ast->asset_type, '3d'))
                            <div style="text-align: center;">
                                <span style="font-size: 44px; display: block;">🧊</span>
                                <span style="font-size: 11px; font-weight: 800; color: var(--brand-forest);">3D MODEL ({{ strtoupper($ast->asset_type) }})</span>
                            </div>
                        @else
                            <span style="font-size: 40px;">📁</span>
                        @endif

                        <span style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.7); color: white; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 6px;">
                            {{ strtoupper($ast->asset_type) }}
                        </span>
                    </div>

                    <div style="padding: 16px;">
                        <strong style="font-size: 13px; color: var(--text-primary); display: block; margin-bottom: 4px; word-break: break-word;">
                            {{ $ast->name }}
                        </strong>
                        <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px;">
                            Tag: <strong>{{ $ast->version_tag ?? 'v1' }}</strong> • {{ $ast->created_at ? $ast->created_at->format('Y-m-d') : '' }}
                        </div>

                        <div style="display: flex; gap: 8px; justify-content: space-between; align-items: center;">
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $ast->url }}'); alert('{{ __('URL copied to clipboard!') }}')" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px;">
                                📋 {{ __('Copy URL') }}
                            </button>

                            <form method="POST" action="{{ route('superadmin.cms.assets.delete', $ast) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this asset?') }}')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tactile-btn" style="padding: 4px 10px; font-size: 11px; background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);">
                                    🗑️ {{ __('Delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 48px; color: var(--text-muted);">
                    {{ __('No media assets uploaded yet.') }}
                </div>
            @endforelse
        </div>

        <div style="margin-top: 24px;">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection
