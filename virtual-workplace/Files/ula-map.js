// UlaSpace token migration map: retired name -> Figma-true token (--ula-*)
// Values chosen by matching the retired token's literal value to the Figma ramp.
window.ULA_MAP = {
  // fonts
  '--nx-font-ar':'--ula-font-ar','--nx-font-en':'--ula-font-en','--nx-font-mono':'--ula-font-mono',
  '--nx-font-family':'--ula-font-family','--nx-font-arabic':'--ula-font-ar','--nx-font-sans':'--ula-font-en',
  '--font-sans':'--ula-font-en','--font-arabic':'--ula-font-ar','--font-mono':'--ula-font-mono',
  '--font-en':'--ula-font-en','--font-ar':'--ula-font-ar','--font-family':'--ula-font-family',
  // palm ramp (old ramp was mislabelled: old 500 = real palm-700, old 300 = real palm-500)
  '--nx-palm-950':'--ula-palm-950','--nx-palm-900':'--ula-palm-900','--nx-palm-800':'--ula-palm-chrome',
  '--nx-palm-700':'--ula-palm-800','--nx-palm-500':'--ula-palm-700','--nx-palm-300':'--ula-palm-500',
  '--nx-palm-100':'--ula-palm-100','--nx-palm-hero':'--ula-surface-dark','--nx-palm-hover':'--ula-accent-hover',
  // sand / stone
  '--nx-sand-50':'--ula-white','--nx-sand-100':'--ula-sand-100','--nx-sand-200':'--ula-sand-200',
  '--nx-sand-300':'--ula-sand-300','--nx-sand-400':'--ula-sand-400','--nx-sand-500':'--ula-stone-300',
  '--nx-stone-track':'--ula-stone-100','--nx-stone-text':'--ula-stone-500',
  // gold / terracotta
  '--nx-gold-200':'--ula-gold-200','--nx-gold-400':'--ula-gold-400','--nx-gold-500':'--ula-gold-500','--nx-gold-600':'--ula-gold-600',
  '--nx-terracotta-100':'--ula-terracotta-200','--nx-terracotta-400':'--ula-terracotta-400','--nx-terracotta-500':'--ula-terracotta-500',
  // spacing
  '--nx-spacing-1':'--ula-space-2','--nx-spacing-2':'--ula-space-3','--nx-spacing-3':'--ula-space-4',
  '--nx-spacing-4':'--ula-space-5','--nx-spacing-5':'--ula-space-6','--nx-spacing-6':'--ula-space-7',
  '--nx-spacing-8':'--ula-space-8','--nx-spacing-10':'--ula-space-9','--nx-spacing-12':'--ula-space-10',
  '--nx-space-1':'--ula-space-2','--nx-space-2':'--ula-space-3','--nx-space-3':'--ula-space-4','--nx-space-3-5':'--ula-space-4',
  '--nx-space-4':'--ula-space-5','--nx-space-5':'--ula-space-6','--nx-space-6':'--ula-space-7','--nx-space-8':'--ula-space-8',
  '--nx-space-10':'--ula-space-9','--nx-space-12':'--ula-space-10','--nx-space-16':'--ula-space-11',
  '--nx-space-20':'--ula-space-12','--nx-space-24':'--ula-space-13','--nx-space-30':'--ula-space-14',
  '--space-1':'--ula-space-2','--space-2':'--ula-space-3','--space-3':'--ula-space-4','--space-4':'--ula-space-5',
  '--space-5':'--ula-space-6','--space-6':'--ula-space-7','--space-8':'--ula-space-8','--space-10':'--ula-space-9','--space-12':'--ula-space-10',
  // layout
  '--nx-container-max':'--ula-layout-container-max','--nx-container-narrow':'--ula-layout-container-narrow',
  '--nx-card-pad':'--ula-layout-card-pad','--nx-panel-pad':'--ula-layout-panel-pad','--nx-gutter':'--ula-layout-gutter',
  // radii
  '--nx-radius-xs':'--ula-radius-xs','--nx-radius-sm':'--ula-radius-sm','--nx-radius-md':'--ula-radius-md',
  '--nx-radius-lg':'--ula-radius-lg','--nx-radius-xl':'--ula-radius-xl','--nx-radius-2xl':'--ula-radius-xl',
  '--nx-radius-arch':'--ula-radius-xl','--nx-radius-full':'--ula-radius-pill','--nx-radius-pill':'--ula-radius-pill',
  '--radius-sm':'--ula-radius-xs','--radius-md':'--ula-radius-sm','--radius-lg':'--ula-radius-lg',
  '--radius-xl':'--ula-radius-xl','--radius-2xl':'--ula-radius-xl','--radius-full':'--ula-radius-pill',
  // elevation
  '--nx-shadow-xs':'--ula-shadow-xs','--nx-shadow-sm':'--ula-shadow-sm','--nx-shadow-md':'--ula-shadow-md',
  '--nx-shadow-lg':'--ula-shadow-lg','--nx-shadow-xl':'--ula-shadow-xl','--nx-shadow-2xl':'--ula-shadow-xl',
  '--nx-shadow-on-map':'--ula-shadow-on-map','--nx-shadow-card':'--ula-shadow-xs',
  '--nx-shadow-soft-3d':'--ula-shadow-xs','--nx-shadow-inset-3d':'--ula-shadow-xs',
  '--shadow-sm':'--ula-shadow-sm','--shadow-md':'--ula-shadow-md','--shadow-lg':'--ula-shadow-lg','--shadow-xl':'--ula-shadow-xl',
  '--shadow-card':'--ula-shadow-xs','--shadow-soft-3d':'--ula-shadow-xs','--shadow-inset-3d':'--ula-shadow-xs',
  '--shadow-hover':'--ula-shadow-md','--shadow-elevated':'--ula-shadow-lg','--shadow-panel':'--ula-shadow-md',
  '--shadow-modal':'--ula-shadow-xl','--shadow-modal-3d':'--ula-shadow-xl','--shadow-dock':'--ula-shadow-lg',
  '--shadow-input':'--ula-shadow-xs','--shadow-tactile-btn':'--ula-shadow-sm','--shadow-tactile-secondary':'--ula-shadow-xs',
  '--shadow-brand':'--ula-shadow-md',
  '--nx-focus-ring':'--ula-focus-ring','--nx-focus-ring-on-dark':'--ula-focus-ring-on-dark','--nx-backdrop-blur':'--ula-backdrop-blur',
  // type scale
  '--nx-font-size-xs':'--ula-size-xs','--nx-font-size-sm':'--ula-size-sm','--nx-font-size-md':'--ula-size-body',
  '--nx-font-size-lg':'--ula-size-body-lg','--nx-font-size-xl':'--ula-size-h4','--nx-font-size-2xl':'--ula-size-h3',
  '--nx-font-size-3xl':'--ula-size-h2','--nx-font-size-4xl':'--ula-size-h1',
  '--nx-font-weight-light':'--ula-weight-light','--nx-font-weight-regular':'--ula-weight-regular',
  '--nx-font-weight-medium':'--ula-weight-medium','--nx-font-weight-semibold':'--ula-weight-semibold',
  '--nx-font-weight-bold':'--ula-weight-bold','--nx-font-weight-black':'--ula-weight-bold',
  // surfaces
  '--nx-bg-page':'--ula-surface-page','--nx-bg-surface':'--ula-surface-card','--nx-bg-surface-subtle':'--ula-surface-page-alt',
  '--nx-bg-surface-elevated':'--ula-surface-raised','--nx-bg-surface-hover':'--ula-surface-hover','--nx-bg-card':'--ula-surface-card',
  '--nx-bg-capsule':'--ula-surface-capsule','--nx-surface-card':'--ula-surface-card','--nx-surface-subtle':'--ula-surface-page-alt',
  '--nx-surface-sand':'--ula-surface-warm','--nx-canvas-bg':'--ula-surface-page',
  '--bg-base':'--ula-surface-page','--bg-page':'--ula-surface-page','--bg-body':'--ula-surface-page',
  '--bg-surface':'--ula-surface-card','--bg-card':'--ula-surface-card','--bg-surface-subtle':'--ula-surface-page-alt',
  '--bg-elevated':'--ula-surface-raised','--bg-surface-elevated':'--ula-surface-raised','--bg-overlay':'--ula-surface-overlay',
  '--bg-glass':'--ula-surface-capsule','--bg-glass-hover':'--ula-surface-capsule-strong','--bg-dock':'--ula-surface-capsule-strong',
  '--bg-input':'--ula-surface-page','--bg-primary':'--ula-accent-default',
  // text
  '--nx-text-primary':'--ula-text-primary','--nx-text-secondary':'--ula-text-secondary','--nx-text-muted':'--ula-text-muted',
  '--nx-text-mute':'--ula-text-muted','--nx-text-main':'--ula-text-primary','--nx-text-sub':'--ula-text-secondary',
  '--nx-text-placeholder':'--ula-text-muted','--nx-text-on-dark':'--ula-text-on-dark','--nx-text-on-dark-muted':'--ula-text-on-dark-muted',
  '--text-primary':'--ula-text-primary','--text-secondary':'--ula-text-secondary','--text-muted':'--ula-text-muted',
  '--text-disabled':'--ula-text-disabled','--text-inverse':'--ula-text-on-dark','--text-main':'--ula-text-primary','--text-dim':'--ula-text-muted',
  // borders
  '--nx-border-subtle':'--ula-border-subtle','--nx-border-default':'--ula-border-default','--nx-border-strong':'--ula-border-strong',
  '--nx-border-hover':'--ula-border-hover','--nx-border-on-dark':'--ula-border-on-dark','--nx-border-line':'--ula-border-subtle',
  '--border-subtle':'--ula-border-subtle','--border-default':'--ula-border-default','--border-strong':'--ula-border-strong',
  '--border-color':'--ula-border-subtle','--border-card':'--ula-border-subtle','--border-panel':'--ula-border-default',
  '--border-brand':'--ula-border-focus',
  // accent / brand
  '--nx-accent':'--ula-highlight-default','--nx-accent-hover':'--ula-highlight-ink','--nx-accent-fg':'--ula-text-on-gold',
  '--nx-primary-500':'--ula-accent-default','--nx-primary-600':'--ula-accent-press','--nx-primary-surface':'--ula-surface-accent-soft',
  '--brand-500':'--ula-accent-default','--brand-600':'--ula-accent-press','--brand-400':'--ula-accent-hover','--brand-light':'--ula-surface-accent-soft',
  '--brand-forest':'--ula-palm-900','--brand-primary':'--ula-palm-900','--brand-navy':'--ula-palm-900','--brand-sage':'--ula-palm-500',
  '--brand-emerald':'--ula-status-success','--brand-gold':'--ula-highlight-default','--brand-crimson':'--ula-status-danger',
  '--brand-teal':'--ula-status-info','--brand-accent':'--ula-highlight-default','--accent-primary':'--ula-accent-default',
  '--violet-500':'--ula-highlight-default','--purple-500':'--ula-highlight-default','--cyan-500':'--ula-status-info',
  '--emerald-500':'--ula-status-success','--emerald-400':'--ula-status-success','--rose-500':'--ula-status-danger',
  '--rose-400':'--ula-status-danger','--amber-500':'--ula-status-warning','--amber-400':'--ula-status-warning',
  // status
  '--nx-status-live':'--ula-status-success','--nx-status-live-bg':'--ula-tone-palm-bg',
  '--nx-status-scheduled':'--ula-status-warning','--nx-status-scheduled-bg':'--ula-tone-gold-bg',
  '--nx-status-attention':'--ula-status-danger','--nx-status-attention-bg':'--ula-tone-terracotta-bg',
  '--nx-status-cancelled':'--ula-text-muted','--nx-status-cancelled-bg':'--ula-tone-stone-bg',
  '--status-available':'--ula-status-success','--status-busy':'--ula-status-danger','--status-away':'--ula-status-warning',
  '--status-focus':'--ula-highlight-default','--status-offline':'--ula-text-muted','--status-success':'--ula-status-success',
  '--status-warning':'--ula-status-warning','--status-danger':'--ula-status-danger','--status-error':'--ula-status-danger',
  // motion
  '--nx-duration-instant':'--ula-duration-instant','--nx-duration-fast':'--ula-duration-fast','--nx-duration-base':'--ula-duration-base',
  '--nx-duration-slow':'--ula-duration-slow','--nx-ease-standard':'--ula-ease-in-out','--nx-ease-out':'--ula-ease-out',
  '--ease-spring':'--ula-ease-entrance','--ease-smooth':'--ula-ease-in-out',
  // scrims
  '--nx-scrim-hero':'--ula-scrim-hero','--nx-scrim-caption':'--ula-scrim-caption','--nx-scrim-welcome':'--ula-scrim-welcome',
  '--nx-scrim-welcome-rtl':'--ula-scrim-welcome-rtl','--nx-scrim-welcome-ltr':'--ula-scrim-welcome-ltr',
  // map chrome
  '--nx-map-dark-bg':'--ula-surface-map-canvas','--nx-map-dock-bg':'--ula-surface-capsule-strong',
  '--nx-map-gold':'--ula-highlight-default','--nx-map-border':'--ula-border-on-dark','--nx-map-border-strong':'--ula-alpha-ivory-30',
  '--nx-map-text':'--ula-text-on-dark','--nx-map-muted':'--ula-text-on-dark-subtle','--nx-map-subtext':'--ula-text-on-dark-subtle',
  '--nx-map-green':'--ula-status-success',
  // sidebar chrome
  '--sidebar-bg':'--ula-surface-dark','--sidebar-text':'--ula-text-on-dark','--sidebar-text-muted':'--ula-text-on-dark-muted',
  '--sidebar-border':'--ula-border-on-dark','--sidebar-hover':'--ula-control-dark-fill-hover','--sidebar-active':'--ula-control-dark-fill-strong'
};

