// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/mapping/Plentymarkets}
Ext.define('Shopware.apps.Plentymarkets.store.mapping.Plentymarkets', {
    extend: 'Ext.data.Store',
    storeId: 'plentymarkets-store-mapping-plentymarkets',
    model: 'Shopware.apps.Plentymarkets.model.mapping.Plentymarkets',
    batch: true,
    autoLoad: false,
    proxy: {
        type: 'ajax',
        api: {
            read: '{url action=getPlentyMappingData}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
