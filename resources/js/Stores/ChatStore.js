import { defineStore } from 'pinia';
import HttpApi from "@/Services/HttpApi.js";

export const useChatStore = defineStore('chat', {
    state: () => ({
        messages: [],
        message: '',
        isOpen: false
    }),
    actions: {
        toggleChat() {
            this.isOpen = !this.isOpen;
        },
        async sendMessage() {
            const response = await HttpApi.post('/chat/send', {
                message: this.message,
                chat_id: null
            });
            this.messages.push(response.data.message);
        },
        async getChatMessages() {
            const response = await HttpApi.get(`/chat/user/messages`);
            this.messages = response.data.messages;
        }
    }
});
