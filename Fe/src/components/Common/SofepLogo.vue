<template>
  <!-- 1. Circular Medallion / Avatar Mode (100% nằm gọn hoàn hảo bên trong hình tròn) -->
  <div v-if="circle" 
       class="sofep-circle-logo-badge rounded-circle d-inline-flex flex-column align-items-center justify-content-center user-select-none position-relative overflow-hidden" 
       :class="[variantClass, circleSizeClass]">
    <div class="emblem-inner-glow position-absolute top-0 start-0 w-100 h-100 rounded-circle"></div>
    
    <!-- Precision Luxury Geometric 'S' Monogram SVG -->
    <svg class="sofep-emblem-svg flex-shrink-0" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient :id="'sofepGold_' + uid" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#fde68a" />
          <stop offset="35%" stop-color="#f59e0b" />
          <stop offset="70%" stop-color="#d97706" />
          <stop offset="100%" stop-color="#92400e" />
        </linearGradient>
        <linearGradient :id="'sofepShine_' + uid" x1="0%" y1="100%" x2="100%" y2="0%">
          <stop offset="0%" stop-color="#ffffff" stop-opacity="0.8" />
          <stop offset="50%" stop-color="#fef3c7" stop-opacity="0.9" />
          <stop offset="100%" stop-color="#fbbf24" stop-opacity="0.5" />
        </linearGradient>
        <filter :id="'softGlow_' + uid" x="-20%" y="-20%" width="140%" height="140%">
          <feDropShadow dx="0" dy="1" stdDeviation="1" flood-color="#b45309" flood-opacity="0.25" />
        </filter>
      </defs>

      <!-- Outer Geometric Diamond Hexagon Frame -->
      <path d="M20 3.5L34.5 11.8V28.2L20 36.5L5.5 28.2V11.8L20 3.5Z" 
            :stroke="`url(#sofepGold_${uid})`" 
            stroke-width="1.2" 
            stroke-opacity="0.5" 
            stroke-dasharray="2 2" />

      <!-- Luxury Interlocking Architectural 'S' Monogram -->
      <path d="M27.5 13.5C26 10 22.8 8.5 19 8.5C13.5 8.5 10 11.8 10 16C10 22 28 19 28 25C28 29.5 24 32.5 18.5 32.5C13.8 32.5 10.8 30.2 9.5 27.2" 
            :stroke="`url(#sofepGold_${uid})`" 
            stroke-width="3.2" 
            stroke-linecap="round" 
            stroke-linejoin="round"
            :filter="`url(#softGlow_${uid})`" />

      <!-- Inner Bevel Highlight for 3D Luxury Reflection -->
      <path d="M25 13C23.8 10.5 21.5 9.8 19 9.8C15 9.8 11.5 12.2 11.5 15.5C11.5 19.5 25.5 18 26.5 24C27.2 27.8 23.5 31 18.5 31" 
            :stroke="`url(#sofepShine_${uid})`" 
            stroke-width="1.2" 
            stroke-linecap="round" />

      <!-- Delicate Sparkle Diamond Stars -->
      <circle cx="28.5" cy="11.5" r="1.2" :fill="`url(#sofepGold_${uid})`" />
      <circle cx="8.5" cy="29" r="1.2" :fill="`url(#sofepGold_${uid})`" />
    </svg>

    <!-- Mini Brand Name SOFEP nằm gọn hoàn toàn bên trong vòng tròn -->
    <div v-if="!iconOnly && showText" class="sofep-circle-brand-name font-streetwear user-select-none">
      SOFEP
    </div>
  </div>

  <!-- 2. Standard Horizontal Logo (cho Header, Footer, Login, v.v.) -->
  <div v-else class="sofep-logo-container user-select-none" :class="[variantClass, sizeClass]">
    
    <!-- Luxury Emblem Icon Box (Bright Pearl White & Champagne Gold) -->
    <div class="sofep-emblem-box rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 position-relative">
      <!-- Glow & border effects -->
      <div class="emblem-inner-glow position-absolute top-0 start-0 w-100 h-100 rounded-3"></div>

      <!-- Precision Luxury Geometric 'S' Monogram SVG -->
      <svg class="sofep-emblem-svg" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient :id="'sofepGold_' + uid" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#fde68a" />
            <stop offset="35%" stop-color="#f59e0b" />
            <stop offset="70%" stop-color="#d97706" />
            <stop offset="100%" stop-color="#92400e" />
          </linearGradient>

          <linearGradient :id="'sofepShine_' + uid" x1="0%" y1="100%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.8" />
            <stop offset="50%" stop-color="#fef3c7" stop-opacity="0.9" />
            <stop offset="100%" stop-color="#fbbf24" stop-opacity="0.5" />
          </linearGradient>

          <filter :id="'softGlow_' + uid" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="1" stdDeviation="1" flood-color="#b45309" flood-opacity="0.25" />
          </filter>
        </defs>

        <!-- Outer Geometric Diamond Hexagon Frame -->
        <path d="M20 3.5L34.5 11.8V28.2L20 36.5L5.5 28.2V11.8L20 3.5Z" 
              :stroke="`url(#sofepGold_${uid})`" 
              stroke-width="1.2" 
              stroke-opacity="0.5" 
              stroke-dasharray="2 2" />

        <!-- Luxury Interlocking Architectural 'S' Monogram -->
        <path d="M27.5 13.5C26 10 22.8 8.5 19 8.5C13.5 8.5 10 11.8 10 16C10 22 28 19 28 25C28 29.5 24 32.5 18.5 32.5C13.8 32.5 10.8 30.2 9.5 27.2" 
              :stroke="`url(#sofepGold_${uid})`" 
              stroke-width="3.2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
              :filter="`url(#softGlow_${uid})`" />

        <!-- Inner Bevel Highlight for 3D Luxury Reflection -->
        <path d="M25 13C23.8 10.5 21.5 9.8 19 9.8C15 9.8 11.5 12.2 11.5 15.5C11.5 19.5 25.5 18 26.5 24C27.2 27.8 23.5 31 18.5 31" 
              :stroke="`url(#sofepShine_${uid})`" 
              stroke-width="1.2" 
              stroke-linecap="round" />

        <!-- Delicate Sparkle Diamond Stars -->
        <circle cx="28.5" cy="11.5" r="1.2" :fill="`url(#sofepGold_${uid})`" />
        <circle cx="8.5" cy="29" r="1.2" :fill="`url(#sofepGold_${uid})`" />
      </svg>
    </div>

    <!-- Luxury Typography Column -->
    <div v-if="!iconOnly && showText" class="d-flex flex-column justify-content-center">
      <!-- Main Brand Name 'SOFEP' -->
      <div class="sofep-brand-title d-flex align-items-center">
        <span class="sofep-name-text">SOFEP</span>
        <span class="sofep-diamond-star ms-1">✦</span>
      </div>

      <!-- Luxury Subtitle -->
      <div v-if="showSubtitle" class="sofep-brand-subtitle">
        {{ subtitle || 'STUDIO 2026' }}
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'SofepLogo',
  props: {
    variant: {
      type: String,
      default: 'light', // 'light' (on white background) or 'dark' (on obsidian/dark background)
      validator: v => ['light', 'dark', 'gold'].includes(v)
    },
    size: {
      type: String,
      default: 'md', // 'xs', 'sm', 'md', 'lg', 'avatar'
      validator: s => ['xs', 'sm', 'md', 'lg', 'avatar'].includes(s)
    },
    subtitle: {
      type: String,
      default: 'STUDIO 2026'
    },
    circle: {
      type: Boolean,
      default: false
    },
    iconOnly: {
      type: Boolean,
      default: false
    },
    showText: {
      type: Boolean,
      default: true
    },
    showSubtitle: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      uid: Math.random().toString(36).substring(2, 8)
    };
  },
  computed: {
    variantClass() {
      return `sofep-variant-${this.variant}`;
    },
    sizeClass() {
      return `sofep-size-${this.size}`;
    },
    circleSizeClass() {
      return `sofep-size-${this.size}`;
    }
  }
};
</script>

