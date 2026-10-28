{{-- Widget Asisten AI TokoSembwko --}}
<div id="gm-chatbot-root">
  {{-- Floating Launcher Button --}}
  <button id="gm-chat-toggle" class="gm-chat-launcher" type="button" aria-label="Buka Chat Asisten TokoSembwko" title="Tanya Asisten TokoSembwko">
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
    {{-- Header --}}
    <div class="gm-chat-header">
      <div class="d-flex align-items-center gap-2">
        <div class="gm-chat-avatar">🌾</div>
        <div>
          <h4 id="gm-chat-title" class="gm-chat-title mb-0">Asisten TokoSembwko</h4>
          <small class="gm-chat-status"><span class="gm-status-dot"></span> Siap Membantu Belanja</small>
        </div>
      </div>
      <button id="gm-chat-close" type="button" class="gm-chat-btn-close" aria-label="Tutup Chat">&times;</button>
    </div>

    {{-- Messages Area --}}
    <div id="gm-chat-messages" class="gm-chat-body">
      <div class="gm-msg gm-msg-bot">
        <div class="gm-bubble">
          Halo, <b>{{ auth()->user()->nama_user }}</b>! 👋<br>
          Saya asisten belanja TokoSembwko. Mau cari kebutuhan sembako apa hari ini? (Beras, minyak, gula, telur, dll.)
        </div>
        <span class="gm-msg-time">{{ date('H:i') }}</span>
      </div>

      {{-- Quick chips --}}
      <div class="gm-quick-chips" id="gm-quick-chips">
        <button type="button" class="gm-chip" data-query="Ada beras apa saja dan harganya berapa?">🌾 Pilihan Beras</button>
        <button type="button" class="gm-chip" data-query="Minyak goreng merk apa yang tersedia?">🍳 Minyak Goreng</button>
        <button type="button" class="gm-chip" data-query="Gula pasir ready stok berapa?">🧂 Gula Pasir</button>
      </div>

      {{-- Typing indicator --}}
      <div id="gm-chat-typing" class="gm-msg gm-msg-bot" style="display:none;">
        <div class="gm-bubble gm-bubble-typing">
          <span class="dot"></span><span class="dot"></span><span class="dot"></span>
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
      <textarea id="gm-chat-input" class="form-control gm-chat-textarea" placeholder="Ketik pertanyaan sembako... (Tekan Enter)" rows="1" maxlength="500" required></textarea>
      <button type="submit" id="gm-chat-send" class="gm-chat-btn-send" aria-label="Kirim Pesan">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </button>
    </form>
  </div>
</div>

<style>
/* CSS Khusus Chatbot TokoSembwko */
#gm-chatbot-root {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1050;
  font-family: inherit;
}

.gm-chat-launcher {
  position: relative;
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-coklat, #8b5a3c) 0%, var(--color-oranye, #e8590c) 100%);
  color: #fff;
  border: 0;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(0,0,0,0.22);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s, box-shadow 0.2s;
}
.gm-chat-launcher:hover {
  transform: scale(1.06);
  box-shadow: 0 6px 18px rgba(0,0,0,0.28);
}
.gm-chat-launcher svg {
  width: 28px;
  height: 28px;
}
.gm-chat-launcher-badge {
  position: absolute;
  top: -3px;
  right: -3px;
  background: #10b981;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  border: 2px solid #fff;
}

.gm-chat-window {
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: min(390px, calc(100vw - 32px));
  height: min(540px, calc(100vh - 120px));
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.18);
  border: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: gmFadeInUp 0.22s ease-out;
}

