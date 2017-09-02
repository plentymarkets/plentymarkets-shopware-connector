// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/store/settings/additional/order_origin}

Ext.define('Shopware.apps.PlentyConnector.store.additional.OrderOrigin', {
    extend: 'Ext.data.Store',

    storeId: 'plentymarkets-store-additional-orderorigin',

    model: 'Shopware.apps.PlentyConnector.model.additional.OrderOrigin',

    batch: true,
    autoLoad: false,

    proxy: {
        type: 'ajax',
        url: '{url action=getOrderOrigins}',
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});

// {/block}