<style scoped>
/* ==========================================================================
   SOFEP Brand Identity - Modern Luxury Bright Styling
   ========================================================================== */

.sofep-logo-container {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  text-decoration: none;
}

.sofep-size-xs.sofep-logo-container { gap: 8px; }
.sofep-size-sm.sofep-logo-container { gap: 10px; }
.sofep-size-md.sofep-logo-container { gap: 13px; }
.sofep-size-lg.sofep-logo-container { gap: 16px; }

.sofep-logo-container:hover {
  transform: translateY(-1px) scale(1.02);
}

.sofep-logo-container:hover .sofep-emblem-box {
  box-shadow: 0 6px 20px rgba(217, 119, 6, 0.25), 0 0 15px rgba(253, 230, 138, 0.4);
  border-color: rgba(217, 119, 6, 0.5);
}

/* Emblem Box */
.sofep-emblem-box {
  background: linear-gradient(135deg, #ffffff 0%, #fffdf7 60%, #fef3c7 100%);
  border: 1.5px solid rgba(217, 119, 6, 0.3);
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.12), inset 0 1px 2px #ffffff;
  transition: all 0.3s ease;
  overflow: hidden;
}

.emblem-inner-glow {
  background: radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.9) 0%, transparent 60%);
  pointer-events: none;
}

