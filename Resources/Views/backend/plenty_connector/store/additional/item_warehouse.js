// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/store/settings/additional/item_warehouse}

Ext.define('Shopware.apps.PlentyConnector.store.additional.ItemWarehouse', {
    extend: 'Ext.data.Store',

    storeId: 'plentymarkets-store-additional-itemwarehouse',

    model: 'Shopware.apps.PlentyConnector.model.additional.ItemWarehouse',

    batch: true,
    autoLoad: false,

    proxy: {
        type: 'ajax',
        url: '{url action=getItemWarehouses}',
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});

// {/block}
