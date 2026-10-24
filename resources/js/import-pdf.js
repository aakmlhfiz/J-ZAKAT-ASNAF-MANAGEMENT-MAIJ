document.addEventListener('alpine:init', () => {
    Alpine.data('extractionPoller', () => ({
        elapsed: 0,
        timer: null,
        pollInterval: null,
        importId: 0,
        pollUrl: '',

        init() {
            this.importId = this.$el.dataset?.importId || this.$root.dataset?.importId;
            this.pollUrl  = this.$root.dataset?.pollUrl || `/app/import-pdf/status/${this.importId}`;

            this.startPolling();
        },

        startPolling() {
            this.elapsed = 0;
            this.timer = setInterval(() => {
                this.elapsed++;
            }, 1000);

            this.pollInterval = setInterval(() => {
                fetch(this.pollUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'extracted' || data.status === 'committed' || data.status === 'failed') {
                            window.location.reload();
                        }
                    })
                    .catch(() => {});
            }, 2000);
        },

        stopPolling() {
            if (this.timer) clearInterval(this.timer);
            if (this.pollInterval) clearInterval(this.pollInterval);
        },
    }));


});