@keyframes gmFadeInUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.gm-chat-header {
  background: var(--color-gelap, #111827);
  color: #fff;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 2px solid var(--color-oranye, #e8590c);
}
.gm-chat-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255,255,255,0.15);
  display: grid;
  place-items: center;
  font-size: 18px;
}
.gm-chat-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #fff;
}
.gm-chat-status {
  font-size: 0.72rem;
  color: #d1d5db;
  display: flex;
  align-items: center;
  gap: 5px;
}
.gm-status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #10b981;
  display: inline-block;
}
.gm-chat-btn-close {
  background: none;
  border: 0;
  color: #9ca3af;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
  padding: 0 4px;
}
.gm-chat-btn-close:hover { color: #fff; }

.gm-chat-body {
  flex: 1;
  overflow-y: auto;
  padding: 14px;
  background: #f9fafb;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.gm-msg {
  display: flex;
  flex-direction: column;
  max-width: 84%;
}
.gm-msg-bot {
  align-self: flex-start;
}
.gm-msg-user {
  align-self: flex-end;
}
.gm-bubble {
  padding: 9px 13px;
  border-radius: 14px;
  font-size: 0.84rem;
  line-height: 1.45;
  word-break: break-word;
  white-space: pre-wrap;
}
.gm-msg-bot .gm-bubble {
  background: #ffffff;
  color: #1f2937;
  border: 1px solid #e5e7eb;
  border-top-left-radius: 3px;
}
.gm-msg-user .gm-bubble {
  background: var(--color-coklat, #8b5a3c);
  color: #ffffff;
  border-top-right-radius: 3px;
}
.gm-msg-time {
  font-size: 0.65rem;
  color: #9ca3af;
  margin-top: 2px;
  padding: 0 4px;
}
.gm-msg-user .gm-msg-time { text-align: right; }

.gm-quick-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 4px;
}
.gm-chip {
  background: #fff;
  border: 1px solid #d1d5db;
  border-radius: 12px;
  padding: 4px 10px;
  font-size: 0.72rem;
  color: var(--color-coklat, #8b5a3c);
  cursor: pointer;
  transition: all 0.15s;
}
.gm-chip:hover {
  background: #fff4ec;
  border-color: var(--color-oranye, #e8590c);
  color: var(--color-oranye, #e8590c);
}

.gm-bubble-typing {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 10px 14px;
}
.gm-bubble-typing .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #9ca3af;
  animation: gmBlink 1.2s infinite ease-in-out;
}
.gm-bubble-typing .dot:nth-child(2) { animation-delay: 0.2s; }
.gm-bubble-typing .dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes gmBlink {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
  40% { transform: scale(1.1); opacity: 1; }
}

.gm-chat-error-bar {
  background: #fee2e2;
  color: #b91c1c;
  padding: 6px 12px;
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid #fecaca;
}
.gm-chat-error-bar button {
  background: none;
  border: 0;
  color: #b91c1c;
  font-size: 16px;
  cursor: pointer;
}

.gm-chat-footer {
  padding: 10px;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  display: flex;
  align-items: flex-end;
  gap: 8px;
}
.gm-chat-textarea {
  resize: none;
  border-radius: 12px;
  font-size: 0.82rem;
  padding: 8px 12px;
  border-color: #d1d5db;
  max-height: 90px;
}
.gm-chat-textarea:focus {
  border-color: var(--color-coklat, #8b5a3c);
  box-shadow: 0 0 0 2px rgba(139, 90, 60, 0.2);
}
.gm-chat-btn-send {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: var(--color-oranye, #e8590c);
  color: #fff;
  border: 0;
  display: grid;
  place-items: center;
  cursor: pointer;
  flex: none;
  transition: background 0.15s;
}
.gm-chat-btn-send:hover { background: #c2410c; }
.gm-chat-btn-send:disabled { background: #d1d5db; cursor: not-allowed; }
.gm-chat-btn-send svg { width: 18px; height: 18px; }

@media (max-width: 576px) {
  .gm-chat-window {
    right: 12px;
    bottom: 84px;
    width: calc(100vw - 24px);
    height: calc(100vh - 110px);
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.getElementById('gm-chat-toggle');
  const closeBtn = document.getElementById('gm-chat-close');
  const chatPanel = document.getElementById('gm-chat-panel');
  const chatForm = document.getElementById('gm-chat-form');
  const chatInput = document.getElementById('gm-chat-input');
  const chatMessages = document.getElementById('gm-chat-messages');
  const chatTyping = document.getElementById('gm-chat-typing');
  const chatError = document.getElementById('gm-chat-error');
  const sendBtn = document.getElementById('gm-chat-send');
  const chipsContainer = document.getElementById('gm-quick-chips');

  const icChat = toggleBtn.querySelector('.ic-chat');
  const icClose = toggleBtn.querySelector('.ic-close');

  const CSRF_TOKEN = '{{ csrf_token() }}';
  const CHAT_URL = '{{ route("chatbot.message") }}';

  // Riwayat percakapan untuk konteks Gemini
  let conversationHistory = [];

  // Pulihkan riwayat dari sessionStorage
  try {
    const saved = sessionStorage.getItem('gm_chat_history');
    if (saved) {
      conversationHistory = JSON.parse(saved);
      // Render ulang pesan sebelumnya
      conversationHistory.forEach(item => {
        appendMessage(item.role === 'model' ? 'bot' : 'user', item.text, false);
      });
    }
  } catch(e) {}

  function toggleChat() {
    const isVisible = chatPanel.style.display !== 'none';
    if (isVisible) {
      chatPanel.style.display = 'none';
      icChat.style.display = 'block';
      icClose.style.display = 'none';
    } else {
      chatPanel.style.display = 'flex';
      icChat.style.display = 'none';
      icClose.style.display = 'block';
      scrollToBottom();
      setTimeout(() => chatInput.focus(), 150);
    }
  }

  toggleBtn.addEventListener('click', toggleChat);
  closeBtn.addEventListener('click', toggleChat);

  // Quick Chips click handler
  if (chipsContainer) {
    chipsContainer.addEventListener('click', function(e) {
      const chip = e.target.closest('.gm-chip');
      if (chip) {
        const query = chip.getAttribute('data-query');
        chatInput.value = query;
        sendMessage();
      }
    });
  }

  // Handle Enter key (Shift+Enter for newline)
  chatInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  chatForm.addEventListener('submit', function(e) {
    e.preventDefault();
    sendMessage();
  });

  async function sendMessage() {
    const message = chatInput.value.trim();
    if (!message) return;

    chatError.style.display = 'none';
    if (chipsContainer) chipsContainer.style.display = 'none';

    // Tambahkan pesan user ke UI
    appendMessage('user', message, true);
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
        appendMessage('bot', data.reply, true);
        conversationHistory.push({ role: 'model', text: data.reply });
        saveHistory();
      } else {
        const errMsg = data.reply || 'Gagal menerima balasan dari asisten.';
        appendMessage('bot', errMsg, true);
      }
    } catch (err) {
      showTyping(false);
      chatError.style.display = 'flex';
      appendMessage('bot', 'Maaf, terjadi masalah koneksi ke server. Silakan coba lagi.', true);
    } finally {
      chatInput.disabled = false;
      sendBtn.disabled = false;
      chatInput.focus();
    }
  }

  function appendMessage(sender, text, animate) {
    const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const msgDiv = document.createElement('div');
    msgDiv.className = `gm-msg gm-msg-${sender}`;

    const bubble = document.createElement('div');
    bubble.className = 'gm-bubble';
    bubble.textContent = text;

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

