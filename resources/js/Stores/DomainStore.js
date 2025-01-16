
import { defineStore } from 'pinia';
import axios from 'axios';

export const useDomainStore = defineStore('domain', {
    state: () => ({
        domain: null,
        loading: false,
        error: null
    }),

    actions: {
        async fetchDomainInfo() {
            this.loading = true;
            try {
                const response = await axios.get('/api/domain/info');
                this.domain = response.data;
                this.error = null;
            } catch (err) {
                this.error = err.message;
                this.domain = null;
            } finally {
                this.loading = false;
            }
        }
    },

    getters: {
        getDomain: (state) => state.domain,
        isLoading: (state) => state.loading,
        getError: (state) => state.error
    }
});
