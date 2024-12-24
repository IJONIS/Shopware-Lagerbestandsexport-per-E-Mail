import { createDefaultNotificationHandler } from '@shopware-ag/admin-extension-sdk/es/notification';
import './extension/sw-settings-index';

const notificationHandler = createDefaultNotificationHandler();

Shopware.Service('lagerbestandsexportService', () => {
    return {
        handleTestMailResponse: (response) => {
            if (response.success) {
                notificationHandler.dispatch({
                    title: 'Erfolg',
                    message: response.message,
                    variant: 'success'
                });
            } else {
                notificationHandler.dispatch({
                    title: 'Fehler',
                    message: response.message,
                    variant: 'error'
                });
            }
        },
        sendTestMail() {
            return Shopware.api.post(
                '/api/_action/lagerbestandsexport/send/test',
                {}
            );
        }
    };
});
