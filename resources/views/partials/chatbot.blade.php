{{-- Widget Asisten AI GoManisGo --}}
<div id="gm-chatbot-root">
  {{-- Floating Launcher Button (Bisa Buka & Menutup Chat) --}}
  <button id="gm-chat-toggle" class="gm-chat-launcher" type="button" aria-label="Buka Chat Asisten GoManisGo" title="Tanya Asisten GoManisGo">
    <div class="gm-chat-launcher-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ic-chat">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
      </svg>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ic-close" style="display:none">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </div>
    <span class="gm-chat-launcher-badge">AI</span>
  </button>

  {{-- Chat Window Modal / Popup --}}
  <div id="gm-chat-panel" class="gm-chat-window" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="gm-chat-title">
    {{-- Resize Handles (Top, Left, & Top-Left Corner) --}}
    <div class="gm-resize-handle gm-resize-t" title="Tarik untuk ubah tinggi"></div>
    <div class="gm-resize-handle gm-resize-l" title="Tarik untuk ubah lebar"></div>
    <div class="gm-resize-handle gm-resize-tl" title="Tarik untuk sesuaikan ukuran (klik ganda untuk reset)">
      <span class="gm-resize-grip"></span>
    </div>

    {{-- Header --}}
    <div class="gm-chat-header">
      <div class="gm-chat-header-left">
        <div class="gm-chat-avatar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
            <path d="M18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
          </svg>
        </div>
        <div class="gm-chat-header-info">
          <h4 id="gm-chat-title" class="gm-chat-title">Asisten GoManisGo</h4>
          <div class="gm-chat-status"><span class="gm-status-dot"></span> <span class="gm-chat-status-text">Online · Siap membantu</span></div>
        </div>
      </div>
      <div class="gm-chat-header-actions">
        {{-- Tombol Sesuaikan / Perbesar Ukuran --}}
        <button id="gm-chat-expand" type="button" class="gm-chat-header-btn" aria-label="Ubah Ukuran Chat" title="Perbesar / Perkecil Ukuran">
          <svg class="ic-expand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 3 21 3 21 9"></polyline>
            <polyline points="9 21 3 21 3 15"></polyline>
            <line x1="21" y1="3" x2="14" y2="10"></line>
            <line x1="3" y1="21" x2="10" y2="14"></line>
          </svg>
          <svg class="ic-compress" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
            <polyline points="4 14 10 14 10 20"></polyline>
            <polyline points="20 10 14 10 14 4"></polyline>
            <line x1="14" y1="10" x2="21" y2="3"></line>
            <line x1="3" y1="21" x2="10" y2="14"></line>
          </svg>
        </button>
        <button id="gm-chat-minimize" type="button" class="gm-chat-header-btn" aria-label="Minimize Chat" title="Minimize">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>
        <button id="gm-chat-close" type="button" class="gm-chat-header-btn" aria-label="Tutup Chat" title="Tutup">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
    </div>

    {{-- Messages Area --}}
    <div id="gm-chat-messages" class="gm-chat-body">
      {{-- Empty state: shown when no previous conversation --}}
      <div class="gm-chat-empty" id="gm-chat-empty">
        <div class="gm-chat-empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
          </svg>
        </div>
        <div class="gm-chat-empty-title">Halo, {{ auth()->user()->nama_user }}! 👋</div>
        <div class="gm-chat-empty-desc">Saya bisa membantu kamu mencari produk, cek harga, dan informasi pesanan.</div>
      </div>

      {{-- Welcome message --}}
      <div class="gm-msg gm-msg-bot">
        <div class="gm-bubble">
          Halo, <b>{{ auth()->user()->nama_user }}</b>! 👋<br>
          Saya asisten belanja GoManisGo. Mau cari kebutuhan sembako apa hari ini? (Beras, minyak, gula, telur, dll.)
        </div>
        <span class="gm-msg-time">{{ date('H:i') }}</span>
      </div>

      {{-- Quick chips --}}
      <div class="gm-quick-chips" id="gm-quick-chips">
        <button type="button" class="gm-chip" data-query="Cari produk beras yang tersedia">
          <svg viewBox="0 0 20 20" fill="currentColor" class="gm-chip-icon"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/></svg>
          <span>Cari produk</span>
        </button>
        <button type="button" class="gm-chip" data-query="Berapa harga beras, minyak goreng, dan gula pasir?">
          <svg viewBox="0 0 20 20" fill="currentColor" class="gm-chip-icon"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-14a1 1 0 10-2 0v2H7a1 1 0 100 2h1v1H7a1 1 0 100 2h1v2a1 1 0 102 0v-2h1a1 1 0 100-2h-1V9h1a1 1 0 100-2h-1V5z" clip-rule="evenodd"/></svg>
          <span>Cek harga</span>
        </button>
        <button type="button" class="gm-chip" data-query="Produk apa saja yang stoknya masih tersedia?">
          <svg viewBox="0 0 20 20" fill="currentColor" class="gm-chip-icon"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/><path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
          <span>Tanya stok</span>
        </button>
        <button type="button" class="gm-chip" data-query="Bagaimana cara memesan produk lewat chat ini?">
          <svg viewBox="0 0 20 20" fill="currentColor" class="gm-chip-icon"><path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"/></svg>
          <span>Bantuan pesanan</span>
        </button>
      </div>

      {{-- Typing indicator --}}
      <div id="gm-chat-typing" class="gm-msg gm-msg-bot" style="display:none;">
        <div class="gm-bubble gm-bubble-typing">
          <span class="gm-typing-label">Asisten GoManisGo</span>
          <div class="gm-typing-dots">
            <span class="dot"></span><span class="dot"></span><span class="dot"></span>
          </div>
        </div>
      </div>
    </div>

    {{-- Error Banner --}}
    <div id="gm-chat-error" class="gm-chat-error-bar" style="display:none;">
      <span>Gagal mengirim pesan. Silakan coba lagi.</span>
      <button type="button" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>

    {{-- Input Area --}}
    <form id="gm-chat-form" class="gm-chat-footer">
      <div class="gm-chat-input-wrap">
        <textarea id="gm-chat-input" class="gm-chat-textarea" placeholder="Tanya tentang produk atau pesanan..." rows="1" maxlength="500" required></textarea>
        <button type="submit" id="gm-chat-send" class="gm-chat-btn-send" aria-label="Kirim Pesan" title="Kirim Pesan">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
          </svg>
        </button>
      </div>
      <div class="gm-chat-footer-hint">Tekan Enter untuk kirim · Shift+Enter baris baru</div>
    </form>
  </div>
