// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/mapping/Plentymarkets}

/**
 * The plentymarkets store is used to load the data for mapping on the side of plentymarkets and
 * is extended by the Ext data store "Ext.data.Store". With Ext stores you can handle
 * model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
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