/* Brand Name Typography */
.sofep-name-text {
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  font-weight: 900;
  letter-spacing: 2.2px;
  line-height: 1;
  text-transform: uppercase;
}

/* Subtitle Typography */
.sofep-brand-subtitle {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-weight: 700;
  letter-spacing: 3.2px;
  line-height: 1.1;
  text-transform: uppercase;
  margin-top: 3px;
}

/* Diamond Star Sparkle */
.sofep-diamond-star {
  color: #d97706;
  font-size: 11px;
  animation: sofepTwinkle 3s ease-in-out infinite;
}

@keyframes sofepTwinkle {
  0%, 100% { opacity: 0.8; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.2); }
}

/* --- CIRCULAR MEDALLION / AVATAR MODE --- */
.sofep-circle-logo-badge {
  background: linear-gradient(135deg, #ffffff 0%, #fffdfa 60%, #fef3c7 100%);
  border: 1.5px solid rgba(180, 131, 37, 0.35);
  box-shadow: 0 3px 10px rgba(180, 131, 37, 0.12), inset 0 1px 2px #ffffff;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  text-decoration: none;
  overflow: hidden;
}

.sofep-circle-logo-badge:hover {
  transform: scale(1.05);
  box-shadow: 0 5px 16px rgba(180, 131, 37, 0.22), 0 0 12px rgba(253, 230, 138, 0.4);
  border-color: rgba(180, 131, 37, 0.55);
}

.sofep-circle-logo-badge.sofep-size-xs {
  width: 36px;
  height: 36px;
}
.sofep-circle-logo-badge.sofep-size-xs .sofep-emblem-svg {
  width: 18px;
  height: 18px;
}
.sofep-circle-logo-badge.sofep-size-xs .sofep-circle-brand-name {
  font-size: 6px;
  letter-spacing: 1px;
}

.sofep-circle-logo-badge.sofep-size-sm {
  width: 48px;
  height: 48px;
}
.sofep-circle-logo-badge.sofep-size-sm .sofep-emblem-svg {
  width: 24px;
  height: 24px;
}
.sofep-circle-logo-badge.sofep-size-sm .sofep-circle-brand-name {
  font-size: 7.5px;
  letter-spacing: 1.4px;
  margin-top: 1px;
}

.sofep-circle-logo-badge.sofep-size-md {
  width: 62px;
  height: 62px;
}
.sofep-circle-logo-badge.sofep-size-md .sofep-emblem-svg {
  width: 32px;
  height: 32px;
}
.sofep-circle-logo-badge.sofep-size-md .sofep-circle-brand-name {
  font-size: 9.5px;
  letter-spacing: 2px;
  margin-top: 2px;
}

.sofep-circle-logo-badge.sofep-size-lg {
  width: 76px;
  height: 76px;
}
.sofep-circle-logo-badge.sofep-size-lg .sofep-emblem-svg {
  width: 38px;
  height: 38px;
}
.sofep-circle-logo-badge.sofep-size-lg .sofep-circle-brand-name {
  font-size: 11.5px;
  letter-spacing: 2.5px;
  margin-top: 2px;
}

.sofep-circle-logo-badge.sofep-size-avatar {
  width: 100%;
  height: 100%;
}
.sofep-circle-logo-badge.sofep-size-avatar .sofep-emblem-svg {
  width: 52%;
  height: 52%;
}
.sofep-circle-logo-badge.sofep-size-avatar .sofep-circle-brand-name {
  font-size: 14%;
  letter-spacing: 1.5px;
  margin-top: 2px;
}

.sofep-circle-brand-name {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-weight: 900;
  line-height: 1;
  text-transform: uppercase;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #78350f 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* --- VARIANT: LIGHT (Dành cho nền sáng / Navbar Trắng) --- */
.sofep-variant-light .sofep-name-text {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #78350f 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.sofep-variant-light .sofep-brand-subtitle {
  color: #b45309;
}

/* --- VARIANT: DARK (Dành cho Footer Đen / Dark Mode) --- */
.sofep-variant-dark .sofep-emblem-box,
.sofep-circle-logo-badge.sofep-variant-dark {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border-color: rgba(245, 158, 11, 0.4);
  box-shadow: 0 4px 14px rgba(245, 158, 11, 0.15);
}

.sofep-variant-dark .sofep-name-text,
.sofep-circle-logo-badge.sofep-variant-dark .sofep-circle-brand-name {
  background: linear-gradient(135deg, #ffffff 0%, #fef3c7 70%, #f59e0b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.sofep-variant-dark .sofep-brand-subtitle {
  color: #fbbf24;
  opacity: 0.9;
}

/* --- SIZES FOR HORIZONTAL LOGO --- */
.sofep-size-xs .sofep-emblem-box {
  width: 26px;
  height: 26px;
}
.sofep-size-xs .sofep-emblem-svg {
  width: 18px;
  height: 18px;
}
.sofep-size-xs .sofep-name-text {
  font-size: 0.85rem;
}
.sofep-size-xs .sofep-brand-subtitle {
  font-size: 6.5px;
  letter-spacing: 2px;
}

/* Size Small */
.sofep-size-sm .sofep-emblem-box {
  width: 32px;
  height: 32px;
}
.sofep-size-sm .sofep-emblem-svg {
  width: 22px;
  height: 22px;
}
.sofep-size-sm .sofep-name-text {
  font-size: 1rem;
}
.sofep-size-sm .sofep-brand-subtitle {
  font-size: 7.5px;
  letter-spacing: 2.5px;
}

/* Size Medium (Default Navbar) */
.sofep-size-md .sofep-emblem-box {
  width: 42px;
  height: 42px;
}
.sofep-size-md .sofep-emblem-svg {
  width: 30px;
  height: 30px;
}
.sofep-size-md .sofep-name-text {
  font-size: 1.35rem;
}
.sofep-size-md .sofep-brand-subtitle {
  font-size: 8.5px;
  letter-spacing: 3.2px;
}

/* Size Large (Hero / Banner / Auth) */
.sofep-size-lg .sofep-emblem-box {
  width: 56px;
  height: 56px;
}
.sofep-size-lg .sofep-emblem-svg {
  width: 40px;
  height: 40px;
}
.sofep-size-lg .sofep-name-text {
  font-size: 1.85rem;
  letter-spacing: 2.8px;
}
.sofep-size-lg .sofep-brand-subtitle {
  font-size: 10px;
  letter-spacing: 4.5px;
}
</style>