</div>

<style>
/* ==========================================================================
   GoManisGo Chatbot UI — Optimized & Streamlined CSS
   ========================================================================== */

#gm-chatbot-root {
  --gm-w: min(390px, calc(100vw - 32px));
  --gm-max-w: calc(100vw - 32px);
  --gm-h: min(560px, calc(100dvh - 110px));
  --gm-max-h: calc(100dvh - 100px);
  --gm-bottom: 20px;
  --gm-right: 20px;
  --gm-launcher: 54px;
  --gm-header: 56px;
  --gm-avatar: 34px;
  --gm-send: 40px;
  --gm-radius: 20px;

  position: fixed;
  bottom: var(--gm-bottom);
  right: var(--gm-right);
  z-index: 1050;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  pointer-events: none;
}

#gm-chatbot-root *,
#gm-chatbot-root *::before,
#gm-chatbot-root *::after {
  box-sizing: border-box;
}

/* Cursor states during resize */
.gm-resizing, .gm-resizing * { user-select: none !important; -webkit-user-select: none !important; }
.gm-resizing-tl, .gm-resizing-tl * { cursor: nwse-resize !important; }
.gm-resizing-t, .gm-resizing-t * { cursor: ns-resize !important; }
.gm-resizing-l, .gm-resizing-l * { cursor: ew-resize !important; }

