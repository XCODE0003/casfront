import { defineStore } from 'pinia';
import HttpApi from "@/Services/HttpApi.js";
export const useChatStore = defineStore('chat', {
    state: () => ({
        messages: [],
        chats: [],
        message: '',
        activeChat: null,
    }),
    actions: {
        async getChats() {
            const response = await HttpApi.get('/chat/support/get');
            this.chats = response.data.chats;
            this.activeChat = this.chats[0].id;
            await this.getChatMessages(this.activeChat);
        },
        async getChatMessages(chatId) {
            const response = await HttpApi.get(`/chat/messages/${chatId}`);
            this.messages = response.data.messages;
        },
        async sendMessage() {
            const response = await HttpApi.post('/chat/send', {
                message: this.message,
                chat_id: this.activeChat
            });
            this.message = '';
            this.messages.push(response.data.message);
        },
        async selectChat(chatId) {
            this.activeChat = chatId;
            await this.getChatMessages(chatId);
        }
    }
});