// Literal hex values from the retired palette -> the real Figma hex
window.ULA_HEX = {
  '#0b1410':'#0e1c17',   // old palm-950 -> Figma palm/950
  '#f9f4ee':'#f9f6ef',   // old page bg -> Figma sand/100
  '#eae3d4':'#ede6d9',   // old map light bg -> Figma sand/300
  '#f6ebda':'#f0dfbc',   // old gold-200 -> Figma gold/200
  '#faeae6':'#ecc9ae',   // old terracotta-100 -> Figma terracotta/200
  '#4ea66f':'#8baa94',   // old dark status live -> Figma palm/300
  '#e5b765':'#e6c88b',   // old dark status scheduled -> Figma gold/300
  '#c9743a':'#d99a6c',   // old dark status attention -> Figma terracotta/300
  '#8e9d95':'#857a6c',   // old text muted -> Figma stone/500
  '#5a6b63':'#665d52',   // old text secondary -> Figma stone/600
  '#8e877c':'#857a6c',   // old stone text -> Figma stone/500
  '#63756d':'#857a6c',   // old dark placeholder -> Figma stone/500
  '#a4b5ad':'#c1b6a6',   // old dark text muted -> Figma stone/300
  '#1e382f':'#1b3223'    // old dark hover -> Figma palm/800
};

// Local composite variables that keep their name (their values get rewritten by the ref pass)
window.ULA_KEEP = ['--accent-gradient','--nx-accent-gradient','--transition-smooth'];