/* Floating Launcher Button */
.gm-chat-launcher {
  pointer-events: auto;
  position: relative;
  width: var(--gm-launcher);
  height: var(--gm-launcher);
  border-radius: 50%;
  background: linear-gradient(135deg, #F45B0B 0%, #e04500 100%);
  color: #fff;
  border: 0;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(244, 91, 11, 0.35), 0 2px 6px rgba(0, 0, 0, 0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.25s cubic-bezier(.4,0,.2,1), box-shadow 0.25s cubic-bezier(.4,0,.2,1);
  padding: 0;
}
.gm-chat-launcher:hover {
  transform: scale(1.08) translateY(-2px);
  box-shadow: 0 8px 24px rgba(244, 91, 11, 0.45), 0 4px 10px rgba(0, 0, 0, 0.15);
}
.gm-chat-launcher:active { transform: scale(0.95); }
.gm-chat-launcher svg { width: 26px; height: 26px; }

.gm-chat-launcher-badge {
  position: absolute;
  top: -3px;
  right: -3px;
  background: #10b981;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  border: 2px solid #fff;
  letter-spacing: 0.03em;
  line-height: 1.1;
}

/* Chat Window Modal / Panel */
.gm-chat-window {
  pointer-events: auto;
  position: fixed;
  bottom: calc(var(--gm-bottom) + var(--gm-launcher) + 12px);
  right: var(--gm-right);
  width: var(--gm-w);
  max-width: var(--gm-max-w);
  height: min(560px, calc(100vh - 110px));
  height: var(--gm-h);
  max-height: calc(100vh - 100px);
  max-height: var(--gm-max-h);
  background: #ffffff;
  border-radius: var(--gm-radius);
  box-shadow: 0 16px 48px rgba(17, 24, 39, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 1050;
}
#gm-chat-panel[style*="display: flex"],
#gm-chat-panel[style*="display:flex"] {
  animation: gmSlideUp 0.25s cubic-bezier(.4,0,.2,1);
}

@keyframes gmSlideUp {
  from { opacity: 0; transform: translateY(14px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

@media (prefers-reduced-motion: reduce) {
  #gm-chat-panel[style*="display: flex"],
  #gm-chat-panel[style*="display:flex"] { animation: none; }
  .gm-chat-launcher, .gm-chip { transition: none; }
}

/* Resize Handles */
.gm-resize-handle {
  position: absolute;
  z-index: 25;
  touch-action: none;
}
.gm-resize-t { top: 0; left: 24px; right: 0; height: 8px; cursor: ns-resize; }
.gm-resize-l { top: 24px; bottom: 0; left: 0; width: 8px; cursor: ew-resize; }
.gm-resize-tl {
  top: 0;
  left: 0;
  width: 24px;
  height: 24px;
  cursor: nwse-resize;
  display: flex;
  align-items: center;
  justify-content: center;
}
.gm-resize-grip {
  width: 9px;
  height: 9px;
  border-top: 2px solid rgba(255, 255, 255, 0.35);
  border-left: 2px solid rgba(255, 255, 255, 0.35);
  border-top-left-radius: 4px;
  transition: border-color 0.15s, transform 0.15s;
  pointer-events: none;
}
.gm-resize-tl:hover .gm-resize-grip {
  border-color: #F45B0B;
  transform: scale(1.2);
}

/* Header */
.gm-chat-header {
  background: #111827;
  color: #fff;
  height: var(--gm-header);
  min-height: var(--gm-header);
  padding: 0 14px 0 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
  position: relative;
  border-top-left-radius: inherit;
  border-top-right-radius: inherit;
}
.gm-chat-header::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #F45B0B 0%, #ff8a50 50%, #F45B0B 100%);
}
.gm-chat-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  flex: 1 1 auto;
}
.gm-chat-avatar {
  width: var(--gm-avatar);
  height: var(--gm-avatar);
  min-width: var(--gm-avatar);
  border-radius: 50%;
  background: linear-gradient(135deg, #F45B0B, #ff7a33);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}
.gm-chat-avatar svg { width: 18px; height: 18px; color: #fff; }
.gm-chat-header-info { min-width: 0; flex: 1 1 auto; }
.gm-chat-title {
  font-size: 0.88rem;
  font-weight: 600;
  color: #fff;
  margin: 0;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.gm-chat-status {
  font-size: 0.68rem;
  color: #9ca3af;
  display: flex;
  align-items: center;
  gap: 5px;
  margin-top: 2px;
  white-space: nowrap;
}
.gm-status-dot {
  width: 7px;
  height: 7px;
  min-width: 7px;
  border-radius: 50%;
  background: #10b981;
  display: inline-block;
  box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);
  animation: gmPulse 2s ease-in-out infinite;
}
@keyframes gmPulse {
  0%, 100% { box-shadow: 0 0 4px rgba(16, 185, 129, 0.4); }
  50% { box-shadow: 0 0 8px rgba(16, 185, 129, 0.7); }
}

.gm-chat-header-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
  margin-left: 8px;
}
.gm-chat-header-btn {
  background: none;
  border: 0;
  color: #9ca3af;
  width: 36px;
  height: 36px;
  min-width: 36px;
  min-height: 36px;
  display: grid;
  place-items: center;
  cursor: pointer;
  border-radius: 8px;
  transition: background 0.15s, color 0.15s;
  padding: 0;
}
.gm-chat-header-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
}
.gm-chat-header-btn svg { width: 17px; height: 17px; }

/* Empty State */
.gm-chat-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 16px 14px 6px;
  gap: 6px;
  flex-shrink: 0;
}
.gm-chat-empty-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: linear-gradient(135deg, #FFF1E8, #FFE0CC);
  display: grid;
  place-items: center;
  margin-bottom: 2px;
}
.gm-chat-empty-icon svg { width: 22px; height: 22px; color: #F45B0B; }
.gm-chat-empty-title { font-size: 0.9rem; font-weight: 600; color: #1f2937; line-height: 1.3; }
.gm-chat-empty-desc { font-size: 0.75rem; color: #6b7280; line-height: 1.45; max-width: 280px; }

/* Messages Area */
.gm-chat-body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 14px;
  background: #F8FAFC;
  display: flex;
  flex-direction: column;
  gap: 10px;
  scroll-behavior: smooth;
}
.gm-chat-body::-webkit-scrollbar { width: 4px; }
.gm-chat-body::-webkit-scrollbar-track { background: transparent; }
.gm-chat-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.gm-chat-body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* Messages */
.gm-msg {
  display: flex;
  flex-direction: column;
  max-width: 85%;
  min-width: 0;
  animation: gmMsgIn 0.2s ease-out;
}
@keyframes gmMsgIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
.gm-msg-bot { align-self: flex-start; }
.gm-msg-user { align-self: flex-end; }

.gm-bubble {
  padding: 9px 13px;
  border-radius: 16px;
  font-size: 0.83rem;
  line-height: 1.5;
  word-break: break-word;
  overflow-wrap: anywhere;
  white-space: pre-wrap;
}
.gm-msg-bot .gm-bubble {
  background: #ffffff;
  color: #334155;
  border: 1px solid #e2e8f0;
  border-top-left-radius: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.gm-msg-user .gm-bubble {
  background: linear-gradient(135deg, #F45B0B, #e04500);
  color: #ffffff;
  border-top-right-radius: 4px;
  box-shadow: 0 2px 6px rgba(244, 91, 11, 0.25);
}
.gm-bubble b, .gm-bubble strong { font-weight: 600; color: inherit; }
.gm-msg-bot .gm-bubble b, .gm-msg-bot .gm-bubble strong { color: #0f172a; }
.gm-msg-time {
  font-size: 0.62rem;
  color: #94a3b8;
  margin-top: 3px;
  padding: 0 6px;
}
.gm-msg-user .gm-msg-time { text-align: right; }

/* Quick Chips */
.gm-quick-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 2px;
  padding: 0 2px;
  flex-shrink: 0;
}
.gm-chip {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 5px 12px;
  font-size: 0.72rem;
  color: #334155;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(.4,0,.2,1);
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-weight: 500;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  line-height: 1.3;
}
.gm-chip:hover {
  background: #FFF5EE;
  border-color: #F45B0B;
  color: #F45B0B;
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(244, 91, 11, 0.12);
}
.gm-chip:active { transform: translateY(0); }
.gm-chip-icon { width: 13px; height: 13px; flex-shrink: 0; opacity: 0.7; }
.gm-chip:hover .gm-chip-icon { opacity: 1; }

/* Typing Indicator */
.gm-bubble-typing {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 8px 14px;
}
.gm-typing-label { font-size: 0.62rem; color: #94a3b8; font-weight: 500; }
.gm-typing-dots { display: flex; align-items: center; gap: 5px; }
.gm-bubble-typing .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #94a3b8;
  animation: gmBounce 1.4s infinite ease-in-out;
}
.gm-bubble-typing .dot:nth-child(2) { animation-delay: 0.2s; }
.gm-bubble-typing .dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes gmBounce {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.3; }
  40% { transform: scale(1); opacity: 1; }
}

/* Error Banner */
.gm-chat-error-bar {
  background: #fef2f2;
  color: #b91c1c;
  padding: 7px 12px;
  font-size: 0.74rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid #fecaca;
  flex-shrink: 0;
}
.gm-chat-error-bar button {
  background: none;
  border: 0;
  color: #b91c1c;
  font-size: 18px;
  cursor: pointer;
  padding: 0 2px;
  line-height: 1;
}

/* Input Footer */
.gm-chat-footer {
  padding: 10px 12px 8px;
  background: #ffffff;
  border-top: 1px solid #f1f5f9;
  flex-shrink: 0;
}
.gm-chat-input-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #F8FAFC;
  border: 1.5px solid #e2e8f0;
  border-radius: 24px;
  padding: 3px 3px 3px 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
  min-height: 48px;
}
.gm-chat-input-wrap:focus-within {
  border-color: #F45B0B;
  box-shadow: 0 0 0 3px rgba(244, 91, 11, 0.1);
  background: #fff;
}
.gm-chat-textarea {
  flex: 1 1 auto;
  min-width: 0;
  resize: none;
  border: 0;
  background: transparent;
  font-size: 0.83rem;
  padding: 6px 0;
  outline: none;
  max-height: 72px;
  line-height: 1.4;
  color: #334155;
  font-family: inherit;
}
.gm-chat-textarea::placeholder { color: #94a3b8; }
.gm-chat-btn-send {
  width: var(--gm-send);
  height: var(--gm-send);
  min-width: var(--gm-send);
  min-height: var(--gm-send);
  border-radius: 50%;
  background: linear-gradient(135deg, #F45B0B, #e04500);
  color: #fff;
  border: 0;
  display: grid;
  place-items: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: transform 0.15s, box-shadow 0.15s, background 0.15s;
  padding: 0;
}
.gm-chat-btn-send:hover { transform: scale(1.05); box-shadow: 0 3px 10px rgba(244, 91, 11, 0.3); }
.gm-chat-btn-send:active { transform: scale(0.95); }
.gm-chat-btn-send:disabled {
  background: #d1d5db;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}
.gm-chat-btn-send svg { width: 17px; height: 17px; }
.gm-chat-footer-hint {
  font-size: 0.6rem;
  color: #94a3b8;
  text-align: center;
  margin-top: 5px;
}

/* ==========================================================================
   Responsive Breakpoints
   ========================================================================== */

/* Tablet (481px to 768px) */
@media (min-width: 481px) and (max-width: 768px) {
  #gm-chatbot-root {
    --gm-w: min(420px, calc(100vw - 32px));
    --gm-h: min(520px, calc(100dvh - 96px));
    --gm-max-h: calc(100dvh - 90px);
    --gm-bottom: 16px;
    --gm-right: 16px;
    --gm-launcher: 50px;
  }
  .gm-chat-window {
    bottom: calc(var(--gm-bottom) + var(--gm-launcher) + 10px);
  }
  .gm-chat-header-btn {
    width: 38px;
    height: 38px;
    min-width: 38px;
    min-height: 38px;
  }
}

/* Smartphone (≤ 480px) */
@media (max-width: 480px) {
  #gm-chatbot-root {
    --gm-w: calc(100vw - 24px);
    --gm-max-w: 400px;
    --gm-h: min(500px, calc(100dvh - 86px));
    --gm-max-h: calc(100dvh - 84px);
    --gm-bottom: 12px;
    --gm-right: 12px;
    --gm-launcher: 48px;
    --gm-header: 54px;
    --gm-avatar: 32px;
    --gm-send: 44px; /* Minimum 44x44px touch target */
    --gm-radius: 16px;
  }
  .gm-chat-window {
    bottom: calc(var(--gm-bottom) + var(--gm-launcher) + 10px);
    max-width: calc(100vw - 24px) !important;
  }
  .gm-chat-header { padding: 0 8px 0 14px; }
  .gm-chat-header-actions { gap: 2px; margin-left: 4px; }
  .gm-chat-header-btn { width: 36px; height: 36px; min-width: 36px; min-height: 36px; }
  .gm-chat-header-btn svg { width: 16px; height: 16px; }
  .gm-chat-body { padding: 10px; gap: 8px; }
  .gm-msg { max-width: 88%; }
  .gm-bubble { padding: 8px 12px; font-size: 0.81rem; border-radius: 14px; }
  .gm-chat-empty { padding: 12px 10px 4px; gap: 4px; }
  .gm-chat-empty-icon { width: 38px; height: 38px; }
  .gm-chat-empty-icon svg { width: 18px; height: 18px; }
  .gm-chat-empty-title { font-size: 0.85rem; }
  .gm-chat-empty-desc { font-size: 0.72rem; }
  .gm-quick-chips { gap: 5px; }
  .gm-chip { padding: 5px 10px; font-size: 0.7rem; border-radius: 16px; }
  .gm-chat-footer { padding: 8px 10px; }
  .gm-chat-input-wrap { min-height: 48px; padding: 2px 2px 2px 12px; }
  .gm-chat-textarea { font-size: 0.81rem; }
  .gm-chat-footer-hint { display: none; }
}

/* Short Screen Heights (≤ 520px Landscape / Low-res) */
@media (max-height: 520px) {
  #gm-chatbot-root {
    --gm-h: calc(100dvh - 76px);
    --gm-max-h: calc(100dvh - 70px);
    --gm-bottom: 8px;
    --gm-header: 48px;
  }
  .gm-chat-window {
    bottom: calc(var(--gm-bottom) + var(--gm-launcher) + 6px);
  }
  .gm-chat-empty { display: none !important; }
  .gm-chat-footer { padding: 6px 10px; }
  .gm-chat-input-wrap { min-height: 42px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.getElementById('gm-chat-toggle');
  const closeBtn = document.getElementById('gm-chat-close');
  const minimizeBtn = document.getElementById('gm-chat-minimize');
  const expandBtn = document.getElementById('gm-chat-expand');
  const chatPanel = document.getElementById('gm-chat-panel');
  const chatForm = document.getElementById('gm-chat-form');
  const chatInput = document.getElementById('gm-chat-input');
  const chatMessages = document.getElementById('gm-chat-messages');
  const chatTyping = document.getElementById('gm-chat-typing');
  const chatError = document.getElementById('gm-chat-error');
  const sendBtn = document.getElementById('gm-chat-send');
  const chipsContainer = document.getElementById('gm-quick-chips');
  const emptyState = document.getElementById('gm-chat-empty');

  const icChat = toggleBtn?.querySelector('.ic-chat');
  const icClose = toggleBtn?.querySelector('.ic-close');
  const badge = toggleBtn?.querySelector('.gm-chat-launcher-badge');
  const icExpand = expandBtn?.querySelector('.ic-expand');
  const icCompress = expandBtn?.querySelector('.ic-compress');

  const CSRF_TOKEN = '{{ csrf_token() }}';
  const CHAT_URL = '{{ route("chatbot.message") }}';

  let conversationHistory = [];

  // Pulihkan riwayat percakapan dari sessionStorage
  try {
    const saved = sessionStorage.getItem('gm_chat_history');
    if (saved) {
      conversationHistory = JSON.parse(saved);
      if (conversationHistory.length > 0 && emptyState) {
        emptyState.style.display = 'none';
      }
      conversationHistory.forEach(item => {
        appendMessage(item.role === 'model' ? 'bot' : 'user', item.text);
      });
    }
  } catch(e) {}

  // ========================================================================
  // Fitur Resize (Pointer Events + rAF untuk efisiensi & performa 60fps)
  // ========================================================================
  let isExpanded = false;

  function applySize(width, height) {
    const minW = Math.min(320, window.innerWidth - 24);
    const maxW = Math.max(minW, window.innerWidth - 24);
    const minH = 340;
    const maxH = Math.max(minH, window.innerHeight - 90);

    chatPanel.style.width = `${Math.max(minW, Math.min(maxW, Math.round(width)))}px`;
    chatPanel.style.height = `${Math.max(minH, Math.min(maxH, Math.round(height)))}px`;
  }

  function resetSize() {
    chatPanel.style.width = '';
    chatPanel.style.height = '';
    isExpanded = false;
    if (icExpand) icExpand.style.display = 'block';
    if (icCompress) icCompress.style.display = 'none';
    try { localStorage.removeItem('gm_chat_size'); } catch(e) {}
  }

  // Pulihkan preferensi ukuran di desktop/tablet
  try {
    const savedSize = localStorage.getItem('gm_chat_size');
    if (savedSize && window.innerWidth > 576) {
      const { width, height } = JSON.parse(savedSize);
      if (width && height) applySize(width, height);
    }
  } catch(e) {}

  // Toggle Expand / Compress
  if (expandBtn) {
    expandBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (!isExpanded) {
        applySize(Math.min(620, window.innerWidth - 32), Math.min(760, window.innerHeight - 100));
        isExpanded = true;
        if (icExpand) icExpand.style.display = 'none';
        if (icCompress) icCompress.style.display = 'block';
      } else {
        resetSize();
      }
    });
  }

  // Pointer-based Drag Resizing (Efisien, tanpa reflow berlebih)
  let resizeState = null;
  let resizeRaf = null;

  function onResizeStart(e, dir) {
    if (e.button !== 0 && e.pointerType === 'mouse') return;
    e.preventDefault();
    e.stopPropagation();

    const rect = chatPanel.getBoundingClientRect();
    resizeState = {
      dir,
      startX: e.clientX,
      startY: e.clientY,
      startW: rect.width,
      startH: rect.height,
      targetW: rect.width,
      targetH: rect.height,
      handle: e.currentTarget,
      pointerId: e.pointerId
    };

    e.currentTarget.setPointerCapture(e.pointerId);
    document.documentElement.classList.add('gm-resizing', `gm-resizing-${dir}`);
  }

  function onResizeMove(e) {
    if (!resizeState || e.pointerId !== resizeState.pointerId) return;
    e.preventDefault();

    const deltaX = resizeState.startX - e.clientX;
    const deltaY = resizeState.startY - e.clientY;

    if (resizeState.dir === 'tl' || resizeState.dir === 'l') {
      resizeState.targetW = resizeState.startW + deltaX;
    }
    if (resizeState.dir === 'tl' || resizeState.dir === 't') {
      resizeState.targetH = resizeState.startH + deltaY;
    }

    if (!resizeRaf) {
      resizeRaf = requestAnimationFrame(() => {
        if (resizeState) applySize(resizeState.targetW, resizeState.targetH);
        resizeRaf = null;
      });
    }
  }

  function onResizeEnd(e) {
    if (!resizeState || e.pointerId !== resizeState.pointerId) return;
    try { resizeState.handle.releasePointerCapture(e.pointerId); } catch(err) {}

    document.documentElement.classList.remove('gm-resizing', 'gm-resizing-tl', 'gm-resizing-t', 'gm-resizing-l');
    if (resizeRaf) { cancelAnimationFrame(resizeRaf); resizeRaf = null; }

    if (window.innerWidth > 576) {
      const rect = chatPanel.getBoundingClientRect();
      try {
        localStorage.setItem('gm_chat_size', JSON.stringify({
          width: Math.round(rect.width),
          height: Math.round(rect.height)
        }));
      } catch(err) {}
    }

    resizeState = null;
  }

  chatPanel.querySelectorAll('.gm-resize-handle').forEach(el => {
    const dir = el.classList.contains('gm-resize-tl') ? 'tl' : (el.classList.contains('gm-resize-t') ? 't' : 'l');
    el.addEventListener('pointerdown', (e) => onResizeStart(e, dir));
    el.addEventListener('pointermove', onResizeMove);
    el.addEventListener('pointerup', onResizeEnd);
    el.addEventListener('pointercancel', onResizeEnd);
  });

  const handleTL = chatPanel.querySelector('.gm-resize-tl');
  if (handleTL) {
    handleTL.addEventListener('dblclick', (e) => {
      e.stopPropagation();
      resetSize();
    });
  }

  // ========================================================================
  // Buka & Tutup Panel (Tombol AI bisa buka dan menutup)
  // ========================================================================
  function openChat() {
    chatPanel.style.display = 'flex';
    if (icChat) icChat.style.display = 'none';
    if (icClose) icClose.style.display = 'block';
    if (badge) badge.style.display = 'none';
    if (toggleBtn) {
      toggleBtn.setAttribute('aria-label', 'Tutup Chat Asisten GoManisGo');
      toggleBtn.title = 'Tutup Chat Asisten GoManisGo';
    }
    scrollToBottom();
    setTimeout(() => chatInput.focus(), 150);
  }

  function closeChat() {
    chatPanel.style.display = 'none';
    if (icChat) icChat.style.display = 'block';
    if (icClose) icClose.style.display = 'none';
    if (badge) badge.style.display = 'block';
    if (toggleBtn) {
      toggleBtn.setAttribute('aria-label', 'Buka Chat Asisten GoManisGo');
      toggleBtn.title = 'Tanya Asisten GoManisGo';
    }
    chatPanel.style.maxHeight = '';
    chatPanel.style.bottom = '';
  }

  function toggleChat() {
    if (chatPanel.style.display !== 'none') {
      closeChat();
    } else {
      openChat();
    }
  }

  // Tombol launcher AI berfungsi sebagai toggle (buka dan tutup)
  if (toggleBtn) toggleBtn.addEventListener('click', toggleChat);
  if (closeBtn) closeBtn.addEventListener('click', closeChat);
  if (minimizeBtn) minimizeBtn.addEventListener('click', closeChat);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && chatPanel.style.display !== 'none') closeChat();
  });

  // Adaptasi keyboard virtual pada smartphone (visualViewport throttled)
  if (window.visualViewport) {
    let vvRaf = null;
    const onViewportChange = () => {
      if (vvRaf) cancelAnimationFrame(vvRaf);
      vvRaf = requestAnimationFrame(() => {
        if (chatPanel.style.display !== 'none' && window.innerWidth <= 768) {
          const vv = window.visualViewport;
          const availableHeight = Math.max(220, vv.height - 86);
          chatPanel.style.maxHeight = `${availableHeight}px`;
          const offsetBottom = Math.max(10, window.innerHeight - (vv.offsetTop + vv.height) + 70);
          chatPanel.style.bottom = `${offsetBottom}px`;
          if (document.activeElement === chatInput) scrollToBottom();
        } else {
          chatPanel.style.maxHeight = '';
          chatPanel.style.bottom = '';
        }
      });
    };
    window.visualViewport.addEventListener('resize', onViewportChange);
    window.visualViewport.addEventListener('scroll', onViewportChange);
  }

  // Quick Chips
  if (chipsContainer) {
    chipsContainer.addEventListener('click', (e) => {
      const chip = e.target.closest('.gm-chip');
      if (chip) {
        chatInput.value = chip.getAttribute('data-query');
        sendMessage();
      }
    });
  }

  // Handle Enter & Submit
  chatInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    sendMessage();
  });

  // ========================================================================
  // Kirim Pesan & Kelola Percakapan
  // ========================================================================
  async function sendMessage() {
    const message = chatInput.value.trim();
    if (!message) return;

    chatError.style.display = 'none';
    if (chipsContainer) chipsContainer.style.display = 'none';
    if (emptyState) emptyState.style.display = 'none';

    appendMessage('user', message);
    conversationHistory.push({ role: 'user', text: message });
    saveHistory();

    chatInput.value = '';
    chatInput.disabled = true;
    sendBtn.disabled = true;
    showTyping(true);

    try {
      const response = await fetch(CHAT_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          message: message,
          history: conversationHistory.slice(-8)
        })
      });

      const data = await response.json();
      showTyping(false);

      if (response.ok && data.reply) {
        appendMessage('bot', data.reply);
        conversationHistory.push({ role: 'model', text: data.reply });
        saveHistory();
      } else {
        appendMessage('bot', data.reply || 'Gagal menerima balasan dari asisten.');
      }
    } catch (err) {
      showTyping(false);
      chatError.style.display = 'flex';
      appendMessage('bot', 'Maaf, terjadi masalah koneksi ke server. Silakan coba lagi.');
    } finally {
      chatInput.disabled = false;
      sendBtn.disabled = false;
      chatInput.focus();
    }
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  function appendMessage(sender, text) {
    const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const msgDiv = document.createElement('div');
    msgDiv.className = `gm-msg gm-msg-${sender}`;

    const bubble = document.createElement('div');
    bubble.className = 'gm-bubble';

    // Format aman: escape karakter HTML terlebih dahulu sebelum format markdown
    bubble.innerHTML = escapeHtml(text)
      .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
      .replace(/\n/g, '<br>');

    const timeSpan = document.createElement('span');
    timeSpan.className = 'gm-msg-time';
    timeSpan.textContent = time;

    msgDiv.appendChild(bubble);
    msgDiv.appendChild(timeSpan);
    chatMessages.insertBefore(msgDiv, chatTyping);
    scrollToBottom();
  }

  function showTyping(show) {
    chatTyping.style.display = show ? 'flex' : 'none';
    scrollToBottom();
  }

  function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function saveHistory() {
    try {
      sessionStorage.setItem('gm_chat_history', JSON.stringify(conversationHistory.slice(-12)));
    } catch(e) {}
  }
});
</script>
