import './component/lagerbestandsexport-settings';

Shopware.Component.override('sw-settings-index', {
    template: `
        <sw-page class="sw-settings-index">
            <template #content>
                <sw-card-view>
                    <lagerbestandsexport-settings />
                </sw-card-view>
            </template>
        </sw-page>
    `
});
