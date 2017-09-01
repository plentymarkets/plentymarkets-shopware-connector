// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/store/settings}

Ext.define('Shopware.apps.PlentyConnector.store.Settings', {
    extend: 'Ext.data.Store',

    storeId: 'plentymarkets-store-settings',

    model: 'Shopware.apps.PlentyConnector.model.Settings',

    batch: true,
    autoLoad: false,

    proxy: {
        type: 'ajax',
        api: {
            read: '{url action=getSettingsList}',
            save: '{url action=setSettingsList}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});

// {/block}
