<template>
  <section class="client-page">
    <div class="client-container chatbot-grid">
      <aside class="chat-sidebar">
        <div class="sidebar-head">
          <span class="eyebrow">Chatbot</span>
          <h1>Hỗ trợ tra cứu bằng hội thoại</h1>
          <p>Phần giao diện đã sẵn sàng. Luồng AI xử lý câu hỏi sẽ được nối ở bước tiếp theo.</p>
        </div>

        <div class="conversation-list">
          <button
            v-for="conversation in conversations"
            :key="conversation.id"
            type="button"
            class="conversation-btn"
            :class="{ active: activeConversationId === conversation.id }"
            @click="activeConversationId = conversation.id"
          >
            <strong>{{ conversation.title }}</strong>
            <span>{{ conversation.updatedAt }}</span>
          </button>
        </div>
      </aside>

      <div class="chat-panel">
        <div class="chat-thread">
          <article
            v-for="message in activeMessages"
            :key="message.id"
            class="chat-bubble"
            :class="message.role"
          >
            <strong>{{ message.role === 'bot' ? 'BusBot' : 'Bạn' }}</strong>
            <p>{{ message.content }}</p>
          </article>
        </div>

        <form class="chat-form" @submit.prevent="notifyComingSoon">
          <textarea
            v-model="draft"
            rows="3"
            placeholder="Nhập câu hỏi về tuyến xe, trạm hoặc điểm đến..."
          ></textarea>
          <button type="submit">Gửi</button>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue';
import { showAlert } from '../../utils/notify';

const draft = ref('');
const activeConversationId = ref(1);

const conversations = [
  { id: 1, title: 'Tư vấn đi từ trung tâm ra biển', updatedAt: 'Hôm nay' },
  { id: 2, title: 'Tìm tuyến đi sân bay', updatedAt: 'Hôm qua' },
];

const messages = {
  1: [
    { id: 1, role: 'bot', content: 'Chào bạn, mình có thể hỗ trợ tìm tuyến xe buýt, trạm gần nhất và lộ trình phù hợp.' },
    { id: 2, role: 'user', content: 'Tôi muốn đi từ trung tâm ra biển Mỹ Khê.' },
    { id: 3, role: 'bot', content: 'Giao diện hội thoại đã sẵn sàng. Phần AI sẽ được kết nối ở bước tiếp theo để trả lời tự động.' },
  ],
  2: [
    { id: 4, role: 'bot', content: 'Bạn có thể nhập điểm đi, điểm đến hoặc một địa danh để hệ thống gợi ý tuyến phù hợp.' },
  ],
};

const activeMessages = computed(() => messages[activeConversationId.value] || []);

async function notifyComingSoon() {
  draft.value = '';
  await showAlert({
    icon: 'info',
    title: 'Chatbot đang hoàn thiện',
    text: 'Màn hình hội thoại đã sẵn sàng. Bước tiếp theo sẽ nối xử lý AI và gửi/nhận tin nhắn thật.',
  });
}
</script>

<style scoped>
.client-page { padding: 0 0 20px; }
.client-container { width: min(1420px, calc(100% - 32px)); margin: 0 auto; }
.chatbot-grid { display: grid; grid-template-columns: 340px minmax(0, 1fr); gap: 20px; }
.chat-sidebar, .chat-panel { border-radius: 28px; border: 1px solid rgba(122, 88, 61, 0.12); background: rgba(255, 255, 255, 0.92); }
.chat-sidebar { padding: 22px; }
.eyebrow { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(255, 140, 61, 0.12); color: #bf5a1f; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
.sidebar-head h1 { margin: 14px 0 8px; font-size: 34px; line-height: 0.98; }
.sidebar-head p { margin: 0; color: #6f5a4d; line-height: 1.6; }
.conversation-list { display: grid; gap: 12px; margin-top: 20px; }
.conversation-btn { width: 100%; text-align: left; padding: 16px; border-radius: 18px; border: 1px solid rgba(122, 88, 61, 0.12); background: #fffaf6; }
.conversation-btn.active { border-color: #ffb98f; background: #fff2e8; }
.conversation-btn strong, .conversation-btn span { display: block; }
.conversation-btn span { margin-top: 6px; color: #8a7263; font-size: 13px; }
.chat-panel { display: grid; grid-template-rows: minmax(0, 1fr) auto; min-height: 680px; }
.chat-thread { padding: 22px; overflow: auto; display: grid; gap: 14px; }
.chat-bubble { max-width: 70%; padding: 16px; border-radius: 20px; line-height: 1.6; }
.chat-bubble strong { display: block; margin-bottom: 6px; font-size: 13px; }
.chat-bubble.bot { background: #fff3e9; color: #5d3924; }
.chat-bubble.user { margin-left: auto; background: #25150f; color: #fff; }
.chat-bubble p { margin: 0; }
.chat-form { display: grid; gap: 12px; padding: 18px 22px 22px; border-top: 1px solid rgba(122, 88, 61, 0.1); }
.chat-form textarea { width: 100%; padding: 14px; border-radius: 18px; border: 1px solid #e4d4c5; resize: vertical; background: #fff; }
.chat-form button { justify-self: end; min-height: 42px; padding: 0 18px; border: 0; border-radius: 999px; color: #fff; background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%); }

@media (max-width: 980px) {
  .client-container { width: min(100% - 20px, 1420px); }
  .chatbot-grid { grid-template-columns: 1fr; }
  .chat-panel { min-height: 560px; }
  .chat-bubble { max-width: 100%; }
}
</style>
