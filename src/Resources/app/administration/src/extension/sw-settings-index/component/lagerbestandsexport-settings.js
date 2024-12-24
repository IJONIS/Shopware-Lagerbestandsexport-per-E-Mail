import template from './lagerbestandsexport-settings.html.twig';

Shopware.Component.register('lagerbestandsexport-settings', {
    template,

    inject: ['lagerbestandsexportService'],

    data() {
        return {
            isLoading: false
        };
    },

    methods: {
        onTestMail() {
            this.isLoading = true;
            this.lagerbestandsexportService.sendTestMail()
                .then((response) => {
                    this.lagerbestandsexportService.handleTestMailResponse(response);
                })
                .catch((error) => {
                    this.createNotificationError({
                        title: this.$tc('lagerbestandsexport.notification.error.title'),
                        message: error.message
                    });
                })
                .finally(() => {
                    this.isLoading = false;
                });
        }
    }
});
